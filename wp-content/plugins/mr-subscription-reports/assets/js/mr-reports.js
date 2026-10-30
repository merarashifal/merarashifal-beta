/**
 * ZodiacBEE / MeraRashifal - Interactive Subscription, A la Carte & PG Simulation Engine
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // State Variables
        var currentCurrency = 'USD';
        var currentSystem   = 'vedic';
        var currentCycle    = 'yearly';

        var activeOrderType   = ''; // 'pack' or 'alacarte'
        var activePackKey     = '';
        var activeReportSlug  = '';
        var activeItemName    = '';
        var activeItemPrice   = '';

        // Elements
        var pgModal        = $('#mr-pg-modal');
        var detailsModal   = $('#mr-details-modal');
        var sampleModal    = $('#mr-sample-modal');
        var progressBanner = $('#mr-progress-banner');
        var progressBar    = $('#mr-banner-bar');
        var progressStatus = $('#mr-banner-status');
        var progressTitle  = $('#mr-banner-title');
        var btnGoReport    = $('#mr-go-to-report-btn');

        // =========================================================
        // MeraRashifal Luxury Brand Notification & Auth Dialog Engine
        // =========================================================
        window.mrNotify = {
            show: function(opts) {
                opts = opts || {};
                var type = opts.type || 'info';
                var modal = $('#mr-notify-modal');
                if (!modal.length) {
                    if (opts.primaryUrl) {
                        window.location.href = opts.primaryUrl;
                    }
                    return;
                }

                // Set type class on icon wrap
                var iconWrap = $('#mr-notify-icon-wrap');
                iconWrap.attr('class', 'mr-notify-icon-wrap type-' + type);

                var iconEl = $('#mr-notify-icon');
                if (type === 'auth') {
                    iconEl.attr('class', 'fa-solid fa-user-lock');
                } else if (type === 'success') {
                    iconEl.attr('class', 'fa-solid fa-circle-check');
                } else if (type === 'warning') {
                    iconEl.attr('class', 'fa-solid fa-triangle-exclamation');
                } else if (type === 'error') {
                    iconEl.attr('class', 'fa-solid fa-circle-xmark');
                } else {
                    iconEl.attr('class', 'fa-solid fa-sparkles');
                }

                // Set Eyebrow
                var eyebrow = opts.eyebrow || (type === 'auth' ? 'Account Authentication' : 'MeraRashifal Advisory');
                $('#mr-notify-eyebrow').html('<i class="fa-solid fa-sparkles" style="color:#D9A441;"></i> ' + eyebrow);

                // Set Title
                $('#mr-notify-title').text(opts.title || 'MeraRashifal Notice');

                // Set Message
                $('#mr-notify-message').html(opts.message || '');

                // Setup Primary Button
                var primaryBtn = $('#mr-notify-primary-btn');
                var primaryText = opts.primaryText || (type === 'auth' ? '<i class="fa-brands fa-google" style="margin-right:8px;"></i> Sign In with Google &rarr;' : 'Understood');
                $('#mr-notify-btn-text').html(primaryText);
                if (opts.primaryUrl) {
                    primaryBtn.attr('href', opts.primaryUrl);
                } else if (type === 'auth') {
                    primaryBtn.attr('href', (typeof mr_reports_vars !== 'undefined' && (mr_reports_vars.account_url || mr_reports_vars.login_url)) || '/my-account/');
                } else {
                    primaryBtn.attr('href', '#');
                }

                primaryBtn.off('click').on('click', function(e) {
                    var targetDestination = opts.primaryUrl || primaryBtn.attr('href');
                    if (targetDestination && targetDestination !== '#') {
                        e.preventDefault();
                        window.location.href = targetDestination;
                        return;
                    }
                    if (typeof opts.onPrimary === 'function') {
                        e.preventDefault();
                        opts.onPrimary();
                    }
                    modal.fadeOut(150);
                });

                // Setup Secondary Button
                var secBtn = $('#mr-notify-secondary-btn');
                if (opts.secondaryText === false) {
                    secBtn.hide();
                } else {
                    secBtn.show().text(opts.secondaryText || 'Stay on this page');
                    secBtn.off('click').on('click', function() {
                        if (typeof opts.onSecondary === 'function') {
                            opts.onSecondary();
                        }
                        modal.fadeOut(150);
                    });
                }

                modal.fadeIn(200);
            },

            auth: function(opts) {
                opts = opts || {};
                var accountUrl = (typeof mr_reports_vars !== 'undefined' && (mr_reports_vars.account_url || mr_reports_vars.login_url)) ||
                                 (typeof mr_razorpay_vars !== 'undefined' && (mr_razorpay_vars.account_url || mr_razorpay_vars.login_url)) ||
                                 '/my-account/';

                // In any case, sign-in always takes user to the /my-account page
                var targetUrl = accountUrl;

                this.show({
                    type: 'auth',
                    eyebrow: opts.eyebrow || 'Account Authentication',
                    title: opts.title || 'Sign In to Continue',
                    message: opts.message || 'Sign in to your sacred MeraRashifal account to order personalized Vedic reports or value packs.',
                    primaryText: opts.primaryText || '<i class="fa-brands fa-google" style="margin-right:8px;"></i> Sign In with Google &rarr;',
                    primaryUrl: targetUrl,
                    secondaryText: 'Stay on this page'
                });
            },

            alert: function(msg, title, type, callback) {
                this.show({
                    type: type || 'warning',
                    title: title || 'MeraRashifal Notice',
                    message: msg,
                    primaryText: 'Understood',
                    secondaryText: false,
                    onPrimary: callback
                });
            },

            close: function() {
                $('#mr-notify-modal').fadeOut(150);
            },

            toast: function(message, type, duration) {
                type = type || 'info';
                duration = duration || 3500;
                var container = $('#mr-toast-container');
                if (!container.length) {
                    $('body').append('<div id="mr-toast-container" class="mr-toast-container" aria-live="polite"></div>');
                    container = $('#mr-toast-container');
                }

                var iconMap = {
                    success: 'fa-circle-check',
                    warning: 'fa-triangle-exclamation',
                    error: 'fa-circle-xmark',
                    info: 'fa-sparkles'
                };
                var icon = iconMap[type] || 'fa-bell';

                var toastHtml = $(
                    '<div class="mr-toast-item toast-' + type + '">' +
                        '<i class="fa-solid ' + icon + '"></i>' +
                        '<span>' + message + '</span>' +
                    '</div>'
                );

                container.append(toastHtml);

                setTimeout(function() {
                    toastHtml.css({ opacity: 0, transform: 'translateY(-12px)' });
                    setTimeout(function() { toastHtml.remove(); }, 300);
                }, duration);
            }
        };

        // Close handlers for Notify modal
        $(document).on('click', '.mr-notify-close-btn, #mr-notify-modal', function(e) {
            if ($(e.target).is('#mr-notify-modal') || $(e.target).hasClass('mr-notify-close-btn') || $(e.target).closest('.mr-notify-close-btn').length) {
                window.mrNotify.close();
            }
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#mr-notify-modal').is(':visible')) {
                window.mrNotify.close();
            }
        });

        // Comparison Table Data
        var comparisonData = {
            vedic: [
                { name: 'Yearly Horoscope Report (Vedic)', m: 1, q: 2, h: 4, y: 6 },
                { name: 'Monthly Horoscope Report (Vedic)', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Vedic Kundli & Life Predictions Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Vedic Mahadasha & Antardasha Life Timing Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Vedic Kundli Dosh & Remedial Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Tarot Reading of the Day Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Numerology Report', m: 1, q: 2, h: 4, y: 8 }
            ],
            western: [
                { name: 'Yearly Horoscope Report (Western)', m: 1, q: 2, h: 4, y: 6 },
                { name: 'Monthly Horoscope Report (Western)', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Natal Chat Reading Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Synastry Chart Reading Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Transit Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Tarot Reading of the Day Report', m: 2, q: 4, h: 6, y: 12 },
                { name: 'Numerology Report', m: 1, q: 2, h: 4, y: 8 }
            ]
        };

        // Render Comparison Table
        function renderComparisonTable() {
            var tbody = $('#mr-comparison-tbody');
            if (!tbody.length) return;

            var rows = comparisonData[currentSystem] || comparisonData.vedic;
            var html = '';
            rows.forEach(function(r) {
                html += '<tr>' +
                    '<td><strong style="color:#ffffff;">' + r.name + '</strong></td>' +
                    '<td style="text-align:center;">' + r.m + '</td>' +
                    '<td style="text-align:center;">' + r.q + '</td>' +
                    '<td style="text-align:center;">' + r.h + '</td>' +
                    '<td style="text-align:center; font-weight:bold; color:#fbbf24;">' + r.y + '</td>' +
                '</tr>';
            });
            tbody.html(html);
        }
        renderComparisonTable();

        // 1. Currency Switcher Handler
        $(document).on('click', '.mr-curr-btn', function() {
            var curr = $(this).data('currency');
            currentCurrency = curr;

            $('.mr-curr-btn').removeClass('active');
            $('.mr-curr-btn[data-currency="' + curr + '"]').addClass('active');

            // Update Pack Card Prices
            $('.mr-pack-card').each(function() {
                var amt = (curr === 'CAD') ? $(this).data('cad') : $(this).data('usd');
                $(this).find('.mr-price-amount').text('$' + amt);
                $(this).find('.mr-price-curr').text(curr);
            });

            // Update A la Carte Prices
            $('.mr-alacarte-card').each(function() {
                var amt = (curr === 'CAD') ? $(this).data('cad') : $(this).data('usd');
                $(this).find('.mr-price-val').text('$' + parseFloat(amt).toFixed(2));
                $(this).find('.mr-price-curr').text(curr);
            });
        });

        // 2. Astrology System Switcher (Vedic vs Western)
        $(document).on('click', '.mr-toggle-btn', function() {
            var sys = $(this).data('system');
            currentSystem = sys;

            $('.mr-toggle-btn').removeClass('active');
            $(this).addClass('active');

            updatePackVisibility();
            renderComparisonTable();
        });

        // 3. Billing Cycle Switcher (Monthly, Quarterly, Half-Yearly, Yearly)
        $(document).on('click', '.mr-cycle-btn', function() {
            var cycle = $(this).data('cycle');
            currentCycle = cycle;

            $('.mr-cycle-btn').removeClass('active');
            $(this).addClass('active');

            updatePackVisibility();
        });

        function updatePackVisibility() {
            $('.mr-pack-card').hide();
            $('.mr-pack-card[data-system="' + currentSystem + '"][data-cycle="' + currentCycle + '"]').fadeIn(200);
        }

        // 4. A la Carte Category Filter
        $(document).on('click', '.mr-filter-btn', function() {
            var filter = $(this).data('filter');
            $('.mr-filter-btn').removeClass('active');
            $(this).addClass('active');

            if (filter === 'all') {
                $('.mr-alacarte-card').fadeIn(200);
            } else {
                $('.mr-alacarte-card').hide();
                $('.mr-alacarte-card[data-system="' + filter + '"]').fadeIn(200);
            }
        });

        // 5. Open Simulated Payment Gateway (PG) for Packs
        $(document).on('click', '.mr-btn-buy-pack', function(e) {
            e.preventDefault();
            var card = $(this).closest('.mr-pack-card');
            activeOrderType = 'pack';
            activePackKey = $(this).data('pack-key');
            activeItemName = card.data('name');
            var price = (currentCurrency === 'CAD') ? card.data('cad') : card.data('usd');
            activeItemPrice = '$' + price + ' ' + currentCurrency;

            $('#mr-pg-item-name').text(activeItemName);
            $('#mr-pg-order-type').text('Subscription Pack (' + currentCurrency + ')');
            $('#mr-pg-total-amount').text(activeItemPrice);
            $('#mr-pg-error-msg').hide();

            pgModal.fadeIn(200);
        });

        // 6. Open Simulated Payment Gateway (PG) for A la Carte
        $(document).on('click', '.mr-btn-buy-alacarte', function(e) {
            e.preventDefault();
            var card = $(this).closest('.mr-alacarte-card, .mr-sales-card');
            activeOrderType = 'alacarte';
            activeReportSlug = card.data('slug') || $(this).data('slug');
            activeItemName = card.data('title') || $(this).data('title');
            
            var inrPrice = $(this).data('price');
            if (inrPrice === undefined && card.length) {
                inrPrice = card.data('price');
            }

            // If free report (₹0), bypass payment and open details form immediately
            if (inrPrice !== undefined && parseFloat(inrPrice) === 0) {
                openDetailsModal(activeReportSlug, activeItemName);
                return;
            }

            // Require login before proceeding to payment or details
            var isLoggedIn = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.is_logged_in) ||
                             (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.is_logged_in);

            if (!isLoggedIn) {
                var loginUrl = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.login_url) ||
                               (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.login_url) ||
                               '/wp-login.php';
                if (window.mrNotify && typeof window.mrNotify.auth === 'function') {
                    window.mrNotify.auth({
                        loginUrl: loginUrl,
                        message: 'Please sign in to order your personalized ' + (activeItemName ? activeItemName.replace(/^Generate\s+/i, '') : 'report') + ' or subscription pack.'
                    });
                } else {
                    window.location.href = loginUrl;
                }
                return;
            }

            if (inrPrice !== undefined) {
                activeItemPrice = '₹' + inrPrice;
                $('#mr-pg-order-type').text('A la Carte Single Report (INR)');
            } else {
                var price = (currentCurrency === 'CAD') ? card.data('cad') : card.data('usd');
                activeItemPrice = '$' + parseFloat(price).toFixed(2) + ' ' + currentCurrency;
                $('#mr-pg-order-type').text('A la Carte Single Report (' + currentCurrency + ')');
            }

            $('#mr-pg-item-name').text(activeItemName);
            $('#mr-pg-total-amount').text(activeItemPrice);
            $('#mr-pg-error-msg').hide();

            pgModal.fadeIn(200);
        });

        // 7. Modal Close handlers
        $(document).on('click', '.mr-modal-close, .mr-modal', function(e) {
            if ($(e.target).hasClass('mr-modal') || $(e.target).hasClass('mr-modal-close') || $(e.target).closest('.mr-modal-close').length) {
                var m = $(this).closest('.mr-modal');
                if ($(e.target).hasClass('mr-modal')) {
                    m = $(e.target);
                }
                m.removeClass('is-open').fadeOut(120, function() {
                    $(this).attr('style', 'display: none !important;');
                });
                m.attr('style', 'display: none !important;');
            }
        });

        // 8. Handle Simulated PG - Success Button
        $('#mr-btn-pg-success').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing Payment...');

            $.ajax({
                url: mr_reports_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'mr_simulate_payment',
                    nonce: mr_reports_vars.nonce,
                    order_type: activeOrderType,
                    pack_key: activePackKey,
                    report_slug: activeReportSlug,
                    currency: currentCurrency,
                    sim_status: 'success'
                },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-lock"></i> Complete Payment (Simulate Success)');
                    if (res.success) {
                        pgModal.fadeOut(150);

                        if (activeOrderType === 'pack') {
                            if (window.mrNotify && typeof window.mrNotify.toast === 'function') {
                                window.mrNotify.toast('Payment Successful! ' + res.data.message, 'success');
                            }
                            window.location.href = res.data.redirect || mr_reports_vars.catalog_url;
                        } else {
                            // A la Carte: Open Birth Details Modal immediately!
                            openDetailsModal(activeReportSlug, activeItemName);
                        }
                    } else {
                        $('#mr-pg-error-msg').show().find('p').text(res.data.message || 'Payment error.');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-lock"></i> Complete Payment (Simulate Success)');
                    $('#mr-pg-error-msg').show().find('p').text('Gateway communication error.');
                }
            });
        });

        // 9. Handle Simulated PG - Failure Button
        $('#mr-btn-pg-fail').on('click', function() {
            $('#mr-pg-error-msg').slideDown(200).find('p').text('Transaction declined: Test bank simulation returned decline code 05 (Do not honor). Please retry or select another payment method.');
        });

        // 10. Direct Generate with Token (User already has tokens)
        $(document).on('click', '.mr-btn-generate-direct', function(e) {
            e.preventDefault();
            activeReportSlug = $(this).data('slug');
            activeItemName   = $(this).data('title');
            openDetailsModal(activeReportSlug, activeItemName);
        });

        // Helper: Open Birth Details Modal with Dynamic Fields
        window.openDetailsModal = function(slug, title) {
            var isFree = (slug === 'yearly_horoscope_vedic' || slug === 'yearly_horoscope_snapshot');
            var isLoggedIn = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.is_logged_in) ||
                             (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.is_logged_in);

            if (!isFree && !isLoggedIn) {
                var loginUrl = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.login_url) ||
                               (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.login_url) ||
                               '/wp-login.php';
                if (window.mrNotify && typeof window.mrNotify.auth === 'function') {
                    window.mrNotify.auth({
                        loginUrl: loginUrl,
                        message: 'Please sign in to order or generate your personalized ' + (title ? title.replace(/^Generate\s+/i, '') : 'report') + '.'
                    });
                } else {
                    window.location.href = loginUrl;
                }
                return;
            }

            activeReportSlug = slug;
            activeItemName   = title;
            $('#mr_report_slug').val(slug);
            var cleanTitle = title ? title.replace(/^Generate\s+/i, '').replace(/Report\s+Report/i, 'Report') : 'Yearly Horoscope Detailed Report (2026-2027)';
            $('#mr-modal-report-title').text(cleanTitle);

            // Clear previous errors & error highlights
            $('#mr-modal-error-box').hide().find('.mr-error-text').text('');
            $('#mr-birth-details-form input, #mr-birth-details-form select').removeClass('mr-input-error');

            if (slug === 'kundli_matching') {
                $('#mr-matching-fields').show();
                $('#mr-single-person-fields').hide();
                $('#mr-palm-upload-fields').hide();

                $('#mr_boy_name, #mr_boy_dob, #mr_boy_tob, #mr_boy_pob').prop('required', true);
                $('#mr_girl_name, #mr_girl_dob, #mr_girl_tob, #mr_girl_pob').prop('required', true);
                $('#mr_person_name, #mr_dob, #mr_tob, #mr_pob').prop('required', false);

                setTimeout(function() {
                    initCityInput('#mr_boy_pob', '#mr-boy-pob-results', '#mr_boy_pob_lat', '#mr_boy_pob_lng', '#mr_boy_pob_tz');
                    initCityInput('#mr_girl_pob', '#mr-girl-pob-results', '#mr_girl_pob_lat', '#mr_girl_pob_lng', '#mr_girl_pob_tz');
                }, 150);
            } else if (slug === 'palmistry_report') {
                $('#mr-matching-fields').hide();
                $('#mr-single-person-fields').show();
                $('#mr-palm-upload-fields').show();

                $('#mr_boy_name, #mr_boy_dob, #mr_boy_tob, #mr_boy_pob').prop('required', false);
                $('#mr_girl_name, #mr_girl_dob, #mr_girl_tob, #mr_girl_pob').prop('required', false);
                $('#mr_person_name, #mr_dob, #mr_tob, #mr_pob').prop('required', true);

                setTimeout(function() {
                    initCityInput('#mr_pob', '#mr-pob-results', '#mr_pob_lat', '#mr_pob_lng', '#mr_pob_tz');
                }, 150);
            } else {
                $('#mr-matching-fields').hide();
                $('#mr-single-person-fields').show();
                $('#mr-palm-upload-fields').hide();

                $('#mr_boy_name, #mr_boy_dob, #mr_boy_tob, #mr_boy_pob').prop('required', false);
                $('#mr_girl_name, #mr_girl_dob, #mr_girl_tob, #mr_girl_pob').prop('required', false);
                $('#mr_person_name, #mr_dob, #mr_tob, #mr_pob').prop('required', true);

                setTimeout(function() {
                    initCityInput('#mr_pob', '#mr-pob-results', '#mr_pob_lat', '#mr_pob_lng', '#mr_pob_tz');
                }, 150);
            }

            // Auto-populate from user saved astro details if available
            if (window.mr_reports_vars) {
                if (mr_reports_vars.user_email && !$('#mr_recipient_email').val()) {
                    $('#mr_recipient_email').val(mr_reports_vars.user_email);
                }
                if (mr_reports_vars.user_astro_details) {
                    var d = mr_reports_vars.user_astro_details;
                    if (!$('#mr_person_name').val() && d.name) $('#mr_person_name').val(d.name);
                    if (!$('#mr_boy_name').val() && d.name) $('#mr_boy_name').val(d.name);
                    if (d.gender) $('#mr_gender').val(d.gender);
                    if (!$('#mr_dob').val() && d.dob) $('#mr_dob').val(d.dob);
                    if (!$('#mr_boy_dob').val() && d.dob) $('#mr_boy_dob').val(d.dob);
                    if (!$('#mr_tob').val() && d.tob) $('#mr_tob').val(d.tob);
                    if (!$('#mr_boy_tob').val() && d.tob) $('#mr_boy_tob').val(d.tob);
                    if (!$('#mr_pob').val() && d.pob) $('#mr_pob').val(d.pob);
                    if (!$('#mr_boy_pob').val() && d.pob) $('#mr_boy_pob').val(d.pob);
                    if (!$('#mr_pob_lat').val() && d.lat) $('#mr_pob_lat').val(d.lat);
                    if (!$('#mr_boy_pob_lat').val() && d.lat) $('#mr_boy_pob_lat').val(d.lat);
                    if (!$('#mr_pob_lng').val() && d.lon) $('#mr_pob_lng').val(d.lon);
                    if (!$('#mr_boy_pob_lng').val() && d.lon) $('#mr_boy_pob_lng').val(d.lon);
                    if (!$('#mr_pob_tz').val() && d.tz) $('#mr_pob_tz').val(d.tz);
                    if (!$('#mr_boy_pob_tz').val() && d.tz) $('#mr_boy_pob_tz').val(d.tz);
                }
            }

            detailsModal.addClass('is-open');
            if (detailsModal.length && detailsModal[0].style) {
                detailsModal[0].style.setProperty('display', 'flex', 'important');
            }
            detailsModal.fadeIn(150);
        }

        // =========================================================================
        // Unified City Autocomplete (High-Speed In-Memory Cache + Persistent Loader)
        // Fetches each query ONLY 1 TIME and keeps loader running till place is selected
        // =========================================================================
        var citySearchTimer = null;
        var activeIndex = -1;
        var cityQueryCache = {}; // Query cache dictionary (1-time fetch guarantee)
        var activeCityAjax = null; // In-flight request handle for aborting duplicates

        function ensureCityLoader(input) {
            var $input = $(input);
            var $wrap = $input.closest('.mr-city-input-wrap, .mr-pob-input-wrap');
            if (!$wrap.length) {
                // If not wrapped, wrap input dynamically
                $input.wrap('<div class="mr-city-input-wrap" style="position:relative; width:100%;"></div>');
                $wrap = $input.closest('.mr-city-input-wrap');
            }
            var $loader = $wrap.find('.mr-city-loader');
            if (!$loader.length) {
                $loader = $('<span class="mr-city-loader"><span class="mr-city-spinner-ring"></span></span>');
                $input.after($loader);
            }
            return $loader;
        }

        function showCityLoader(input) {
            var $loader = ensureCityLoader(input);
            $loader.addClass('is-active').show();
        }

        function hideCityLoader(input) {
            var $input = $(input);
            var $wrap = $input.closest('.mr-city-input-wrap, .mr-pob-input-wrap, .mr-form-group, .mr-pob-group');
            $wrap.find('.mr-city-loader').removeClass('is-active').hide();
        }

        function renderCityResults(input, resultsBox, list, targetId) {
            if (list && list.length > 0) {
                var html = '';
                list.forEach(function(city, idx) {
                    html += '<div class="mr-city-item" data-index="' + idx + '" data-target="' + targetId + '" data-name="' + (city.name || '').replace(/"/g, '&quot;') + '" data-lat="' + (city.lat || '') + '" data-lon="' + (city.lon || '') + '" data-tz="' + (city.tz || '5.5') + '">';
                    html += '<i class="fa-solid fa-location-dot" style="margin-right:6px; color:#F58220;"></i> <span>' + city.name + '</span>';
                    html += '</div>';
                });
                resultsBox.html(html).show();
            } else {
                resultsBox.hide().empty();
            }
            // CRITICAL REQUIREMENT: Loader continues running while user browses results!
            // Loader is ONLY stopped when user selects a place from the list.
        }

        // Ensure city inputs are always editable on focus/click
        $(document).on('focus click', '.mr-city-input', function() {
            this.readOnly = false;
            this.disabled = false;
            $(this).removeAttr('readonly disabled').css({
                'pointer-events': 'auto',
                'cursor': 'text',
                'user-select': 'text'
            });
        });

        // Instant search on user input with deduplication and 1-time fetch cache
        $(document).on('input paste', '.mr-city-input', function(e) {
            var input = $(this);
            var query = (input.val() || '').trim();
            input.removeClass('mr-input-error');
            clearTimeout(citySearchTimer);
            activeIndex = -1;

            var id = input.attr('id') || 'pob';
            var resultsBox = $('#' + id.replace(/_/g, '-') + '-results');
            if (!resultsBox.length) {
                resultsBox = $('#' + id + '-results');
            }
            if (!resultsBox.length) {
                resultsBox = input.siblings('.mr-city-results, .mr-city-dropdown');
            }

            if (query.length < 2) {
                resultsBox.hide().empty();
                hideCityLoader(input);
                if (activeCityAjax && activeCityAjax.readyState !== 4) {
                    activeCityAjax.abort();
                }
                return;
            }

            // 1. Keep the loader running on this box from the moment query starts
            showCityLoader(input);

            var cacheKey = query.toLowerCase();

            // 2. CHECK CACHE: If already fetched once, reuse instantly (0ms latency, NO duplicate requests!)
            if (cityQueryCache.hasOwnProperty(cacheKey)) {
                renderCityResults(input, resultsBox, cityQueryCache[cacheKey], id);
                return;
            }

            // 3. Debounce network fetch so typing a word sends ONLY 1 request, aborting previous in-flight calls
            citySearchTimer = setTimeout(function() {
                if (activeCityAjax && activeCityAjax.readyState !== 4) {
                    activeCityAjax.abort();
                }

                activeCityAjax = $.ajax({
                    url: mr_reports_vars.ajax_url,
                    type: 'GET',
                    data: {
                        action: 'mr_city_search',
                        q: query
                    },
                    dataType: 'json',
                    success: function(res) {
                        var list = (res.success && res.data && res.data.length > 0) ? res.data : [];
                        // Save in memory cache (ensures each query is fetched ONLY 1 TIME)
                        cityQueryCache[cacheKey] = list;
                        renderCityResults(input, resultsBox, list, id);
                    },
                    error: function(xhr, status) {
                        if (status !== 'abort') {
                            resultsBox.hide();
                        }
                    }
                });
            }, 260);
        });

        // Keyboard navigation across city dropdown suggestions
        $(document).on('keydown', '.mr-city-input', function(e) {
            var id = $(this).attr('id') || 'pob';
            var resultsBox = $('#' + id.replace(/_/g, '-') + '-results');
            if (!resultsBox.length) resultsBox = $('#' + id + '-results');
            if (!resultsBox.length) resultsBox = $(this).siblings('.mr-city-results, .mr-city-dropdown');
            var items = resultsBox.find('.mr-city-item');
            if (!items.length || !resultsBox.is(':visible')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                items.removeClass('active').eq(activeIndex).addClass('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                items.removeClass('active').eq(activeIndex).addClass('active');
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                items.eq(activeIndex).trigger('click');
            } else if (e.key === 'Escape') {
                resultsBox.hide();
            }
        });

        // Handle suggestion click from dropdown (SELECTION EVENT)
        // STOPS AND HIDES LOADER WHEN THE USER SELECTS THE PLACE FROM THE LIST
        $(document).on('click', '.mr-city-item', function(e) {
            e.stopPropagation();
            var targetId = $(this).data('target');
            var cityName = $(this).data('name');
            var lat      = $(this).data('lat');
            var lon      = $(this).data('lon');
            var tz       = $(this).data('tz');

            var input = $('#' + targetId);
            if (!input.length) {
                input = $(this).closest('.mr-city-input-wrap, .mr-pob-group, .mr-form-group, .mr-field-group').find('.mr-city-input');
            }

            input.val(cityName).removeClass('mr-input-error');
            $('#' + targetId + '_lat').val(lat);
            $('#' + targetId + '_lng').val(lon);
            $('#' + targetId + '_tz').val(tz);

            var resultsBox = $('#' + targetId.replace(/_/g, '-') + '-results');
            if (!resultsBox.length) resultsBox = $('#' + targetId + '-results');
            if (!resultsBox.length) resultsBox = $(this).closest('.mr-city-results, .mr-city-dropdown');
            resultsBox.hide().empty();

            // USER HAS SELECTED PLACE FROM LIST: STOP LOADER NOW!
            hideCityLoader(input);
        });

        // Also stop loader when Google Places Autocomplete prediction (.pac-item) is clicked
        $(document).on('click', '.pac-item', function() {
            var activeInput = document.activeElement;
            if (activeInput) {
                hideCityLoader(activeInput);
            }
            $('.mr-city-loader').removeClass('is-active').hide();
        });

        // Close city dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.mr-city-input, .mr-city-results, .mr-city-dropdown').length) {
                $('.mr-city-results, .mr-city-dropdown').hide();
            }
        });

        // Helper function for backwards compatibility
        function initCityInput() {}

        // Palm Photo Select Trigger & Feedback
        $(document).on('click', '#mr-btn-choose-palm', function(e) {
            e.preventDefault();
            $('#mr_palm_image').trigger('click');
        });
        $(document).on('change', '#mr_palm_image', function() {
            if (this.files && this.files.length > 0) {
                $('#mr-palm-file-status').html('<i class="fa-solid fa-circle-check"></i> Selected: ' + this.files[0].name).show();
            } else {
                $('#mr-palm-file-status').hide();
            }
        });

        // 11. Sample Reading Preview - Publication Grade Document Reader
        $(document).on('click', '.mr-link-sample', function(e) {
            e.preventDefault();
            var title = $(this).data('title') || 'Astrological Report';
            var slug = $(this).data('slug') || '';
            $('#mr-sample-title').text(title + ' (Sample Report)');
            $('.mr-sample-buy-btn').data('slug', slug).data('title', title);
            
            // Show loader & stream full publication-grade sample HTML into iframe
            $('#mr-sample-loader').show();
            var sampleUrl = mr_reports_vars.ajax_url + '?action=mr_view_sample_report&slug=' + encodeURIComponent(slug);
            var iframe = $('#mr-sample-iframe');
            iframe.off('load').on('load', function() {
                $('#mr-sample-loader').fadeOut(120);
            });
            iframe.attr('src', sampleUrl);

            sampleModal.addClass('is-open').fadeIn(150);
            if (sampleModal.length && sampleModal[0].style) {
                sampleModal[0].style.setProperty('display', 'flex', 'important');
            }
        });

        // Close Sample Modal Handler
        function closeSampleModal() {
            var m = $('#mr-sample-modal');
            m.removeClass('is-open').fadeOut(120, function() {
                $(this).attr('style', 'display: none !important;');
                $('#mr-sample-iframe').attr('src', 'about:blank');
            });
            m.attr('style', 'display: none !important;');
            $('#mr-sample-iframe').attr('src', 'about:blank');
        }

        $(document).on('click', '.mr-sample-close, .mr-sample-close-btn', function(e) {
            e.preventDefault();
            closeSampleModal();
        });

        $(document).on('click', '#mr-sample-modal', function(e) {
            if ($(e.target).is('#mr-sample-modal')) {
                closeSampleModal();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#mr-sample-modal').is(':visible')) {
                closeSampleModal();
            }
        });

        // Click "Get Your Report Now" from inside Sample Modal
        $(document).on('click', '.mr-sample-buy-btn', function(e) {
            e.preventDefault();
            var slug = $(this).data('slug');
            var title = $(this).data('title');
            closeSampleModal();

            // Look for any buy or generate button on the page for this slug
            var pageBtn = $('.mr-btn-generate-direct[data-slug="' + slug + '"], .mr-btn-buy-alacarte[data-slug="' + slug + '"], .mr-alacarte-card[data-slug="' + slug + '"] .mr-btn-buy-alacarte, .mr-alacarte-card[data-slug="' + slug + '"] .mr-btn-generate-direct').first();
            if (pageBtn.length) {
                pageBtn.trigger('click');
                return;
            }

            var isFree = (slug === 'yearly_horoscope_vedic' || slug === 'yearly_horoscope_snapshot');
            var isLoggedIn = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.is_logged_in) ||
                             (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.is_logged_in);

            if (!isFree && !isLoggedIn) {
                var loginUrl = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.login_url) ||
                               (typeof mr_razorpay_vars !== 'undefined' && mr_razorpay_vars.login_url) ||
                               '/wp-login.php';
                if (window.mrNotify) {
                    window.mrNotify.auth({
                        loginUrl: loginUrl,
                        message: 'Sign in to your sacred MeraRashifal account to explore and generate your personalized Vedic reports and unlock life-changing cosmic insights.'
                    });
                } else {
                    window.location.href = loginUrl;
                }
                return;
            }

            if (isFree && typeof window.openDetailsModal === 'function') {
                window.openDetailsModal(slug, title);
                return;
            }

            // If logged in, navigate to catalog or report landing page
            var catUrl = (typeof mr_reports_vars !== 'undefined' && mr_reports_vars.catalog_url) ? mr_reports_vars.catalog_url : '/reports/';
            window.location.href = catUrl;
        });

        function closeDetailsModal() {
            var m = $('#mr-details-modal');
            m.removeClass('is-open').fadeOut(100, function() {
                $(this).attr('style', 'display: none !important;');
            });
            m.attr('style', 'display: none !important;');
        }

        // 12. Submit Birth Details & Launch Sticky Bottom Progress Banner
        $('#mr-birth-details-form').on('submit', function(e) {
            e.preventDefault();

            var formEl = this;
            var reportSlug = $('#mr_report_slug').val();
            var errors = [];
            var firstErrorField = null;

            // Clear previous errors & highlights
            $('#mr-birth-details-form input, #mr-birth-details-form select').removeClass('mr-input-error');
            $('#mr-modal-error-box').hide().find('.mr-error-text').text('');

            // Validate Email
            var emailVal = ($('#mr_recipient_email').val() || '').trim();
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailVal || !emailPattern.test(emailVal)) {
                errors.push("Please provide a valid email address to receive your report PDF.");
                $('#mr_recipient_email').addClass('mr-input-error');
                if (!firstErrorField) firstErrorField = $('#mr_recipient_email');
            }

            if (reportSlug === 'kundli_matching') {
                // Validate Boy Details (Var)
                if (!$('#mr_boy_name').val().trim()) {
                    errors.push("Boy's full name is required.");
                    $('#mr_boy_name').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_boy_name');
                }
                if (!$('#mr_boy_dob').val()) {
                    errors.push("Boy's date of birth is required.");
                    $('#mr_boy_dob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_boy_dob');
                }
                if (!$('#mr_boy_tob').val()) {
                    errors.push("Boy's time of birth is required.");
                    $('#mr_boy_tob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_boy_tob');
                }
                if (!$('#mr_boy_pob').val().trim()) {
                    errors.push("Boy's place of birth is required.");
                    $('#mr_boy_pob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_boy_pob');
                }

                // Validate Girl Details (Kanya)
                if (!$('#mr_girl_name').val().trim()) {
                    errors.push("Girl's full name is required.");
                    $('#mr_girl_name').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_girl_name');
                }
                if (!$('#mr_girl_dob').val()) {
                    errors.push("Girl's date of birth is required.");
                    $('#mr_girl_dob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_girl_dob');
                }
                if (!$('#mr_girl_tob').val()) {
                    errors.push("Girl's time of birth is required.");
                    $('#mr_girl_tob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_girl_tob');
                }
                if (!$('#mr_girl_pob').val().trim()) {
                    errors.push("Girl's place of birth is required.");
                    $('#mr_girl_pob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_girl_pob');
                }
            } else {
                // Validate Standard Single Person Details
                if (!$('#mr_person_name').val().trim()) {
                    errors.push("Full name is required.");
                    $('#mr_person_name').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_person_name');
                }
                if (!$('#mr_dob').val()) {
                    errors.push("Date of birth is required.");
                    $('#mr_dob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_dob');
                }
                if (!$('#mr_tob').val()) {
                    errors.push("Time of birth is required.");
                    $('#mr_tob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_tob');
                }
                if (!$('#mr_pob').val().trim()) {
                    errors.push("Place of birth is required.");
                    $('#mr_pob').addClass('mr-input-error');
                    if (!firstErrorField) firstErrorField = $('#mr_pob');
                }
            }

            // If validation errors exist, display banner in modal, scroll to first error, and DO NOT close modal!
            if (errors.length > 0) {
                var errorNotice = (reportSlug === 'kundli_matching')
                    ? "Please fill in all birth details for both the Boy and Girl to generate your 36 Gun Milan report."
                    : (errors[0] || "Please fill in all required birth details.");
                $('#mr-modal-error-box').slideDown(150).find('.mr-error-text').text(errorNotice);
                if (firstErrorField && firstErrorField.length) {
                    firstErrorField.focus();
                    var modalContent = $('#mr-details-modal .mr-modal-content');
                    if (modalContent.length) {
                        modalContent.animate({
                            scrollTop: firstErrorField.offset().top - modalContent.offset().top + modalContent.scrollTop() - 70
                        }, 250);
                    }
                }
                return false;
            }

            var formData = new FormData(formEl);
            formData.append('action', 'mr_generate_report');
            formData.append('nonce', mr_reports_vars.nonce);

            // 1. Instantly hide and close modal so user is unblocked to browse other features
            closeDetailsModal();

            // 2. Persist active generation state in localStorage
            var activeGen = {
                report_slug: reportSlug,
                report_title: activeItemName || 'Astrology Report',
                status: 'processing',
                timestamp: Date.now(),
                pdf_url: '',
                dismissed: false
            };
            try {
                localStorage.setItem('mr_active_generation', JSON.stringify(activeGen));
            } catch(e) {}

            // 3. Launch Compact Sticky Bottom Banner (<= 728x90)
            $('#mr-banner-spinner').show();
            $('#mr-banner-check-icon').hide();
            $('#mr-banner-pill').removeClass('ready').text('In Process');
            progressTitle.text(activeItemName || 'Astrology Report');
            progressStatus.text('Your requested report is in process, please visit other products till then.');
            progressBar.css('width', '25%').css('background', 'linear-gradient(90deg, #F58220, #2CB67D)');
            btnGoReport.removeClass('mr-btn-banner-retry').prop('disabled', true).html('<span>Go To Report</span> <i class="fa-solid fa-lock"></i>');
            progressBanner.slideDown(250);

            // Progressive Stages Animation
            setTimeout(function() {
                progressBar.css('width', '55%');
            }, 900);

            setTimeout(function() {
                progressBar.css('width', '80%');
            }, 2000);

            // 4. AJAX call to generate report with multipart/form-data support
            $.ajax({
                url: mr_reports_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        setTimeout(function() {
                            progressBar.css('width', '100%');
                            $('#mr-banner-spinner').hide();
                            $('#mr-banner-check-icon').show();
                            $('#mr-banner-pill').addClass('ready').text('Ready');
                            progressStatus.html('<strong style="color:#2CB67D;">Report ready!</strong> Click Go To Report to view or download.');
                            btnGoReport.removeClass('mr-btn-banner-retry').prop('disabled', false).html('<span>Go To Report</span> <i class="fa-solid fa-arrow-up-right-from-square"></i>').data('url', response.data.pdf_url);

                            // Update active state in localStorage to ready
                            activeGen.status = 'ready';
                            activeGen.pdf_url = response.data.pdf_url;
                            activeGen.report_id = response.data.report_id;
                            try {
                                localStorage.setItem('mr_active_generation', JSON.stringify(activeGen));
                            } catch(e) {}

                            // Update token counter on card if present
                            var card = $('.mr-alacarte-card[data-slug="' + formData.report_slug + '"]');
                            if (card.length) {
                                var tag = card.find('.mr-token-tag');
                                var rem = response.data.remaining_tokens;
                                if (rem > 0) {
                                    tag.html('<i class="fa-solid fa-ticket"></i> ' + rem + ' Token' + (rem === 1 ? '' : 's') + ' Available');
                                } else {
                                    tag.remove();
                                    card.find('.mr-btn-generate-direct').replaceWith('<button type="button" class="mr-btn-glow mr-btn-buy-alacarte" data-slug="' + formData.report_slug + '" data-title="' + activeItemName + '"><i class="fa-solid fa-cart-shopping"></i> Buy A la Carte</button>');
                                }
                            }
                        }, 2800);
                    } else {
                        progressBar.css('width', '100%').css('background', '#ef4444');
                        $('#mr-banner-spinner').hide();
                        var errMessage = (response.data && response.data.message) ? response.data.message : 'Generation failed.';
                        progressStatus.html('<strong style="color:#ef4444;">Error: ' + errMessage + '</strong>');
                        btnGoReport.addClass('mr-btn-banner-retry').prop('disabled', false).html('<i class="fa-solid fa-pen-to-square"></i> <span>Edit Details & Retry</span>');
                        try {
                            localStorage.removeItem('mr_active_generation');
                        } catch(e) {}

                        if (response.data && response.data.code === 'auth_required' && response.data.login_url) {
                            setTimeout(function() {
                                if (window.mrNotify) {
                                    window.mrNotify.auth({
                                        loginUrl: response.data.login_url,
                                        message: response.data.message || 'Please sign in to order personalized Vedic reports.'
                                    });
                                } else {
                                    window.location.href = response.data.login_url;
                                }
                            }, 400);
                        } else if (response.data && response.data.code === 'payment_required') {
                            setTimeout(function() {
                                if (window.mrNotify) {
                                    window.mrNotify.alert(
                                        response.data.message || 'Payment is required to generate this personalized report.',
                                        'Payment Required',
                                        'warning'
                                    );
                                }
                            }, 300);
                        }
                    }
                },
                error: function() {
                    progressBar.css('width', '100%').css('background', '#ef4444');
                    $('#mr-banner-spinner').hide();
                    progressStatus.html('<strong style="color:#ef4444;">Server error. Please try again.</strong>');
                    btnGoReport.addClass('mr-btn-banner-retry').prop('disabled', false).html('<i class="fa-solid fa-pen-to-square"></i> <span>Edit Details & Retry</span>');
                    try {
                        localStorage.removeItem('mr_active_generation');
                    } catch(e) {}
                }
            });
        });

        // Clear error highlight on user typing
        $(document).on('input change', '#mr-birth-details-form input, #mr-birth-details-form select', function() {
            $(this).removeClass('mr-input-error');
            if ($('#mr-birth-details-form .mr-input-error').length === 0) {
                $('#mr-modal-error-box').slideUp(100);
            }
        });

        // 13. "Go To Report" / "Edit Details & Retry" button click
        $(document).on('click', '#mr-go-to-report-btn', function(e) {
            e.preventDefault();
            if ($(this).hasClass('mr-btn-banner-retry')) {
                var slug = activeReportSlug || $('#mr_report_slug').val();
                var title = activeItemName || $('#mr-modal-report-title').text();
                openDetailsModal(slug, title);
                return;
            }
            var pdfUrl = $(this).data('url');
            if (pdfUrl) {
                window.open(pdfUrl, '_blank');
                // Per user request: dismiss sticky banner when user clicks Go To Report
                $('#mr-progress-banner').fadeOut(200);
                try {
                    localStorage.removeItem('mr_active_generation');
                } catch(e) {}
            }
        });

        // 14. Close Banner button click
        $(document).on('click', '#mr-banner-close-btn', function(e) {
            e.preventDefault();
            $('#mr-progress-banner').fadeOut(200);
            try {
                var stored = localStorage.getItem('mr_active_generation');
                if (stored) {
                    var data = JSON.parse(stored);
                    data.dismissed = true;
                    localStorage.setItem('mr_active_generation', JSON.stringify(data));
                }
            } catch(e) {}
        });

        // 15. Persist Progress Banner Across All Pages
        function checkPersistedBanner() {
            var stored = null;
            try {
                stored = localStorage.getItem('mr_active_generation');
            } catch(e) {}
            if (!stored) return;

            try {
                var gen = JSON.parse(stored);
                if (!gen || gen.dismissed) return;

                // Max persist 24 hours
                if (Date.now() - (gen.timestamp || 0) > 86400000) {
                    localStorage.removeItem('mr_active_generation');
                    return;
                }

                var banner = $('#mr-progress-banner');
                if (!banner.length) return;

                if (gen.status === 'ready' && gen.pdf_url) {
                    $('#mr-banner-spinner').hide();
                    $('#mr-banner-check-icon').show();
                    $('#mr-banner-pill').addClass('ready').text('Ready');
                    $('#mr-banner-title').text(gen.report_title || 'Astrology Report');
                    $('#mr-banner-status').html('<strong style="color:#2CB67D;">Report ready!</strong> Click Go To Report to view or download.');
                    $('#mr-banner-bar').css('width', '100%');
                    $('#mr-go-to-report-btn').prop('disabled', false).html('<span>Go To Report</span> <i class="fa-solid fa-arrow-up-right-from-square"></i>').data('url', gen.pdf_url);
                    banner.show();
                } else if (gen.status === 'processing') {
                    $('#mr-banner-spinner').show();
                    $('#mr-banner-check-icon').hide();
                    $('#mr-banner-pill').removeClass('ready').text('In Process');
                    $('#mr-banner-title').text(gen.report_title || 'Astrology Report');
                    $('#mr-banner-status').text('Your requested report is in process, please visit other products till then.');
                    $('#mr-banner-bar').css('width', '75%').css('background', 'linear-gradient(90deg, #F58220, #2CB67D)');
                    $('#mr-go-to-report-btn').prop('disabled', true).html('<span>Go To Report</span> <i class="fa-solid fa-lock"></i>');
                    banner.show();

                    pollReportStatus(gen);
                }
            } catch(e) {}
        }

        var pollCount = 0;
        function pollReportStatus(gen) {
            if (pollCount >= 30) return;
            pollCount++;

            setTimeout(function() {
                $.ajax({
                    url: mr_reports_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mr_check_report_status',
                        nonce: mr_reports_vars.nonce,
                        report_id: gen.report_id || 0,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.data && res.data.status === 'ready' && res.data.pdf_url) {
                            $('#mr-banner-spinner').hide();
                            $('#mr-banner-check-icon').show();
                            $('#mr-banner-pill').addClass('ready').text('Ready');
                            $('#mr-banner-title').text(res.data.report_title || gen.report_title);
                            $('#mr-banner-status').html('<strong style="color:#2CB67D;">Report ready!</strong> Click Go To Report to view or download.');
                            $('#mr-banner-bar').css('width', '100%');
                            $('#mr-go-to-report-btn').prop('disabled', false).html('<span>Go To Report</span> <i class="fa-solid fa-arrow-up-right-from-square"></i>').data('url', res.data.pdf_url);

                            gen.status = 'ready';
                            gen.pdf_url = res.data.pdf_url;
                            try {
                                localStorage.setItem('mr_active_generation', JSON.stringify(gen));
                            } catch(e) {}
                        } else if (res.success && res.data && res.data.status === 'processing') {
                            pollReportStatus(gen);
                        }
                    },
                    error: function() {
                        if (pollCount < 25) {
                            pollReportStatus(gen);
                        }
                    }
                });
            }, 3000);
        }

        // Run check on page load across all pages
        checkPersistedBanner();

    });

})(jQuery);
