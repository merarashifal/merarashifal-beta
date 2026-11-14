/**
 * MeraRashifal Privacy Acceptance Engine
 * Shows the "We care about your privacy" card once until user acts,
 * stores preference, and permanently hides on public site pages.
 * Detailed management is available in My Account.
 */
(function() {
    'use strict';

    var COOKIE_NAME = (typeof mr_cookie_consent_vars !== 'undefined' && mr_cookie_consent_vars.cookie_name) ? mr_cookie_consent_vars.cookie_name : 'mr_cookie_consent';
    var COOKIE_DAYS = (typeof mr_cookie_consent_vars !== 'undefined' && mr_cookie_consent_vars.cookie_expiry_days) ? mr_cookie_consent_vars.cookie_expiry_days : 365;

    // Cookie Utilities
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        var secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/; SameSite=Lax" + secure;
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                try {
                    return decodeURIComponent(c.substring(nameEQ.length, c.length));
                } catch(e) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
        }
        return null;
    }

    // Retrieve active consent state from Cookie or LocalStorage
    function getStoredConsent() {
        var raw = getCookie(COOKIE_NAME);
        if (!raw) {
            try {
                raw = localStorage.getItem(COOKIE_NAME);
            } catch(e) {}
        }

        if (raw) {
            try {
                return JSON.parse(raw);
            } catch(e) {
                return null;
            }
        }
        return null;
    }

    // Save consent state to Cookie and LocalStorage
    function saveConsent(state) {
        state.timestamp = new Date().toISOString();
        var serialized = JSON.stringify(state);

        setCookie(COOKIE_NAME, serialized, COOKIE_DAYS);
        try {
            localStorage.setItem(COOKIE_NAME, serialized);
        } catch(e) {}

        // Broadcast standard custom event for analytics / tags
        try {
            var evt = new CustomEvent('mr_cookie_consent_updated', { detail: state });
            window.dispatchEvent(evt);
        } catch(e) {}

        // Update Google Consent Mode if gtag exists
        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {
                'analytics_storage': state.analytics ? 'granted' : 'denied',
                'ad_storage': state.marketing ? 'granted' : 'denied',
                'ad_user_data': state.marketing ? 'granted' : 'denied',
                'ad_personalization': state.marketing ? 'granted' : 'denied',
                'personalization_storage': state.personalization ? 'granted' : 'denied'
            });
        }
    }

    // Dismiss banner permanently
    function dismissBanner() {
        var banner = document.getElementById('mr-cookie-banner');
        if (banner) {
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(16px)';
            setTimeout(function() {
                if (banner && banner.parentNode) {
                    banner.parentNode.removeChild(banner);
                }
            }, 300);
        }
    }

    // Main Init
    function init() {
        var consent = getStoredConsent();
        var banner = document.getElementById('mr-cookie-banner');

        if (consent) {
            // Already consented previously: do not show anything on site pages
            if (banner && banner.parentNode) {
                banner.parentNode.removeChild(banner);
            }
            return;
        }

        // First time visitor: show privacy acceptance card once
        if (banner) {
            banner.style.display = 'block';
        }

        // Action: Accept All
        var btnAccept = document.getElementById('mr-cookie-btn-accept-all');
        if (btnAccept) {
            btnAccept.addEventListener('click', function() {
                var allConsent = {
                    necessary: true,
                    personalization: true,
                    analytics: true,
                    marketing: true,
                    status: 'all'
                };
                saveConsent(allConsent);
                dismissBanner();
            });
        }

        // Action: Reject All
        var btnReject = document.getElementById('mr-cookie-btn-reject-all');
        if (btnReject) {
            btnReject.addEventListener('click', function() {
                var rejectConsent = {
                    necessary: true,
                    personalization: false,
                    analytics: false,
                    marketing: false,
                    status: 'rejected'
                };
                saveConsent(rejectConsent);
                dismissBanner();
            });
        }
    }

    // Public API on window
    window.mrGetCookieConsent = getStoredConsent;

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
