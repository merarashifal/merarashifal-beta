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

        // Tab listener on page load (via query param ?tab= or hash #)
        try {
            var urlParams = new URLSearchParams(window.location.search);
            var queryTab = urlParams.get('tab');
            if (queryTab) {
                switchTab(queryTab);
            } else if (window.location.hash) {
                var initialHash = window.location.hash.substring(1).split('#')[0].split('?')[0];
                switchTab(initialHash);
            }
        } catch(e) {
            if (window.location.hash) {
                switchTab(window.location.hash.substring(1));
            }
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
                pref_notify: $('input[name="pref_notify"]').is(':checked') ? 1 : 0,
                nominee_name: $('#mr_dpdp_nominee_name').val(),
                nominee_relation: $('#mr_dpdp_nominee_relation').val(),
                nominee_email: $('#mr_dpdp_nominee_email').val(),
                nominee_phone: $('#mr_dpdp_nominee_phone').val(),
                consent_profile: $('#mr_consent_profile').is(':checked') ? 1 : 0,
                consent_ai: $('#mr_consent_ai').is(':checked') ? 1 : 0,
                consent_comms: $('#mr_consent_comms').is(':checked') ? 1 : 0,
                cookie_personalization: $('#mr_cookie_personalization').is(':checked') ? 1 : 0,
                cookie_analytics: $('#mr_cookie_analytics').is(':checked') ? 1 : 0,
                cookie_marketing: $('#mr_cookie_marketing').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(res) {
                    $btn.prop('disabled', false).html(originalBtnHtml);
                    if (res.success) {
                        // Sync updated cookie preferences with browser storage
                        if (res.data && res.data.cookie_consent) {
                            try {
                                var consentStr = JSON.stringify(res.data.cookie_consent);
                                var d = new Date();
                                d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
                                var secure = location.protocol === 'https:' ? '; Secure' : '';
                                document.cookie = "mr_cookie_consent=" + encodeURIComponent(consentStr) + "; expires=" + d.toUTCString() + "; path=/; SameSite=Lax" + secure;
                                localStorage.setItem("mr_cookie_consent", consentStr);
                                window.dispatchEvent(new CustomEvent('mr_cookie_consent_updated', { detail: res.data.cookie_consent }));
                            } catch(e) {}
                        }

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
        // 4b. DPDP Act 2023: Section 11 - Right to Access (Export Data Archive)
        // -------------------------------------------------------------
        $('#mr-btn-dpdp-export').on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $feedback = $('#mr-dpdp-export-feedback');
            var originalHtml = $btn.html();
            var spinImg = mr_account_vars.loader_url ? '<img src="' + mr_account_vars.loader_url + '" class="mr-spin-btn-icon" alt="" /> ' : '';

            $btn.prop('disabled', true).html(spinImg + 'Compiling Archive...');
            $feedback.removeClass('mr-feedback-success mr-feedback-error').empty();

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mr_dpdp_export_data',
                    _ajax_nonce: mr_account_vars.nonce
                },
                success: function(res) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (res && res.success && res.data && res.data.data) {
                        try {
                            var jsonStr = JSON.stringify(res.data.data, null, 2);
                            var blob = new Blob([jsonStr], { type: 'application/json' });
                            var blobUrl = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = blobUrl;
                            a.download = res.data.filename || ('merarashifal-data-archive-' + Date.now() + '.json');
                            document.body.appendChild(a);
                            a.click();
                            setTimeout(function() {
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(blobUrl);
                            }, 250);

                            $feedback.addClass('mr-feedback-success').html('✓ Personal data archive (.JSON) downloaded successfully.');
                        } catch (err) {
                            $feedback.addClass('mr-feedback-error').html('⚠ Error generating export file.');
                        }
                    } else {
                        $feedback.addClass('mr-feedback-error').html('⚠ ' + (res.data ? res.data.message : 'Could not compile data archive.'));
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    $feedback.addClass('mr-feedback-error').html('⚠ Server connection error while exporting data.');
                }
            });
        });

        // -------------------------------------------------------------
        // 4c. DPDP Act 2023: Section 12 - Right to Erasure / Account Deletion
        // -------------------------------------------------------------
        var $eraseContainer   = $('#mr-dpdp-erase-confirm-container');
        var $eraseInput       = $('#mr-dpdp-erase-confirm-input');
        var $eraseConfirmBtn  = $('#mr-btn-dpdp-erase-confirm');
        var $eraseFeedback    = $('#mr-dpdp-erase-feedback');
        var $challengeSentence = $('#mr-dpdp-challenge-sentence');
        var $refreshChallengeBtn = $('#mr-btn-refresh-challenge');

        function updateEraseChallenge(challenge) {
            if (!challenge || !challenge.word) return;
            if ($challengeSentence.length && challenge.sentence_html) {
                $challengeSentence.html(challenge.sentence_html);
            }
            $eraseInput.attr('data-expected', challenge.word);
            $eraseInput.attr('placeholder', 'Type ' + challenge.word);
            $eraseInput.val('');
            $eraseConfirmBtn.prop('disabled', true);
        }

        function fetchFreshEraseChallenge(callback) {
            var $icon = $refreshChallengeBtn.find('svg');
            $icon.addClass('mr-spin-btn-icon');
            $refreshChallengeBtn.prop('disabled', true);

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mr_dpdp_get_erase_challenge',
                    _ajax_nonce: mr_account_vars.nonce
                },
                success: function(res) {
                    $icon.removeClass('mr-spin-btn-icon');
                    $refreshChallengeBtn.prop('disabled', false);
                    if (res && res.success && res.data) {
                        updateEraseChallenge(res.data);
                        if (typeof callback === 'function') callback(res.data);
                    }
                },
                error: function() {
                    $icon.removeClass('mr-spin-btn-icon');
                    $refreshChallengeBtn.prop('disabled', false);
                }
            });
        }

        // Open collapsible container & immediately fetch fresh security challenge
        $('#mr-btn-dpdp-erase-open').on('click', function(e) {
            e.preventDefault();
            var isOpen = $eraseContainer.is(':visible');
            $eraseContainer.slideToggle(200);
            $eraseFeedback.empty();

            if (!isOpen) {
                fetchFreshEraseChallenge();
            }
        });

        // Refresh Challenge Button Click
        $refreshChallengeBtn.on('click', function(e) {
            e.preventDefault();
            fetchFreshEraseChallenge();
        });

        $('#mr-btn-dpdp-erase-cancel').on('click', function(e) {
            e.preventDefault();
            $eraseContainer.slideUp(200);
            $eraseInput.val('');
            $eraseConfirmBtn.prop('disabled', true);
            $eraseFeedback.empty();
        });

        // Real-time input comparison against dynamic expected token
        $eraseInput.on('input propertychange', function() {
            var val = $(this).val().trim();
            var expected = ($(this).attr('data-expected') || '').trim();
            if (expected && val.toUpperCase() === expected.toUpperCase()) {
                $eraseConfirmBtn.prop('disabled', false);
            } else {
                $eraseConfirmBtn.prop('disabled', true);
            }
        });

        $eraseConfirmBtn.on('click', function(e) {
            e.preventDefault();
            var val = $eraseInput.val().trim();
            var expected = ($eraseInput.attr('data-expected') || '').trim();

            if (!expected || val.toUpperCase() !== expected.toUpperCase()) {
                $eraseFeedback.addClass('mr-feedback-error').html('⚠ Please type the highlighted verification word exactly as displayed.');
                return;
            }

            var spinImg = mr_account_vars.loader_url ? '<img src="' + mr_account_vars.loader_url + '" class="mr-spin-btn-icon" alt="" /> ' : '';
            var originalHtml = $eraseConfirmBtn.html();

            $eraseConfirmBtn.prop('disabled', true).html(spinImg + 'Erasing Account...');
            $eraseFeedback.removeClass('mr-feedback-success mr-feedback-error').empty();

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mr_dpdp_erase_account',
                    _ajax_nonce: mr_account_vars.nonce,
                    confirm_phrase: val
                },
                success: function(res) {
                    if (res && res.success) {
                        $eraseFeedback.addClass('mr-feedback-success').html('✓ ' + (res.data ? res.data.message : 'Account erased. Redirecting...'));
                        setTimeout(function() {
                            window.location.href = (res.data && res.data.redirect_url) ? res.data.redirect_url : '/?mr_dpdp_erased=1';
                        }, 1200);
                    } else {
                        $eraseConfirmBtn.prop('disabled', false).html(originalHtml);
                        $eraseFeedback.addClass('mr-feedback-error').html('⚠ ' + (res.data ? res.data.message : 'Could not complete erasure.'));
                        if (res.data && res.data.new_challenge) {
                            updateEraseChallenge(res.data.new_challenge);
                        }
                    }
                },
                error: function() {
                    $eraseConfirmBtn.prop('disabled', false).html(originalHtml);
                    $eraseFeedback.addClass('mr-feedback-error').html('⚠ Server connection error while processing erasure request.');
                }
            });
        });

        // -------------------------------------------------------------
        // 5. On-Demand 25-Entry Passbook Filter & Pagination (AJAX)
        // -------------------------------------------------------------
        var currentPassbookFilter = 'all';
        var currentPassbookPage   = 1;
        var isPassbookLoading     = false;

        function loadPassbookPage(filter, page) {
            if (isPassbookLoading) return;
            isPassbookLoading = true;
            currentPassbookFilter = filter || 'all';
            currentPassbookPage   = page || 1;

            var $table = $('#mr-passbook-ledger-table');
            var $tbody = $table.find('tbody');
            var $paginationWrap = $('#mr-passbook-pagination-container');
            var $hint = $('#mr-passbook-filter-hint');

            // Update explanation hint immediately
            var hints = {
                'all': 'Showing complete chronological ledger of report orders, credit recharges, and chat sessions.',
                'credits': 'Showing Niyati AI credit awards, pack recharges, and daily grouped consultation sessions.',
                'orders': 'Showing certified horoscope reports and payment receipts with invoices.'
            };
            if ($hint.length && hints[currentPassbookFilter]) {
                $hint.text(hints[currentPassbookFilter]);
            }

            // Visual feedback while loading
            $tbody.css('opacity', '0.45');

            $.ajax({
                url: (window.mr_account_vars && window.mr_account_vars.ajax_url) ? window.mr_account_vars.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mr_get_passbook_page',
                    nonce: (window.mr_account_vars && window.mr_account_vars.nonce) ? window.mr_account_vars.nonce : '',
                    filter: currentPassbookFilter,
                    page: currentPassbookPage
                },
                success: function(res) {
                    $tbody.css('opacity', '1');
                    isPassbookLoading = false;

                    if (res && res.success && res.data) {
                        $tbody.html(res.data.rows_html);
                        $paginationWrap.html(res.data.pagination_html);

                        // Update filter pill counts dynamically
                        if (res.data.counts) {
                            $('.mr-passbook-filter-btn[data-filter="all"] .mr-filter-count').text(res.data.counts.all);
                            $('.mr-passbook-filter-btn[data-filter="credits"] .mr-filter-count').text(res.data.counts.credits);
                            $('.mr-passbook-filter-btn[data-filter="orders"] .mr-filter-count').text(res.data.counts.orders);
                        }
                    } else {
                        var msg = (res && res.data && res.data.message) ? res.data.message : 'Failed to load records.';
                        $tbody.html('<tr><td colspan="5" style="text-align:center; padding:30px; color:#BE123C;">' + msg + '</td></tr>');
                    }
                },
                error: function() {
                    $tbody.css('opacity', '1');
                    isPassbookLoading = false;
                    $tbody.html('<tr><td colspan="5" style="text-align:center; padding:30px; color:#BE123C;">Connection error. Please refresh the page.</td></tr>');
                }
            });
        }

        // Passbook Filter Tab click
        $(document).on('click', '.mr-passbook-filter-btn', function(e) {
            e.preventDefault();
            if ($(this).hasClass('active')) return;
            $('.mr-passbook-filter-btn').removeClass('active');
            $(this).addClass('active');
            var filter = $(this).data('filter') || 'all';
            loadPassbookPage(filter, 1);
        });

        // Passbook Pagination button clicks
        $(document).on('click', '#mr-passbook-pagination .mr-page-num', function(e) {
            e.preventDefault();
            var targetPage = parseInt($(this).data('page'), 10);
            if (!targetPage || targetPage === currentPassbookPage) return;
            loadPassbookPage(currentPassbookFilter, targetPage);
            var $table = $('#mr-passbook-ledger-table');
            if ($table.length) {
                $('html, body').animate({ scrollTop: $table.offset().top - 80 }, 200);
            }
        });

        $(document).on('click', '#mr-passbook-pagination .mr-prev-page', function(e) {
            e.preventDefault();
            if ($(this).is(':disabled') || currentPassbookPage <= 1) return;
            loadPassbookPage(currentPassbookFilter, currentPassbookPage - 1);
            var $table = $('#mr-passbook-ledger-table');
            if ($table.length) {
                $('html, body').animate({ scrollTop: $table.offset().top - 80 }, 200);
            }
        });

        $(document).on('click', '#mr-passbook-pagination .mr-next-page', function(e) {
            e.preventDefault();
            var totalPages = parseInt($('#mr-passbook-pagination').data('total-pages'), 10) || 1;
            if ($(this).is(':disabled') || currentPassbookPage >= totalPages) return;
            loadPassbookPage(currentPassbookFilter, currentPassbookPage + 1);
            var $table = $('#mr-passbook-ledger-table');
            if ($table.length) {
                $('html, body').animate({ scrollTop: $table.offset().top - 80 }, 200);
            }
        });

        // Reports table pagination (Tab 2)
        $(document).on('click', '#mr-reports-pagination .mr-page-num', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var targetPage = parseInt($(this).data('page'), 10);
            paginateReportsTable($wrap, targetPage);
        });

        $(document).on('click', '#mr-reports-pagination .mr-prev-page', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var current = parseInt($wrap.data('current-page') || 1, 10);
            if (current > 1) {
                paginateReportsTable($wrap, current - 1);
            }
        });

        $(document).on('click', '#mr-reports-pagination .mr-next-page', function(e) {
            e.preventDefault();
            var $wrap = $(this).closest('.mr-pagination-wrap');
            var current = parseInt($wrap.data('current-page') || 1, 10);
            var total = parseInt($wrap.data('total-pages') || 1, 10);
            if (current < total) {
                paginateReportsTable($wrap, current + 1);
            }
        });

        function paginateReportsTable($pagination, page) {
            var total = parseInt($pagination.data('total-pages') || 1, 10);
            if (page < 1 || page > total) return;
            $pagination.data('current-page', page);
            $('.mr-report-row').hide();
            $('.mr-report-row[data-page="' + page + '"]').show();
            $pagination.find('.mr-page-num').removeClass('active');
            $pagination.find('.mr-page-num[data-page="' + page + '"]').addClass('active');
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

        // -------------------------------------------------------------
        // 8. Contact & Support Ticket Form with Single Picture Attachment
        // -------------------------------------------------------------
        var $dropzone      = $('#mr-file-dropzone');
        var $fileInput     = $('#mr_support_attachment');
        var $dropzoneInner = $('#mr-dropzone-prompt');
        var $previewCard   = $('#mr-file-preview-card');
        var $previewImg    = $('#mr-file-preview-img');
        var $fileName      = $('#mr-file-name');
        var $fileSize      = $('#mr-file-size');
        var $fileError     = $('#mr-file-upload-error');
        var $supportForm   = $('#mr-support-ticket-form');
        var $supportFeedback = $('#mr-support-ticket-feedback');

        function formatBytes(bytes, decimals) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var dm = decimals < 0 ? 0 : decimals || 1;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function clearAttachment() {
            $fileInput.val('');
            $previewImg.attr('src', '');
            $fileName.text('');
            $fileSize.text('');
            $previewCard.hide();
            $dropzoneInner.show();
            $fileError.hide().text('');
        }

        function handleFileSelection(file) {
            $fileError.hide().text('');
            if (!file) {
                clearAttachment();
                return;
            }

            // Verify single file is an image
            var validMimes = ['image/jpeg', 'image/png', 'image/webp'];
            var validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            var fileExt = file.name.split('.').pop().toLowerCase();

            if (validMimes.indexOf(file.type) === -1 && validExtensions.indexOf(fileExt) === -1) {
                $fileError.text('Only image files (JPG, PNG, WEBP) are allowed.').show();
                clearAttachment();
                return;
            }

            // 5MB Limit
            var maxBytes = 5 * 1024 * 1024;
            if (file.size > maxBytes) {
                $fileError.text('The image exceeds the 5MB size limit. Please choose a smaller picture.').show();
                clearAttachment();
                return;
            }

            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                $previewImg.attr('src', e.target.result);
                $fileName.text(file.name);
                $fileSize.text(formatBytes(file.size));
                $dropzoneInner.hide();
                $previewCard.show();
            };
            reader.readAsDataURL(file);
        }

        // Dropzone interactions
        $dropzone.on('click', function(e) {
            if ($(e.target).closest('#mr-btn-clear-attachment').length) {
                return;
            }
            $fileInput.trigger('click');
        });

        $dropzone.on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $fileInput.trigger('click');
            }
        });

        $fileInput.on('change', function() {
            var files = this.files;
            if (files && files.length > 0) {
                handleFileSelection(files[0]);
            } else {
                clearAttachment();
            }
        });

        $('#mr-btn-clear-attachment').on('click', function(e) {
            e.stopPropagation();
            clearAttachment();
        });

        // Drag & Drop events
        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('mr-drag-over');
        });

        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('mr-drag-over');
        });

        $dropzone.on('drop', function(e) {
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                // Take only the first picture
                var file = dt.files[0];
                handleFileSelection(file);

                // Set file input if supported by DataTransfer
                try {
                    var container = new DataTransfer();
                    container.items.add(file);
                    $fileInput[0].files = container.files;
                } catch(err) {
                    // Fallback for browsers where DataTransfer cannot assign to input.files
                }
            }
        });

        // Form submission via AJAX
        $supportForm.on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#mr-btn-submit-support');
            var $btnText = $btn.find('.mr-btn-text');
            var $btnSpinner = $btn.find('.mr-btn-spinner');

            $supportFeedback.hide().removeClass('success error').text('');

            // Validate question input
            var message = $.trim($('#mr_support_message').val());

            if (!message) {
                $supportFeedback.addClass('error').text('Please enter your question before submitting.').fadeIn(200);
                $('#mr_support_message').focus();
                return false;
            }

            var formData = new FormData(this);
            formData.append('action', 'mr_submit_support_ticket');
            formData.append('nonce', mr_account_vars.nonce);

            $btn.prop('disabled', true);
            $btnText.hide();
            $btnSpinner.show();

            $.ajax({
                url: mr_account_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        $supportFeedback.addClass('success').html(
                            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px; margin-right:6px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' +
                            response.data.message
                        ).fadeIn(200);

                        // Clear subject, message and attachment
                        $('#mr_support_subject').val('');
                        $('#mr_support_message').val('');
                        clearAttachment();
                    } else {
                        var errMsg = (response && response.data && response.data.message) ? response.data.message : 'An error occurred while submitting your ticket. Please write to support@merarashifal.com.';
                        $supportFeedback.addClass('error').html(
                            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px; margin-right:6px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>' +
                            errMsg
                        ).fadeIn(200);
                    }
                },
                error: function() {
                    $supportFeedback.addClass('error').html(
                        'A network error occurred. Please contact us directly at <a href="mailto:support@merarashifal.com" style="color:#B91C1C; font-weight:700;">support@merarashifal.com</a>.'
                    ).fadeIn(200);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnSpinner.hide();
                }
            });
        });

        // -------------------------------------------------------------
        // Header Auth Button Auto-Switch (Sign In / Sign Up -> Account)
        // -------------------------------------------------------------
        (function initHeaderAuthButtonSwitch() {
            function checkLoggedIn() {
                if ($('body').hasClass('logged-in')) return true;
                if (window.mr_account_vars && (window.mr_account_vars.is_logged_in === true || window.mr_account_vars.is_logged_in === '1' || window.mr_account_vars.is_logged_in === 1)) return true;
                if (document.cookie && document.cookie.indexOf('wordpress_logged_in_') !== -1) return true;
                return false;
            }

            function updateHeaderButtons() {
                if (!checkLoggedIn()) return;

                if (!$('body').hasClass('logged-in')) {
                    $('body').addClass('logged-in');
                }

                $('.elementor-element-0bf8cb1, .elementor-element-b12e20f, header a[href*="my-account"], .elementor-location-header a[href*="my-account"], .ast-header-break-point a[href*="my-account"]').each(function() {
                    var $btn = $(this).is('a') ? $(this) : $(this).find('a.elementor-button');
                    var $text = $btn.find('.elementor-button-text');
                    if ($text.length) {
                        var str = $text.text().trim();
                        if (str === 'Sign In / Sign Up' || str === 'Sign In' || str.indexOf('Sign') !== -1) {
                            $text.text('Account');
                        }
                    }
                });
            }

            updateHeaderButtons();
            setTimeout(updateHeaderButtons, 300);
            setTimeout(updateHeaderButtons, 1000);
        })();

    });
})(jQuery);
