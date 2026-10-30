/**
 * MeraRashifal - Cosmic Accounts Page & Menu Shortcut Interaction
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // Enforce Vedic Sand #FFFBEB background on account page
        if ($('.mr-account-dashboard').length) {
            $('body').addClass('mr-account-page-active');
            $('body, #page, .site, .site-content, #primary, #main, .ast-container, .entry-content, .ast-separate-container, .ast-article-single, .ast-article-post, .mr-account-page-wrapper').css({
                'background-color': '#FFFBEB',
                'background': '#FFFBEB'
            });
        }

        // -------------------------------------------------------------
        // 1. Tab Switching Engine
        // -------------------------------------------------------------
        function switchTab(tabId) {
            if (!tabId) return;
            tabId = tabId.replace('#', '');
            
            var $targetBtn = $('.mr-tab-btn[data-tab="' + tabId + '"]');
            var $targetPane = $('#mr-pane-' + tabId);

            if ($targetBtn.length && $targetPane.length) {
                $('.mr-tab-btn').removeClass('active');
                $('.mr-account-tab-pane').removeClass('active');

                $targetBtn.addClass('active');
                $targetPane.addClass('active');

                // Update Breadcrumb active label
                var tabLabels = {
                    'astrology': 'Astrological Profile & Kundali',
                    'reports': 'My Generated Reports',
                    'passbook': 'Transaction Passbook',
                    'settings': 'Preferences & Settings',
                    'contact': 'Contact & Support'
                };
                if (tabLabels[tabId]) {
                    $('#mr-breadcrumb-active-label').text(tabLabels[tabId]);
                }

                // Smooth scroll to card if below hero
                if ($(window).scrollTop() > 300) {
                    $('html, body').animate({
                        scrollTop: $('.mr-account-hero-card').offset().top - 20
                    }, 300);
                }
            }
        }

        // Click handler for main tabs
        $('.mr-tab-btn').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            switchTab(tab);
            if (history.pushState) {
                history.pushState(null, null, '#' + tab);
            } else {
                location.hash = '#' + tab;
            }
        });

        // Header Action Buttons
        $('#mr-tab-btn-contact-trigger').on('click', function(e) {
            e.preventDefault();
            switchTab('contact');
            location.hash = '#contact';
        });

        $('#mr-tab-btn-settings-trigger').on('click', function(e) {
            e.preventDefault();
            switchTab('settings');
            location.hash = '#settings';
        });

        // Hash listener on page load
        if (window.location.hash) {
            var initialHash = window.location.hash.substring(1);
            switchTab(initialHash);
        }

        $(window).on('hashchange', function() {
            if (window.location.hash) {
                switchTab(window.location.hash.substring(1));
            }
        });

        // -------------------------------------------------------------
        // 2. Place of Birth (POB) Real-time Autocomplete (Cached & Persistent Loader)
        // -------------------------------------------------------------
        var pobTimer = null;
        var $pobInput = $('#mr_astro_pob');
        var $pobDropdown = $('#mr_pob_dropdown');
        var $pobLoader = $('#mr_astro_pob_loader');
        var accountCityCache = {}; // 1-time fetch cache
        var accountActiveXhr = null;

        if ($pobInput.length) {
            $pobInput.on('input paste', function() {
                var query = $(this).val().trim();
                clearTimeout(pobTimer);

                if (query.length < 2) {
                    $pobDropdown.hide().empty();
                    $pobLoader.removeClass('is-active').hide();
                    if (accountActiveXhr && accountActiveXhr.readyState !== 4) accountActiveXhr.abort();
                    return;
                }

                // Keep loader running on this box continuously
                $pobLoader.addClass('is-active').show();

                var cacheKey = query.toLowerCase();
                if (accountCityCache.hasOwnProperty(cacheKey)) {
                    renderPobResults(accountCityCache[cacheKey]);
                    // Loader stays running until user selects an item
                    return;
                }

                pobTimer = setTimeout(function() {
                    if (accountActiveXhr && accountActiveXhr.readyState !== 4) accountActiveXhr.abort();

                    accountActiveXhr = $.ajax({
                        url: mr_account_vars.ajax_url,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'mr_city_search',
                            q: query
                        },
                        success: function(res) {
                            var list = (res && res.success && res.data) ? res.data : [];
                            accountCityCache[cacheKey] = list; // Cache results
                            renderPobResults(list);
                        },
                        error: function() {
                            $pobDropdown.hide();
                        }
                    });
                }, 260);
            });

            function renderPobResults(results) {
                $pobDropdown.empty();
                if (!results || results.length === 0) {
                    $pobDropdown.html('<div class="mr-pob-item" style="color:#94a3b8; cursor:default;">No matching cities found</div>').show();
                    return;
                }
                results.forEach(function(city) {
                    var $item = $('<div class="mr-pob-item"></div>')
                        .html('<span class="mr-pob-pin">📍</span> ' + $('<div>').text(city.name).html())
                        .data('city', city);
                    $pobDropdown.append($item);
                });
                $pobDropdown.show();
            }

            // Click selection on city dropdown: STOP LOADER ON SELECTION
            $(document).on('click', '.mr-pob-item', function() {
                var city = $(this).data('city');
                if (!city) return;

                $pobInput.val(city.name);
                $('#mr_astro_lat').val(city.lat);
                $('#mr_astro_lon').val(city.lon);
                $('#mr_astro_tz').val(city.tz || 5.5);

                $('#mr-display-lat').text(city.lat);
                $('#mr-display-lon').text(city.lon);
                $('#mr-display-tz').text('GMT +' + (city.tz || 5.5));
                $('#mr-coords-status').fadeIn();

                $pobDropdown.hide().empty();

                // User selected place from list: STOP LOADER NOW!
                $pobLoader.removeClass('is-active').hide();
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.mr-pob-search-container').length) {
                    $pobDropdown.hide();
                }
            });
        }

        // -------------------------------------------------------------
        // 3. Save Astrological Details via AJAX
        // -------------------------------------------------------------
        $('#mr-astro-details-form').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#mr-btn-save-astro');
            var $feedback = $('#mr-save-feedback');
            var originalBtnHtml = $btn.html();
            var spinImg = mr_account_vars.loader_url ? '<img src="' + mr_account_vars.loader_url + '" class="mr-spin-btn-icon" alt="" /> ' : '';

            $btn.prop('disabled', true).html(spinImg + 'Saving Coordinates...');
            $feedback.removeClass('mr-feedback-success mr-feedback-error').empty();

            var formData = {
                action: 'mr_save_account_astro_details',
                _ajax_nonce: mr_account_vars.nonce,
                account_name: $('#mr_account_name').val(),
                user_phone: $('#mr_user_phone').val(),
                astro_gender: $('#mr_astro_gender').val(),
                astro_dob: $('#mr_astro_dob').val(),
                astro_tob: $('#mr_astro_tob').val(),
                astro_pob: $('#mr_astro_pob').val(),
                astro_lat: $('#mr_astro_lat').val(),
                astro_lon: $('#mr_astro_lon').val(),
                astro_tz: $('#mr_astro_tz').val()
            };

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(res) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    if (res.success) {
                        $feedback.addClass('mr-feedback-success').html('✓ ' + res.data.message);
                        
                        // Update Rashi snapshot if recalculated
                        if (res.data.rashi) {
                            $('#mr-snap-rashi').text(res.data.rashi.name);
                            $('#mr-snap-elem').text(res.data.rashi.element);
                            $('#mr-snap-ruler').text(res.data.rashi.ruler);
                        }

                        // Update hero name
                        if (formData.account_name) {
                            $('.mr-hero-name').text(formData.account_name);
                            $('.mr-nav-account-label').text(formData.account_name.split(' ')[0]);
                        }

                        setTimeout(function() {
                            $feedback.fadeOut(function() {
                                $(this).text('').show();
                            });
                        }, 4000);
                    } else {
                        $feedback.addClass('mr-feedback-error').html('⚠ ' + (res.data ? res.data.message : 'Error saving coordinates.'));
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    $feedback.addClass('mr-feedback-error').html('⚠ Server connection failed.');
                }
            });
        });

        // -------------------------------------------------------------
        // 4. Save Account Settings via AJAX
        // -------------------------------------------------------------
        $('#mr-account-settings-form').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#mr-btn-save-settings');
            var $feedback = $('#mr-settings-feedback');
            var originalBtnHtml = $btn.html();
            var spinImg = mr_account_vars.loader_url ? '<img src="' + mr_account_vars.loader_url + '" class="mr-spin-btn-icon" alt="" /> ' : '';

            $btn.prop('disabled', true).html(spinImg + 'Saving...');
            $feedback.removeClass('mr-feedback-success mr-feedback-error').empty();

            var formData = {
                action: 'mr_save_account_settings',
                _ajax_nonce: mr_account_vars.nonce,
                pref_chart: $('#mr_pref_chart').val(),
                pref_system: $('#mr_pref_system').val(),
                pref_notify: $('input[name="pref_notify"]').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(res) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    if (res.success) {
                        $feedback.addClass('mr-feedback-success').html('✓ ' + res.data.message);
                        setTimeout(function() {
                            $feedback.fadeOut(function() {
                                $(this).text('').show();
                            });
                        }, 4000);
                    } else {
                        $feedback.addClass('mr-feedback-error').html('⚠ ' + (res.data ? res.data.message : 'Error updating preferences.'));
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    $feedback.addClass('mr-feedback-error').html('⚠ Connection error.');
                }
            });
        });

        // -------------------------------------------------------------
        // 5. Client-Side Table Pagination (Reports & Passbook)
        // -------------------------------------------------------------
        $(document).on('click', '.mr-pagination-wrap .mr-page-num', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var targetPage = parseInt($(this).data('page'), 10);
            paginateAccountTable($wrap, targetPage);
        });

        $(document).on('click', '.mr-pagination-wrap .mr-prev-page', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var current = parseInt($wrap.data('current-page') || 1, 10);
            if (current > 1) {
                paginateAccountTable($wrap, current - 1);
            }
        });

        $(document).on('click', '.mr-pagination-wrap .mr-next-page', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var current = parseInt($wrap.data('current-page') || 1, 10);
            var total = parseInt($wrap.data('total-pages') || 1, 10);
            if (current < total) {
                paginateAccountTable($wrap, current + 1);
            }
        });

        function paginateAccountTable($pagination, page) {
            var total = parseInt($pagination.data('total-pages') || 1, 10);
            if (page < 1 || page > total) return;

            $pagination.data('current-page', page);

            var isReports = $pagination.attr('id') === 'mr-reports-pagination';
            var rowClass = isReports ? '.mr-report-row' : '.mr-txn-row';

            // Show target page rows, hide others
            $(rowClass).hide();
            $(rowClass + '[data-page="' + page + '"]').show();

            // Update page button states
            $pagination.find('.mr-page-num').removeClass('active');
            $pagination.find('.mr-page-num[data-page="' + page + '"]').addClass('active');

            // Prev / Next button state
            $pagination.find('.mr-prev-page').prop('disabled', page <= 1);
            $pagination.find('.mr-next-page').prop('disabled', page >= total);
        }

        // -------------------------------------------------------------
        // 6. Mandatory Terms & Conditions Checkbox for Google Sign In
        // -------------------------------------------------------------
        var $termsCheckbox = $('#mr-agree-terms-checkbox');
        var $googleBtn     = $('#mr-google-signin-btn');
        var $termsWrapper  = $('#mr-terms-consent-wrapper');
        var $warningMsg    = $('#mr-terms-warning-msg');

        // Checkbox state toggle
        $termsCheckbox.on('change', function() {
            if ($(this).is(':checked')) {
                $googleBtn.removeClass('mr-btn-google-disabled').attr('aria-disabled', 'false').attr('title', 'Continue to sign in with Google');
                $warningMsg.slideUp(180);
                $termsWrapper.removeClass('mr-terms-error-shake');
            } else {
                $googleBtn.addClass('mr-btn-google-disabled').attr('aria-disabled', 'true').attr('title', 'Please accept Terms & Conditions below to continue');
            }
        });

        // Intercept click on Google Sign In button if unchecked
        $googleBtn.on('click', function(e) {
            if (!$termsCheckbox.is(':checked')) {
                e.preventDefault();
                e.stopPropagation();

                $warningMsg.slideDown(180);
                $termsWrapper.addClass('mr-terms-error-shake');

                setTimeout(function() {
                    $termsWrapper.removeClass('mr-terms-error-shake');
                }, 500);

                // Focus checkbox
                $termsCheckbox.focus();
                return false;
            }
        });

        // Prevent clicking links in terms text from toggling checkbox
        $('.mr-terms-text a').on('click', function(e) {
            e.stopPropagation();
        });

        // -------------------------------------------------------------
        // 7. Interactive Showcase Actions: Niyati Chat & Scroll to Sign In
        // -------------------------------------------------------------
        $(document).on('click', '.mr-trigger-niyati-btn, #mr-trigger-niyati-showcase, #mr-trigger-niyati-tile', function(e) {
            e.preventDefault();
            var $launcher = $('#niyati-launcher');
            if ($launcher.length) {
                $launcher.trigger('click');
            } else {
                // If on mobile or floating bar
                $('.niyati-launcher-btn').first().trigger('click');
            }
        });

        $(document).on('click', '#mr-btn-scroll-signin', function(e) {
            e.preventDefault();
            var $loginCard = $('#mr-login-card');
            if ($loginCard.length) {
                $('html, body').animate({
                    scrollTop: $loginCard.offset().top - 30
                }, 400, function() {
                    $loginCard.addClass('mr-highlight-card');
                    setTimeout(function() {
                        $loginCard.removeClass('mr-highlight-card');
                    }, 1800);
                });
            }
        });

    });
})(jQuery);
