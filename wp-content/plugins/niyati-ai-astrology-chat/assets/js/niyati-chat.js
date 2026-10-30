/**
 * Niyati AI Vedic Astrology Chatbot - Frontend Client
 * With Free Quotas, Credit Packs, and Wallet Recharge
 */
(function () {
    'use strict';

    var config = window.niyatiConfig || {};
    var restBase = config.rest_url || '/wp-json/niyati/v1';

    // i18n Dictionary: Base Language is English, switches dynamically if user changes language
    var i18n = {
        en: {
            launcherText: 'Ask Niyati',
            botSubtitle: 'Vedic Astrologer • Online',
            resetTitle: 'Reset Kundli',
            quotaText: '🔮 Free Vedic Astrology Consultation',
            rechargePill: '⚡ Recharge',
            onboardingTitle: 'Birth Chart & Consultation',
            onboardingSub: 'Enter your birth details for accurate planetary insights',
            lblName: 'Full Name:',
            placeholderName: 'e.g. Rahul Sharma',
            lblDob: 'Date of Birth (DOB):',
            lblTob: 'Time of Birth (Time):',
            lblPob: 'Place of Birth:',
            placeholderPob: 'e.g. New Delhi, Mumbai, New York...',
            lblLang: 'Preferred Language:',
            btnCompute: '✨ Analyze Chart & Start Chat',
            btnComputing: '⏳ Calculating planetary positions...',
            inputPlaceholder: 'Ask Niyati a question...',
            rechargeTitle: 'Recharge Consultation Credits',
            rechargeSub: '⚡ 1 Question = 1 Credit • Instant Activation • Valid Lifetime',
            confirmReset: 'Are you sure you want to reset your birth chart and start a new consultation?',
            quotaVedic: '🔮 Vedic Astrologer Niyati',
            quotaActiveCredits: '⚡ Active Credits Balance',
            quotaRecharge: '💳 Recharge Credits',
            serverError: 'Unable to connect to the astrology server. Please try again.',
            limitReachedTitle: '⚠️ Question Limit Reached',
            limitReachedMsg: 'You have reached your free question limit. Please recharge credits to continue your consultation with Niyati.',
            btnViewPacks: '⚡ View Packs & Recharge',
            typingIndicator: 'Niyati is analyzing your chart',
            netError: 'Internet connection error. Please try again.',
            initializing: 'Niyati is initializing...',
            initializingNotice: 'Niyati is initializing, please wait a moment...',
            loginRequiredTitle: '🔒 Login Required',
            loginRequiredMsg: 'Please log in to your account first to generate your birth chart and access Niyati consultations.',
            btnLogin: '🔑 Log In to Continue',
            btnCancel: 'Cancel',
            loginNotice: '🔒 Please log in to your account to start your consultation.'
        },
        hi: {
            launcherText: 'नियति से पूछें',
            botSubtitle: 'वैदिक ज्योतिषी • Online',
            resetTitle: 'नई कुंडली / Reset',
            quotaText: '🔮 निःशुल्क वैदिक ज्योतिष परामर्श',
            rechargePill: '⚡ रिचार्ज',
            onboardingTitle: 'कुंडली मिलान एवं परामर्श',
            onboardingSub: 'सटीक भविष्यफल हेतु अपनी जन्म जानकारी दर्ज करें',
            lblName: 'आपका नाम (Name):',
            placeholderName: 'उदा. राहुल शर्मा',
            lblDob: 'जन्म तिथि (DOB):',
            lblTob: 'जन्म समय (Time):',
            lblPob: 'जन्म स्थान (Place of Birth):',
            placeholderPob: 'उदा. नई दिल्ली, मुंबई, मेरठ...',
            lblLang: 'पसंदीदा भाषा (Language):',
            btnCompute: '✨ कुंडली देखें और बात शुरू करें',
            btnComputing: '⏳ वैदिक ग्रहों की गणना हो रही है...',
            inputPlaceholder: 'नियति से अपना प्रश्न पूछें...',
            rechargeTitle: 'परामर्श क्रेडिट्स रिचार्ज करें',
            rechargeSub: '⚡ 1 प्रश्न = 1 क्रेडिट • तत्काल सक्रियण • आजीवन वैधता',
            confirmReset: 'क्या आप नई जन्म कुंडली दर्ज करना चाहते हैं?',
            quotaVedic: '🔮 वैदिक ज्योतिषी नियति',
            quotaActiveCredits: '⚡ एक्टिव क्रेडिट्स बैलेंस',
            quotaRecharge: '💳 रिचार्ज करें',
            serverError: 'सर्वर से संपर्क नहीं हो पाया। कृपया पुनः प्रयास करें।',
            limitReachedTitle: '⚠️ प्रश्न सीमा समाप्त (Limit Reached)',
            limitReachedMsg: 'आपकी निःशुल्क प्रश्न सीमा समाप्त हो चुकी है। नियति से आगे मार्गदर्शन पाने हेतु क्रेडिट्स रिचार्ज करें।',
            btnViewPacks: '⚡ विशेष ऑफर देखें एवं रिचार्ज करें',
            typingIndicator: 'नियति अध्ययन कर रही हैं',
            netError: 'इंटरनेट कनेक्शन में त्रुटि आई। कृपया पुनः प्रयास करें।',
            initializing: 'नियति सक्रिय हो रही हैं...',
            initializingNotice: 'नियति सक्रिय हो रही हैं, कृपया कुछ क्षण प्रतीक्षा करें...',
            loginRequiredTitle: '🔒 लॉगिन अनिवार्य है',
            loginRequiredMsg: 'अपनी जन्म कुंडली तैयार करने और नियति AI से परामर्श प्राप्त करने के लिए कृपया पहले अपने खाते में लॉगिन करें।',
            btnLogin: '🔑 आगे बढ़ने के लिए लॉगिन करें',
            btnCancel: 'रद्द करें',
            loginNotice: '🔒 परामर्श शुरू करने के लिए कृपया पहले अपने खाते में लॉगिन करें।'
        },
        hinglish: {
            launcherText: 'Ask Niyati',
            botSubtitle: 'Vedic Astrologer • Online',
            resetTitle: 'Reset Kundli',
            quotaText: '🔮 Free Vedic Astrology Consultation',
            rechargePill: '⚡ Recharge',
            onboardingTitle: 'Kundli & Astrology Guidance',
            onboardingSub: 'Accurate predictions ke liye apni birth details enter karein',
            lblName: 'Aapka Naam (Full Name):',
            placeholderName: 'e.g. Rahul Sharma',
            lblDob: 'Birth Date (DOB):',
            lblTob: 'Birth Time:',
            lblPob: 'Birth Place:',
            placeholderPob: 'e.g. New Delhi, Mumbai, Lucknow...',
            lblLang: 'Preferred Language:',
            btnCompute: '✨ Kundli Dekhein & Chat Shuru Karein',
            btnComputing: '⏳ Planetary positions calculate ho rahi hain...',
            inputPlaceholder: 'Niyati se apna sawaal poochein...',
            rechargeTitle: 'Recharge Consultation Credits',
            rechargeSub: '⚡ 1 Question = 1 Credit • Instant Activation • Lifetime Validity',
            confirmReset: 'Kya aap new birth chart start karna chahte hain?',
            quotaVedic: '🔮 Vedic Astrologer Niyati',
            quotaActiveCredits: '⚡ Active Credits Balance',
            quotaRecharge: '💳 Recharge Credits',
            serverError: 'Server se connect nahi ho paaya. Please dubara try karein.',
            limitReachedTitle: '⚠️ Question Limit Reached',
            limitReachedMsg: 'Aapki free question limit complete ho gayi hai. Niyati se chat continue rakhne ke liye recharge karein.',
            btnViewPacks: '⚡ View Packs & Recharge',
            typingIndicator: 'Niyati aapki chart study kar rahi hain',
            netError: 'Internet connection error. Dubara try karein.',
            initializing: 'Niyati initialize ho rahi hain...',
            initializingNotice: 'Niyati initialize ho rahi hain, please wait karein...',
            loginRequiredTitle: '🔒 Login Required',
            loginRequiredMsg: 'Apni birth chart calculate karne aur Niyati se guidance paane ke liye please pehle login karein.',
            btnLogin: '🔑 Login to Continue',
            btnCancel: 'Cancel',
            loginNotice: '🔒 Consultation shuru karne ke liye pehle login karein.'
        }
    };

    // State
    var state = {
        isInitialized: false,
        isOpen: false,
        isFullscreen: false,
        isLoggedIn: (window.niyatiConfig && typeof window.niyatiConfig.is_logged_in === 'boolean') ? window.niyatiConfig.is_logged_in : false,
        loginUrl: (window.niyatiConfig && window.niyatiConfig.login_url) ? window.niyatiConfig.login_url : '/wp-login.php',
        chartToken: ((window.niyatiConfig && window.niyatiConfig.is_logged_in) ? (localStorage.getItem('niyati_chart_token') || '') : ''),
        birthData: ((window.niyatiConfig && window.niyatiConfig.is_logged_in) ? JSON.parse(localStorage.getItem('niyati_birth_data') || 'null') : null),
        history: ((window.niyatiConfig && window.niyatiConfig.is_logged_in) ? JSON.parse(localStorage.getItem('niyati_chat_history') || '[]') : []),
        savedProfiles: [],
        lang: (function () {
            var saved = localStorage.getItem('niyati_lang');
            return (saved === 'hi' || saved === 'hinglish') ? saved : 'en';
        })(),
        quota: (window.niyatiConfig && window.niyatiConfig.initial_quota) ? window.niyatiConfig.initial_quota : null,
        paidCredits: (window.niyatiConfig && typeof window.niyatiConfig.initial_paid_credits === 'number') ? window.niyatiConfig.initial_paid_credits : 0,
        validity: (window.niyatiConfig && window.niyatiConfig.initial_validity) ? window.niyatiConfig.initial_validity : null,
        packs: [],
        razorpayKey: '',
        razorpayTestMode: true,
        visitorId: (window.niyatiConfig && window.niyatiConfig.visitor_id) ? window.niyatiConfig.visitor_id : '',
        avatarUrl: (window.niyatiConfig && window.niyatiConfig.avatar_url) ? window.niyatiConfig.avatar_url : '/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg',
        nonce: (window.niyatiConfig && window.niyatiConfig.nonce) ? window.niyatiConfig.nonce : '',
        suggestions: {},
        isComputing: false,
        isSending: false,
        nudge: (window.niyatiConfig && window.niyatiConfig.nudge) ? window.niyatiConfig.nudge : { enabled: false },
        nudgeTimeout: null
    };

    function clearUserSessionData() {
        try {
            localStorage.removeItem('niyati_chart_token');
            localStorage.removeItem('niyati_birth_data');
            localStorage.removeItem('niyati_chat_history');
            localStorage.removeItem('niyati_last_active_time');
            localStorage.removeItem('niyati_last_greeting_time');
            localStorage.removeItem('niyati_last_greeting_date');
            localStorage.removeItem('niyati_user_id');
        } catch (e) {}

        state.chartToken = '';
        state.birthData = null;
        state.history = [];
        state.savedProfiles = [];
        state.activeReportContext = null;
        state.pendingReportContext = null;

        if (el.chatList) el.chatList.innerHTML = '';
        if (el.suggestions) {
            el.suggestions.innerHTML = '';
            el.suggestions.style.display = 'none';
        }
        if (el.savedProfilesWrap) {
            el.savedProfilesWrap.innerHTML = '';
            el.savedProfilesWrap.style.display = 'none';
        }

        if (el.inputName) el.inputName.value = '';
        if (el.inputDob) el.inputDob.value = '';
        if (el.inputTob) el.inputTob.value = '12:00';
        if (el.inputPob) el.inputPob.value = '';
        if (el.inputLat) el.inputLat.value = '28.6139';
        if (el.inputLon) el.inputLon.value = '77.2090';
        if (el.inputTz) el.inputTz.value = '5.5';

        try {
            document.cookie = 'niyati_logged_out=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        } catch (e) {}
    }

    function getWpNonce() {
        return state.nonce || (window.niyatiConfig && window.niyatiConfig.nonce) || '';
    }

    // DOM Elements
    var el = {};
    var toastTimeout = null;

    function showInitializingToast(msg) {
        var t = i18n[state.lang] || i18n['en'];
        var text = msg || t.initializingNotice || 'Niyati is initializing, please wait a moment...';
        var toast = document.getElementById('niyati-init-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'niyati-init-toast';
            toast.className = 'niyati-init-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<span>⏳</span> <span>' + escapeHTML(text) + '</span>';
        toast.classList.add('visible');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(function () {
            toast.classList.remove('visible');
        }, 2600);
    }

    function setChatbotEnabled(enabled) {
        if (!enabled) {
            if (el.launcher) el.launcher.classList.add('niyati-launcher-disabled');
            if (el.container) el.container.classList.add('niyati-chat-disabled');
            if (el.userInput) {
                el.userInput.disabled = true;
                el.userInput.setAttribute('placeholder', (i18n[state.lang] || i18n['en']).initializing || 'Niyati is initializing...');
            }
            if (el.btnSend) el.btnSend.disabled = true;
        } else {
            if (el.launcher) el.launcher.classList.remove('niyati-launcher-disabled');
            if (el.container) el.container.classList.remove('niyati-chat-disabled');
            if (el.userInput) {
                el.userInput.disabled = false;
                el.userInput.setAttribute('placeholder', (i18n[state.lang] || i18n['en']).inputPlaceholder || 'Ask Niyati a question...');
            }
            if (el.btnSend) el.btnSend.disabled = false;
        }
    }

    function init() {
        createWidgetDOM();
        bindEvents();
        applyLanguage(state.lang);
        setChatbotEnabled(false);

        // Check if logged out or if user switched
        var hasLoggedOutCookie = document.cookie.indexOf('niyati_logged_out=1') !== -1;
        if (!state.isLoggedIn || hasLoggedOutCookie) {
            clearUserSessionData();
        } else {
            var currentUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? String(window.niyatiConfig.user_id) : '';
            var storedUserId = localStorage.getItem('niyati_user_id') || '';
            if (storedUserId && currentUserId && storedUserId !== currentUserId) {
                clearUserSessionData();
            }
            if (currentUserId) {
                localStorage.setItem('niyati_user_id', currentUserId);
            }
        }

        updateQuotaUI();

        // Restore temporarily saved birth data if user previously typed details before logging in
        if (state.isLoggedIn) {
            try {
                var savedTemp = localStorage.getItem('niyati_temp_birth_data');
                if (savedTemp) {
                    var tempObj = JSON.parse(savedTemp);
                    if (tempObj) {
                        if (tempObj.name && el.inputName) el.inputName.value = tempObj.name;
                        if (tempObj.dob && el.inputDob) el.inputDob.value = tempObj.dob;
                        if (tempObj.tob && el.inputTob) el.inputTob.value = tempObj.tob;
                        if (tempObj.pob && el.inputPob) el.inputPob.value = tempObj.pob;
                        if (tempObj.lat && el.inputLat) el.inputLat.value = tempObj.lat;
                        if (tempObj.lon && el.inputLon) el.inputLon.value = tempObj.lon;
                        if (tempObj.tz && el.inputTz) el.inputTz.value = tempObj.tz;
                        if (tempObj.lang && el.inputLang) el.inputLang.value = tempObj.lang;
                    }
                }
            } catch (e) {}
        }

        updateLoginNoticeUI();
        initNudgeSystem();

        fetchInitData();

        // ONLY resume chat view if user IS LOGGED IN and has calculated chart
        if (state.isLoggedIn && state.chartToken && state.birthData) {
            showView('chat');
            renderStoredHistory();
        } else {
            showView('onboarding');
        }
    }

    function createWidgetDOM() {
        var avatarUrl = state.avatarUrl;
        var wrapper = document.createElement('div');
        wrapper.id = 'niyati-widget-root';
        wrapper.innerHTML = `
            <!-- Floating Launcher Button -->
            <button id="niyati-launcher" class="niyati-launcher-btn" aria-label="Chat with Niyati">
                <img src="${avatarUrl}" alt="Niyati" class="niyati-launcher-avatar">
                <span id="niyati-launcher-label" class="niyati-launcher-label">Ask Niyati</span>
                <span id="niyati-launcher-badge" class="niyati-launcher-badge" style="display:none;">Free</span>
            </button>

            <!-- Proactive Nudge Speech Bubble -->
            <div id="niyati-nudge-bubble" class="niyati-nudge-bubble" style="display:none;" role="status" aria-live="polite">
                <div class="niyati-nudge-content">
                    <span class="niyati-nudge-sparkle">✨</span>
                    <span id="niyati-nudge-text" class="niyati-nudge-text"></span>
                </div>
                <button type="button" id="niyati-nudge-close" class="niyati-nudge-close" aria-label="Dismiss Nudge">&times;</button>
                <div class="niyati-nudge-arrow"></div>
            </div>

            <!-- Widget Window -->
            <div id="niyati-container" class="niyati-widget-container">
                <!-- Header -->
                <div class="niyati-header">
                    <div class="niyati-header-profile">
                        <div class="niyati-avatar">
                            <img src="${avatarUrl}" alt="Niyati" class="niyati-avatar-img" id="niyati-header-avatar">
                            <div class="niyati-online-dot"></div>
                        </div>
                        <div class="niyati-header-info">
                            <div class="niyati-header-title-row">
                                <h4 id="niyati-title">${escapeHTML(config.bot_name || 'Niyati')}</h4>
                            </div>
                            <div class="niyati-header-sub-row">
                                <span id="niyati-header-subtitle">Vedic Astrologer</span>
                                <span class="niyati-header-status"><span class="niyati-status-pulse"></span>Online</span>
                            </div>
                        </div>
                    </div>
                    <div class="niyati-header-actions">
                        <button type="button" id="niyati-btn-reset" class="niyati-header-btn" title="Reset Kundli" aria-label="Reset Kundli">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                        <button type="button" id="niyati-btn-fullscreen" class="niyati-header-btn" title="Full Screen" aria-label="Toggle Full Screen">
                            <svg class="niyati-icon-expand" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                            <svg class="niyati-icon-compress" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M4 14h6v6M20 10h-6V4M14 10l7-7M10 14l-7 7"/></svg>
                        </button>
                        <button type="button" id="niyati-btn-close" class="niyati-header-btn" title="Close" aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                </div>

                <!-- Professional Credit & Status Bar -->
                <div id="niyati-credit-bar" class="niyati-credit-bar">
                    <div class="niyati-credit-status" id="niyati-credit-status">
                        <span class="niyati-credit-icon" id="niyati-credit-icon">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </span>
                        <div class="niyati-credit-info">
                            <span class="niyati-credit-amount" id="niyati-quota-badge">...</span>
                            <span class="niyati-credit-free" id="niyati-quota-free" style="display:none;"></span>
                            <span class="niyati-credit-validity" id="niyati-quota-validity" style="display:none;"></span>
                        </div>
                    </div>
                    <div class="niyati-credit-action">
                        <button id="niyati-btn-recharge" class="niyati-btn-recharge" title="Recharge Chat Credits">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span id="niyati-recharge-btn-text">Recharge</span>
                        </button>
                    </div>
                </div>

                <!-- VIEW 1: ONBOARDING FORM -->
                <div id="niyati-view-onboarding" class="niyati-view niyati-onboarding-view">
                    <div class="niyati-onboarding-intro">
                        <h3 id="niyati-onboarding-title">Birth Chart & Consultation</h3>
                        <p id="niyati-onboarding-sub">Enter your birth details for accurate planetary insights</p>
                    </div>

                    <!-- Saved Family Members Quick Select -->
                    <div id="niyati-saved-profiles-wrap" class="niyati-saved-profiles-wrap" style="display:none;"></div>

                    <form id="niyati-birth-form">
                        <div class="niyati-form-group">
                            <label id="niyati-lbl-name">Full Name:</label>
                            <input type="text" id="niyati-input-name" placeholder="e.g. Rahul Sharma" required>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <div class="niyati-form-group" style="flex:1;">
                                <label id="niyati-lbl-dob">Date of Birth (DOB):</label>
                                <input type="date" id="niyati-input-dob" required>
                            </div>
                            <div class="niyati-form-group" style="flex:1;">
                                <label id="niyati-lbl-tob">Time of Birth (Time):</label>
                                <input type="time" id="niyati-input-tob" value="12:00" required>
                            </div>
                        </div>

                        <div class="niyati-form-group">
                            <label id="niyati-lbl-pob">Place of Birth:</label>
                            <input type="text" id="niyati-input-pob" placeholder="e.g. New Delhi, Mumbai, New York..." autocomplete="off" required>
                            <div id="niyati-pob-results" class="niyati-city-results"></div>
                            <input type="hidden" id="niyati-input-lat" value="28.6139">
                            <input type="hidden" id="niyati-input-lon" value="77.2090">
                            <input type="hidden" id="niyati-input-tz" value="5.5">
                        </div>

                        <div class="niyati-form-group">
                            <label id="niyati-lbl-lang">Preferred Language:</label>
                            <select id="niyati-input-lang">
                                <option value="en" selected>English</option>
                                <option value="hi">हिंदी (Hindi)</option>
                                <option value="hinglish">Hinglish</option>
                            </select>
                        </div>

                        <div id="niyati-login-notice" class="niyati-login-notice" style="display:none;">
                            <span>🔒</span> <span id="niyati-login-notice-text">Please log in to your account to start your consultation.</span>
                        </div>

                        <button type="submit" id="niyati-btn-compute" class="niyati-btn-submit">
                            ✨ Analyze Chart & Start Chat
                        </button>
                    </form>
                </div>

                <!-- VIEW 2: CHAT INTERFACE (Single unified window for messages and suggested questions) -->
                <div id="niyati-view-chat" class="niyati-view">
                    <div id="niyati-chat-list" class="niyati-chat-messages">
                        <!-- Suggestions Chips sit inline inside the same chat window -->
                        <div id="niyati-suggestions" class="niyati-suggestions-wrap" style="display:none;"></div>
                    </div>

                    <!-- Chat Input -->
                    <div class="niyati-input-bar">
                        <input type="text" id="niyati-user-input" placeholder="Ask Niyati a question..." autocomplete="off">
                        <button id="niyati-btn-send" class="niyati-send-btn" title="Send">➤</button>
                    </div>
                </div>

                <!-- RECHARGE MODAL / DIALOG -->
                <div id="niyati-recharge-sheet" class="niyati-recharge-modal">
                    <div class="niyati-recharge-backdrop"></div>
                    <div class="niyati-recharge-dialog">
                        <div class="niyati-recharge-header">
                            <div class="niyati-recharge-title-wrap">
                                <div class="niyati-recharge-icon">
                                    <img src="${(window.niyatiConfig && window.niyatiConfig.loader_url) ? window.niyatiConfig.loader_url : '/wp-content/uploads/2025/11/loader.webp'}" alt="" class="niyati-spin-loader-icon" />
                                </div>
                                <div class="niyati-recharge-titles">
                                    <h3 id="niyati-recharge-title">Recharge Chat Credits</h3>
                                    <div id="niyati-recharge-sub" class="niyati-recharge-subtitle">
                                        ⚡ 1 Question = 1 Credit • Instant Activation • Valid Lifetime
                                    </div>
                                </div>
                            </div>
                            <button id="niyati-recharge-close" class="niyati-recharge-close" title="Close">✕</button>
                        </div>

                        <div id="niyati-packs-container" class="niyati-packs-grid"></div>

                        <div class="niyati-recharge-footer">
                            <div class="niyati-trust-badges">
                                <span class="niyati-trust-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    100% Safe & Secure
                                </span>
                                <span class="niyati-trust-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                    Instant Wallet Credit
                                </span>
                                <span class="niyati-trust-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    UPI • Cards • NetBanking
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LOGIN REQUIRED MODAL -->
                <div id="niyati-login-modal" class="niyati-login-modal" style="display:none;">
                    <div class="niyati-login-card">
                        <div class="niyati-login-icon">🔒</div>
                        <h3 id="niyati-login-title" class="niyati-login-title">Login Required</h3>
                        <p id="niyati-login-desc" class="niyati-login-desc">Please log in to your account first to generate your birth chart and access Niyati consultations.</p>
                        <div class="niyati-login-actions">
                            <a id="niyati-login-cta" class="niyati-btn-login-cta" href="#">
                                <span id="niyati-login-cta-text">🔑 Log In to Continue</span>
                            </a>
                            <button type="button" id="niyati-login-cancel" class="niyati-btn-login-cancel">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(wrapper);

        // Cache elements
        el.launcher = document.getElementById('niyati-launcher');
        el.launcherLabel = document.getElementById('niyati-launcher-label');
        el.container = document.getElementById('niyati-container');
        el.btnClose = document.getElementById('niyati-btn-close');
        el.btnFullscreen = document.getElementById('niyati-btn-fullscreen');
        el.btnReset = document.getElementById('niyati-btn-reset');
        el.headerSubtitle = document.getElementById('niyati-header-subtitle');
        el.headerAvatar = document.getElementById('niyati-header-avatar');
        el.viewOnboarding = document.getElementById('niyati-view-onboarding');
        el.savedProfilesWrap = document.getElementById('niyati-saved-profiles-wrap');
        el.viewChat = document.getElementById('niyati-view-chat');
        el.birthForm = document.getElementById('niyati-birth-form');
        el.inputName = document.getElementById('niyati-input-name');
        el.inputDob = document.getElementById('niyati-input-dob');
        el.inputTob = document.getElementById('niyati-input-tob');
        el.inputPob = document.getElementById('niyati-input-pob');
        el.inputLat = document.getElementById('niyati-input-lat');
        el.inputLon = document.getElementById('niyati-input-lon');
        el.inputTz = document.getElementById('niyati-input-tz');
        el.inputLang = document.getElementById('niyati-input-lang');
        el.pobResults = document.getElementById('niyati-pob-results');
        el.btnCompute = document.getElementById('niyati-btn-compute');
        el.loginNotice = document.getElementById('niyati-login-notice');
        el.loginNoticeText = document.getElementById('niyati-login-notice-text');
        el.loginModal = document.getElementById('niyati-login-modal');
        el.loginTitle = document.getElementById('niyati-login-title');
        el.loginDesc = document.getElementById('niyati-login-desc');
        el.loginCta = document.getElementById('niyati-login-cta');
        el.loginCtaText = document.getElementById('niyati-login-cta-text');
        el.loginCancel = document.getElementById('niyati-login-cancel');
        el.chatList = document.getElementById('niyati-chat-list');
        el.suggestions = document.getElementById('niyati-suggestions');
        el.userInput = document.getElementById('niyati-user-input');
        el.btnSend = document.getElementById('niyati-btn-send');
        el.quotaBadge = document.getElementById('niyati-quota-badge');
        el.quotaFree = document.getElementById('niyati-quota-free');
        el.quotaValidity = document.getElementById('niyati-quota-validity');
        el.creditIcon = document.getElementById('niyati-credit-icon');
        el.rechargeBtnText = document.getElementById('niyati-recharge-btn-text');
        el.quotaText = document.getElementById('niyati-quota-badge');
        el.launcherBadge = document.getElementById('niyati-launcher-badge');
        el.btnRecharge = document.getElementById('niyati-btn-recharge');
        el.rechargeSheet = document.getElementById('niyati-recharge-sheet');
        el.rechargeClose = document.getElementById('niyati-recharge-close');
        el.packsContainer = document.getElementById('niyati-packs-container');
        el.nudgeBubble = document.getElementById('niyati-nudge-bubble');
        el.nudgeText = document.getElementById('niyati-nudge-text');
        el.nudgeClose = document.getElementById('niyati-nudge-close');
    }

    function applyLanguage(lang) {
        var t = i18n[lang] || i18n['en'];
        if (el.launcherLabel) el.launcherLabel.textContent = t.launcherText;
        if (el.headerSubtitle) el.headerSubtitle.textContent = (lang === 'hi' ? 'वैदिक ज्योतिषी' : 'Vedic Astrologer');
        if (el.btnReset) el.btnReset.title = t.resetTitle;
        if (el.rechargeBtnText) el.rechargeBtnText.textContent = (t.rechargePill || 'Recharge').replace('⚡', '').trim();

        var obTitle = document.getElementById('niyati-onboarding-title');
        var obSub = document.getElementById('niyati-onboarding-sub');
        if (obTitle) obTitle.textContent = t.onboardingTitle;
        if (obSub) obSub.textContent = t.onboardingSub;

        var lblName = document.getElementById('niyati-lbl-name');
        var lblDob = document.getElementById('niyati-lbl-dob');
        var lblTob = document.getElementById('niyati-lbl-tob');
        var lblPob = document.getElementById('niyati-lbl-pob');
        var lblLang = document.getElementById('niyati-lbl-lang');
        if (lblName) lblName.textContent = t.lblName;
        if (el.inputName) el.inputName.placeholder = t.placeholderName;
        if (lblDob) lblDob.textContent = t.lblDob;
        if (lblTob) lblTob.textContent = t.lblTob;
        if (lblPob) lblPob.textContent = t.lblPob;
        if (el.inputPob) el.inputPob.placeholder = t.placeholderPob;
        if (lblLang) lblLang.textContent = t.lblLang;
        if (el.btnCompute && !state.isComputing) el.btnCompute.textContent = t.btnCompute;

        if (el.loginNoticeText) el.loginNoticeText.textContent = t.loginNotice;
        if (el.loginTitle) el.loginTitle.textContent = t.loginRequiredTitle;
        if (el.loginDesc) el.loginDesc.textContent = t.loginRequiredMsg;
        if (el.loginCtaText) el.loginCtaText.textContent = t.btnLogin;
        if (el.loginCancel) el.loginCancel.textContent = t.btnCancel;

        if (el.userInput) el.userInput.placeholder = t.inputPlaceholder;

        var recTitle = document.getElementById('niyati-recharge-title');
        var recSub = document.getElementById('niyati-recharge-sub');
        if (recTitle) recTitle.textContent = t.rechargeTitle;
        if (recSub) recSub.textContent = t.rechargeSub;

        if (el.inputLang && el.inputLang.value !== lang) {
            el.inputLang.value = lang;
        }

        updateLoginNoticeUI();
    }

    function bindEvents() {
        // Toggle widget / Close
        el.launcher.addEventListener('click', toggleWidget);
        el.btnClose.addEventListener('click', handleCloseClick);

        // Fullscreen Toggle
        if (el.btnFullscreen) {
            el.btnFullscreen.addEventListener('click', function (e) {
                if (e && e.preventDefault) e.preventDefault();
                toggleFullscreen();
            });
        }

        // Close fullscreen on Escape key
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && state.isFullscreen) {
                toggleFullscreen(false);
            }
        });

        // Guard input click when disabled
        if (el.userInput && el.userInput.parentElement) {
            el.userInput.parentElement.addEventListener('click', function () {
                if (!state.isInitialized) {
                    showInitializingToast();
                }
            });
        }

        // Reset Kundli
        el.btnReset.addEventListener('click', function () {
            var t = i18n[state.lang] || i18n['en'];
            if (confirm(t.confirmReset)) {
                resetKundli();
            }
        });

        // Dynamic Language Change Listener: When user changes language, all UI updates immediately
        el.inputLang.addEventListener('change', function () {
            state.lang = this.value;
            localStorage.setItem('niyati_lang', state.lang);
            applyLanguage(state.lang);
            renderSuggestions();
        });

        // Recharge Modal Toggle
        el.btnRecharge.addEventListener('click', function (e) {
            if (!state.isLoggedIn) {
                showLoginModal();
                return;
            }
            openRechargeSheet();
        });
        if (el.quotaBadge && el.quotaBadge.parentElement) {
            el.quotaBadge.parentElement.addEventListener('click', function () {
                if (!state.isLoggedIn) {
                    showLoginModal();
                }
            });
        }
        el.rechargeClose.addEventListener('click', closeRechargeSheet);
        var recBackdrop = el.rechargeSheet.querySelector('.niyati-recharge-backdrop');
        if (recBackdrop) {
            recBackdrop.addEventListener('click', closeRechargeSheet);
        }

        // Login Modal Listeners
        if (el.loginCancel) {
            el.loginCancel.addEventListener('click', closeLoginModal);
        }
        if (el.loginModal) {
            el.loginModal.addEventListener('click', function (e) {
                if (e.target === el.loginModal) {
                    closeLoginModal();
                }
            });
        }

        // City Autocomplete
        var cityTimeout;
        el.inputPob.addEventListener('input', function () {
            var val = this.value.trim();
            clearTimeout(cityTimeout);
            if (val.length < 2) {
                el.pobResults.style.display = 'none';
                return;
            }
            cityTimeout = setTimeout(function () {
                fetchCities(val);
            }, 300);
        });

        // Form Submit
        el.birthForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleBirthFormSubmit();
        });

        // Chat Input & Send
        el.btnSend.addEventListener('click', sendMessage);
        el.userInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Close city dropdown on outside click
        document.addEventListener('click', function (e) {
            if (!el.inputPob.contains(e.target) && !el.pobResults.contains(e.target)) {
                el.pobResults.style.display = 'none';
            }
        });
    }

    function updateLoginNoticeUI() {
        if (!el.loginNotice) return;
        var t = i18n[state.lang] || i18n['en'];
        if (!state.isLoggedIn) {
            el.loginNotice.style.display = 'flex';
            if (el.loginNoticeText) el.loginNoticeText.textContent = t.loginNotice || '🔒 Please log in to your account to start your consultation.';
            if (el.btnCompute && !state.isComputing) {
                el.btnCompute.innerHTML = '🔒 ' + (state.lang === 'hi' ? 'लॉगिन करें एवं परामर्श शुरू करें' : (state.lang === 'hinglish' ? 'Login Karein & Chat Shuru Karein' : 'Log In to Start Consultation'));
            }
        } else {
            el.loginNotice.style.display = 'none';
            if (el.btnCompute && !state.isComputing) {
                el.btnCompute.innerHTML = t.btnCompute || '✨ Analyze Chart & Start Chat';
            }
        }
    }

    function initNudgeSystem() {
        if (!el.nudgeBubble) return;

        // Click bubble to open chat widget
        el.nudgeBubble.addEventListener('click', function (e) {
            if (e.target === el.nudgeClose || (el.nudgeClose && el.nudgeClose.contains(e.target))) {
                return;
            }
            hideNudgeBubble();
            if (!state.isOpen) {
                toggleWidget();
            }
        });

        // Close button dismisses nudge
        if (el.nudgeClose) {
            el.nudgeClose.addEventListener('click', function (e) {
                e.stopPropagation();
                hideNudgeBubble();
            });
        }

        // Hover pauses auto-hide; mouseleave resumes countdown
        el.nudgeBubble.addEventListener('mouseenter', function () {
            if (state.nudgeTimeout) {
                clearTimeout(state.nudgeTimeout);
                state.nudgeTimeout = null;
            }
        });

        el.nudgeBubble.addEventListener('mouseleave', function () {
            if (el.nudgeBubble.classList.contains('visible')) {
                var dispSec = (state.nudge && typeof state.nudge.display_seconds === 'number') ? state.nudge.display_seconds : 30;
                if (dispSec > 0) {
                    clearTimeout(state.nudgeTimeout);
                    state.nudgeTimeout = setTimeout(hideNudgeBubble, dispSec * 1000);
                }
            }
        });

        // First load trigger after 3.5 seconds
        setTimeout(function () {
            checkAndTriggerNudge();
        }, 3500);

        // Recurring check every 60 seconds
        setInterval(function () {
            checkAndTriggerNudge();
        }, 60 * 1000);
    }

    function checkAndTriggerNudge() {
        if (state.isOpen) return;
        if (!state.nudge || !state.nudge.enabled) return;

        var lastShown = localStorage.getItem('niyati_nudge_last_shown');
        var intervalMin = (state.nudge && state.nudge.interval_minutes) ? state.nudge.interval_minutes : 30;
        var intervalMs = intervalMin * 60 * 1000;

        if (!lastShown) {
            // First load for this user!
            showNudgeBubble();
        } else {
            var diff = Date.now() - parseInt(lastShown, 10);
            if (isNaN(diff) || diff >= intervalMs) {
                showNudgeBubble();
            }
        }
    }

    function showNudgeBubble() {
        if (state.isOpen || !el.nudgeBubble) return;
        if (!state.nudge || !state.nudge.enabled) return;

        var msg = state.nudge.message || 'Ask Niyati is active to answer all your questions';
        if (el.nudgeText) {
            el.nudgeText.textContent = msg;
        }

        el.nudgeBubble.style.display = 'flex';
        // Force reflow for animation
        void el.nudgeBubble.offsetWidth;
        el.nudgeBubble.classList.add('visible');

        try {
            localStorage.setItem('niyati_nudge_last_shown', Date.now().toString());
        } catch (e) {}

        var dispSec = (state.nudge && typeof state.nudge.display_seconds === 'number') ? state.nudge.display_seconds : 30;
        if (dispSec > 0) {
            clearTimeout(state.nudgeTimeout);
            state.nudgeTimeout = setTimeout(function () {
                hideNudgeBubble();
            }, dispSec * 1000);
        }
    }

    function hideNudgeBubble() {
        if (!el.nudgeBubble) return;
        if (state.nudgeTimeout) {
            clearTimeout(state.nudgeTimeout);
            state.nudgeTimeout = null;
        }
        el.nudgeBubble.classList.remove('visible');
        setTimeout(function () {
            if (!el.nudgeBubble.classList.contains('visible')) {
                el.nudgeBubble.style.display = 'none';
            }
        }, 300);
    }

    function showLoginModal() {
        if (!el.loginModal) return;
        var t = i18n[state.lang] || i18n['en'];
        if (el.loginTitle) el.loginTitle.textContent = t.loginRequiredTitle || '🔒 Login Required';
        if (el.loginDesc) el.loginDesc.textContent = t.loginRequiredMsg || 'Please log in to your account first to generate your birth chart and access Niyati consultations.';
        if (el.loginCtaText) el.loginCtaText.textContent = t.btnLogin || '🔑 Log In to Continue';
        if (el.loginCancel) el.loginCancel.textContent = t.btnCancel || 'Cancel';
        if (el.loginCta) el.loginCta.href = state.loginUrl || '/wp-login.php';
        el.loginModal.style.display = 'flex';
    }

    function closeLoginModal() {
        if (el.loginModal) {
            el.loginModal.style.display = 'none';
        }
    }

    function toggleFullscreen(forceState) {
        var next = (typeof forceState === 'boolean') ? forceState : !state.isFullscreen;
        state.isFullscreen = next;

        var iconExpand = el.btnFullscreen ? el.btnFullscreen.querySelector('.niyati-icon-expand') : null;
        var iconCompress = el.btnFullscreen ? el.btnFullscreen.querySelector('.niyati-icon-compress') : null;

        if (state.isFullscreen) {
            el.container.classList.add('niyati-fullscreen');
            document.body.classList.add('niyati-body-fullscreen-lock');
            if (iconExpand) iconExpand.style.display = 'none';
            if (iconCompress) iconCompress.style.display = 'block';
            if (el.btnFullscreen) el.btnFullscreen.title = 'Exit Full Screen';
            if (el.btnClose) el.btnClose.title = 'Back to Normal Mode (✕)';
        } else {
            el.container.classList.remove('niyati-fullscreen');
            document.body.classList.remove('niyati-body-fullscreen-lock');
            if (iconExpand) iconExpand.style.display = 'block';
            if (iconCompress) iconCompress.style.display = 'none';
            if (el.btnFullscreen) el.btnFullscreen.title = 'Full Screen';
            if (el.btnClose) el.btnClose.title = 'Close';
        }

        if (state.isOpen) {
            scrollToBottom();
            if (el.userInput && !el.userInput.disabled) {
                el.userInput.focus();
            }
        }
    }

    function handleCloseClick(e) {
        if (e && e.preventDefault) e.preventDefault();
        // If in fullscreen mode, crossing it exits fullscreen and returns back to normal mode
        if (state.isFullscreen) {
            toggleFullscreen(false);
            return;
        }
        // In normal mode, closing it minimizes the widget
        toggleWidget(e);
    }

    function toggleWidget(e) {
        if (!state.isInitialized) {
            if (e && e.preventDefault) e.preventDefault();
            showInitializingToast();
            return;
        }
        state.isOpen = !state.isOpen;
        if (state.isOpen) {
            hideNudgeBubble();
            el.container.classList.add('active');
            if (window.mrCurrentReportContext && window.mrCurrentReportContext.slug && !state.activeReportContext) {
                applyReportContext(window.mrCurrentReportContext.slug, window.mrCurrentReportContext.title);
            } else if (state.chartToken && state.birthData) {
                checkAndApplyReturnGreeting();
            }
            scrollToBottom();
        } else {
            if (state.isFullscreen) {
                toggleFullscreen(false);
            }
            el.container.classList.remove('active');
        }
    }

    function openRechargeSheet() {
        renderPacksList();
        el.rechargeSheet.classList.add('active');
    }

    function closeRechargeSheet() {
        el.rechargeSheet.classList.remove('active');
    }

    function showView(viewName) {
        if (viewName === 'chat') {
            el.viewOnboarding.classList.remove('active');
            el.viewChat.classList.add('active');
            renderSuggestions();
            scrollToBottom();
        } else {
            el.viewChat.classList.remove('active');
            el.viewOnboarding.classList.add('active');
            renderSavedProfiles();
        }
    }

    function renderSavedProfiles() {
        if (!el.savedProfilesWrap) return;
        if (!state.isLoggedIn || !state.savedProfiles || !state.savedProfiles.length) {
            el.savedProfilesWrap.style.display = 'none';
            return;
        }

        var count = state.savedProfiles.length;
        var titleText = (state.lang === 'hi')
            ? ('👨‍👩‍👧‍👦 सदस्य चुनें (' + count + '):')
            : ('👨‍👩‍👧‍👦 Quick Select Profile (' + count + '):');
        var newPersonText = (state.lang === 'hi') ? '+ नया व्यक्ति' : '+ New Person';

        el.savedProfilesWrap.innerHTML = `
            <div class="niyati-saved-profiles-header">
                <span class="niyati-saved-profiles-title">${titleText}</span>
                <button type="button" class="niyati-btn-new-profile" id="niyati-btn-new-profile">${newPersonText}</button>
            </div>
            <div class="niyati-saved-profiles-chips" id="niyati-saved-profiles-chips"></div>
        `;

        var chipsContainer = el.savedProfilesWrap.querySelector('#niyati-saved-profiles-chips');
        var btnNew = el.savedProfilesWrap.querySelector('#niyati-btn-new-profile');

        state.savedProfiles.forEach(function (prof) {
            var chip = document.createElement('div');
            chip.className = 'niyati-profile-chip';
            if (state.birthData && state.birthData.name && (state.birthData.name || '').toLowerCase() === (prof.name || '').toLowerCase() && state.birthData.dob === prof.dob) {
                chip.classList.add('active');
            }

            var metaParts = [];
            if (prof.dob) metaParts.push(prof.dob);
            if (prof.moon_sign) metaParts.push('🌙 ' + prof.moon_sign);
            var metaText = metaParts.join(' • ');

            chip.innerHTML = `
                <div class="niyati-chip-avatar">👤</div>
                <div class="niyati-chip-info">
                    <span class="niyati-chip-name">${escapeHTML(prof.name || 'Member')}</span>
                    ${metaText ? `<span class="niyati-chip-meta">${escapeHTML(metaText)}</span>` : ''}
                </div>
            `;

            chip.addEventListener('click', function () {
                if (el.inputName) el.inputName.value = prof.name || '';
                if (el.inputDob) el.inputDob.value = prof.dob || '';
                if (el.inputTob) el.inputTob.value = prof.tob || '12:00';
                if (el.inputPob) el.inputPob.value = prof.pob || '';
                if (el.inputLat) el.inputLat.value = prof.lat || '28.6139';
                if (el.inputLon) el.inputLon.value = prof.lon || '77.2090';
                if (el.inputTz) el.inputTz.value = prof.tz || '5.5';
                if (prof.lang && el.inputLang) el.inputLang.value = prof.lang;

                var allChips = chipsContainer.querySelectorAll('.niyati-profile-chip');
                allChips.forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');

                handleBirthFormSubmit();
            });

            chipsContainer.appendChild(chip);
        });

        if (btnNew) {
            btnNew.addEventListener('click', function (e) {
                e.preventDefault();
                var allChips = chipsContainer.querySelectorAll('.niyati-profile-chip');
                allChips.forEach(function (c) { c.classList.remove('active'); });
                if (el.inputName) {
                    el.inputName.value = '';
                    el.inputName.focus();
                }
                if (el.inputDob) el.inputDob.value = '';
                if (el.inputTob) el.inputTob.value = '12:00';
                if (el.inputPob) el.inputPob.value = '';
                if (el.inputLat) el.inputLat.value = '28.6139';
                if (el.inputLon) el.inputLon.value = '77.2090';
                if (el.inputTz) el.inputTz.value = '5.5';
            });
        }

        el.savedProfilesWrap.style.display = 'block';
    }

    function fetchInitData() {
        var wpNonce = getWpNonce();
        var wpUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? window.niyatiConfig.user_id : 0;
        var url = restBase + '/init' + (wpUserId ? '?user_id=' + wpUserId : '');

        fetch(url, {
            headers: {
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin'
        })
        .then(function (res) {
            var freshNonce = res.headers ? res.headers.get('X-WP-Nonce') : null;
            if (freshNonce) {
                state.nonce = freshNonce;
                if (window.niyatiConfig) window.niyatiConfig.nonce = freshNonce;
            }
            return res.json();
        })
        .then(function (data) {
            if (data.nonce) {
                state.nonce = data.nonce;
                if (window.niyatiConfig) window.niyatiConfig.nonce = data.nonce;
            }
            state.isInitialized = true;
            setChatbotEnabled(true);

            if (typeof data.is_logged_in === 'boolean') {
                state.isLoggedIn = data.is_logged_in;
            }
            if (data.login_url) {
                state.loginUrl = data.login_url;
            }
            if (data.avatar_url) {
                state.avatarUrl = data.avatar_url;
                if (el.headerAvatar) {
                    el.headerAvatar.src = data.avatar_url;
                }
            }
            updateLoginNoticeUI();

            // CRITICAL: If logged out, immediately clean all local session data and force clean onboarding view!
            if (!state.isLoggedIn) {
                clearUserSessionData();
                showView('onboarding');
                updateQuotaUI();
                return;
            }

            if (data.success) {
                state.suggestions = data.questions || {};
                state.quota = data.quota || null;
                state.paidCredits = (typeof data.paid_credits === 'number') ? data.paid_credits : (parseInt(data.paid_credits, 10) || 0);
                state.validity = data.validity || null;
                state.packs = data.packs || [];
                state.razorpayKey = data.razorpay_key_id || '';
                state.razorpayTestMode = data.razorpay_test_mode;
                state.visitorId = data.visitor_id || '';

                // Cross-device sync: if server returns active chat history from disk, sync it
                if (data.chat_history && Array.isArray(data.chat_history) && data.chat_history.length > 0) {
                    state.history = data.chat_history;
                    localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));
                    if (data.active_chart_token) {
                        state.chartToken = data.active_chart_token;
                        localStorage.setItem('niyati_chart_token', state.chartToken);
                    }
                    if (state.isOpen && el.viewChat && el.viewChat.classList.contains('active')) {
                        renderStoredHistory();
                    }
                }

                if (data.saved_profiles && Array.isArray(data.saved_profiles)) {
                    state.savedProfiles = data.saved_profiles;
                    renderSavedProfiles();
                }

                updateQuotaUI();
                renderSuggestions();
                renderPacksList();
            } else {
                updateQuotaUI();
            }

            if (data.nudge) {
                state.nudge = data.nudge;
            }
        })
        .catch(function (err) {
            console.warn('Niyati Init Error:', err);
            state.isInitialized = true;
            setChatbotEnabled(true);
            updateQuotaUI();
            updateLoginNoticeUI();
        });
    }

    function updateQuotaUI() {
        var t = i18n[state.lang] || i18n['en'];

        if (!state.isInitialized) {
            if (el.creditIcon) {
                el.creditIcon.innerHTML = '<span class="niyati-spin">⏳</span>';
            }
            if (el.quotaBadge) {
                el.quotaBadge.textContent = t.initializing || 'Connecting...';
                el.quotaBadge.classList.remove('exhausted');
            }
            if (el.quotaFree) el.quotaFree.style.display = 'none';
            if (el.quotaValidity) el.quotaValidity.style.display = 'none';
            if (el.btnRecharge) el.btnRecharge.classList.remove('urgent');

            if (el.launcherBadge) {
                el.launcherBadge.style.display = 'inline-block';
                el.launcherBadge.textContent = '...';
                el.launcherBadge.style.background = '#94a3b8';
                el.launcherBadge.style.color = '#ffffff';
            }
            return;
        }

        // When user is NOT logged in: show Login prompt in credit bar
        if (!state.isLoggedIn) {
            if (el.creditIcon) {
                el.creditIcon.innerHTML = '<span style="font-size:12px;">🔒</span>';
            }
            if (el.quotaBadge) {
                el.quotaBadge.textContent = (state.lang === 'hi') ? 'परामर्श हेतु लॉगिन करें' : 'Login to Consult';
                el.quotaBadge.classList.remove('exhausted');
            }
            if (el.quotaFree) el.quotaFree.style.display = 'none';
            if (el.quotaValidity) el.quotaValidity.style.display = 'none';
            if (el.btnRecharge) {
                el.btnRecharge.classList.remove('urgent');
            }
            if (el.rechargeBtnText) {
                el.rechargeBtnText.textContent = (state.lang === 'hi') ? 'लॉगिन' : 'Log In';
            }

            if (el.launcherBadge) {
                el.launcherBadge.style.display = 'inline-block';
                el.launcherBadge.textContent = (state.lang === 'hi') ? 'लॉगिन' : 'Log In';
                el.launcherBadge.style.background = '#d97706';
                el.launcherBadge.style.color = '#ffffff';
            }
            return;
        }

        var freeRem = (state.quota && typeof state.quota.remaining === 'number') ? state.quota.remaining : 0;
        var paidRem = state.paidCredits || 0;

        if (paidRem > 0) {
            if (el.creditIcon) {
                el.creditIcon.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>';
            }
            if (el.quotaBadge) {
                el.quotaBadge.textContent = paidRem + ' Credits';
                el.quotaBadge.classList.remove('exhausted');
            }
            if (el.quotaFree) {
                if (freeRem > 0) {
                    el.quotaFree.textContent = '+' + freeRem + ' Free';
                    el.quotaFree.style.display = 'inline-block';
                } else {
                    el.quotaFree.style.display = 'none';
                }
            }
            if (el.quotaValidity) {
                if (state.validity && state.validity.has_expiry && !state.validity.is_expired) {
                    el.quotaValidity.textContent = state.validity.days_left + 'd left';
                    el.quotaValidity.style.display = 'inline-block';
                } else {
                    el.quotaValidity.style.display = 'none';
                }
            }
            if (el.btnRecharge) {
                el.btnRecharge.classList.remove('urgent');
            }
            if (el.rechargeBtnText) {
                el.rechargeBtnText.textContent = (t.rechargePill || 'Recharge').replace('⚡', '').trim();
            }

            if (el.launcherBadge) {
                el.launcherBadge.style.display = 'inline-block';
                el.launcherBadge.textContent = '⚡ ' + paidRem + ' Credits';
                el.launcherBadge.style.background = '#15803d';
                el.launcherBadge.style.color = '#ffffff';
            }
        } else if (freeRem > 0) {
            if (el.creditIcon) {
                el.creditIcon.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            }
            if (el.quotaBadge) {
                el.quotaBadge.textContent = freeRem + ' Free Left';
                el.quotaBadge.classList.remove('exhausted');
            }
            if (el.quotaFree) el.quotaFree.style.display = 'none';
            if (el.quotaValidity) el.quotaValidity.style.display = 'none';
            if (el.btnRecharge) {
                el.btnRecharge.classList.remove('urgent');
            }
            if (el.rechargeBtnText) {
                el.rechargeBtnText.textContent = (t.rechargePill || 'Recharge').replace('⚡', '').trim();
            }

            if (el.launcherBadge) {
                el.launcherBadge.style.display = 'inline-block';
                el.launcherBadge.textContent = freeRem + ' Free';
                el.launcherBadge.style.background = '#d97706';
                el.launcherBadge.style.color = '#ffffff';
            }
        } else {
            // Exhausted
            if (el.creditIcon) {
                el.creditIcon.innerHTML = '<span style="color:#ef4444; font-size:11px;">⚠️</span>';
            }
            if (el.quotaBadge) {
                el.quotaBadge.textContent = '0 Credits';
                el.quotaBadge.classList.add('exhausted');
            }
            if (el.quotaFree) el.quotaFree.style.display = 'none';
            if (el.quotaValidity) el.quotaValidity.style.display = 'none';
            if (el.btnRecharge) {
                el.btnRecharge.classList.add('urgent');
            }
            if (el.rechargeBtnText) {
                el.rechargeBtnText.textContent = (state.lang === 'hi' ? 'रिचार्ज करें' : 'Recharge Now');
            }

            if (el.launcherBadge) {
                el.launcherBadge.style.display = 'inline-block';
                el.launcherBadge.textContent = 'Recharge';
                el.launcherBadge.style.background = '#ef4444';
                el.launcherBadge.style.color = '#ffffff';
            }
        }
    }

    function renderPacksList() {
        if (!state.packs || !state.packs.length) return;
        el.packsContainer.innerHTML = '';

        state.packs.forEach(function (pack) {
            var isFeatured = !!pack.featured;
            var card = document.createElement('div');
            card.className = 'niyati-pack-card' + (isFeatured ? ' featured' : '');

            // Sleek badge text
            var badgeText = pack.badge || (isFeatured ? 'Best Value (33% OFF)' : '');

            var discountHTML = (pack.discount_pct > 0)
                ? `<span class="niyati-pack-discount-pill">${pack.discount_pct}% OFF</span>`
                : '';

            // Clean English and Hindi Title parsing - English First, Hindi Second
            var rawTitle = pack.title || '';
            var englishTitle = rawTitle;
            var hindiTitle = '';
            var titleMatch = rawTitle.match(/^(.*?)\s*[\(\[]\s*(.*?)\s*[\)\]]$/);
            if (titleMatch) {
                var hasDevanagari1 = /[\u0900-\u097F]/.test(titleMatch[1]);
                var hasDevanagari2 = /[\u0900-\u097F]/.test(titleMatch[2]);
                if (hasDevanagari1 && !hasDevanagari2) {
                    englishTitle = titleMatch[2].trim();
                    hindiTitle = titleMatch[1].trim();
                } else {
                    englishTitle = titleMatch[1].trim();
                    hindiTitle = titleMatch[2].trim();
                }
            }

            // Total, Base, and Bonus Credits
            var totalCredits = parseInt(pack.total_credits, 10) || 0;
            var bonusCredits = parseInt(pack.bonus_credits, 10) || 0;
            var baseCredits = parseInt(pack.base_credits, 10) || (totalCredits - bonusCredits);
            if (baseCredits <= 0) baseCredits = totalCredits;

            var bonusBadge = (bonusCredits > 0) 
                ? `<span class="niyati-strip-bonus">${baseCredits} Base + 🎁 ${bonusCredits} Free</span>` 
                : '';

            card.innerHTML = `
                <div class="niyati-pack-card-top">
                    <div class="niyati-pack-info">
                        ${badgeText ? `<div class="niyati-pack-tag ${isFeatured ? 'featured-tag' : ''}">${escapeHTML(badgeText)}</div>` : ''}
                        <h4 class="niyati-pack-name">
                            <span class="niyati-en-name">${escapeHTML(englishTitle)}</span>
                            ${hindiTitle ? `<span class="niyati-hi-name">(${escapeHTML(hindiTitle)})</span>` : ''}
                        </h4>
                    </div>
                    <div class="niyati-pack-pricing">
                        <div class="niyati-price-current">
                            <span class="niyati-curr">₹</span><span class="niyati-amt">${pack.offer_price}</span>
                        </div>
                        <div class="niyati-price-prev">
                            ${pack.base_price > pack.offer_price ? `<span class="niyati-mrp">₹${pack.base_price}</span>` : ''}
                            ${discountHTML}
                        </div>
                    </div>
                </div>

                <div class="niyati-pack-value-strip">
                    <div class="niyati-strip-main">
                        <span class="niyati-strip-num">${totalCredits}</span>
                        <span class="niyati-strip-label">Questions Total</span>
                    </div>
                    ${bonusBadge}
                </div>

                <div class="niyati-pack-meta-row">
                    <span class="niyati-meta-rate">₹${pack.rate_per_question}/Q</span>
                    <span class="niyati-meta-sep">•</span>
                    <span class="niyati-meta-perk">Valid Lifetime</span>
                    <span class="niyati-meta-sep">•</span>
                    <span class="niyati-meta-perk">Instant Credit</span>
                </div>

                <button class="niyati-btn-buy ${isFeatured ? 'featured-btn' : ''}" data-pack="${pack.key}">
                    <span>Recharge for ₹${pack.offer_price}</span>
                    <span class="niyati-buy-arrow">&rarr;</span>
                </button>
            `;

            var btn = card.querySelector('.niyati-btn-buy');
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                handleBuyPack(pack, btn);
            });

            card.addEventListener('click', function (e) {
                if (e.target.closest('.niyati-btn-buy')) return;
                handleBuyPack(pack, btn);
            });

            el.packsContainer.appendChild(card);
        });
    }

    function handleBuyPack(pack, btn) {
        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '⏳ Processing...';

        var wpNonce = getWpNonce();
        var wpUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? window.niyatiConfig.user_id : 0;
        var wpUserName = (window.niyatiConfig && window.niyatiConfig.user_name) ? window.niyatiConfig.user_name : '';
        var wpUserEmail = (window.niyatiConfig && window.niyatiConfig.user_email) ? window.niyatiConfig.user_email : '';
        var custName = wpUserName || (state.birthData && state.birthData.name ? state.birthData.name : '');

        fetch(restBase + '/create-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                pack_key: pack.key,
                visitor_id: state.visitorId,
                user_id: wpUserId,
                customer_name: custName,
                customer_email: wpUserEmail
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (orderData) {
            if (!orderData.success) {
                alert(orderData.message || 'Unable to create order.');
                btn.disabled = false;
                btn.textContent = origText;
                return;
            }

            if (orderData.simulated) {
                // Test mode simulation
                setTimeout(function () {
                    verifyPayment({
                        pack_key: pack.key,
                        order_id: orderData.order_id,
                        internal_txn_id: orderData.internal_txn_id || '',
                        payment_id: 'pay_sim_' + Math.floor(Math.random() * 1000000),
                        signature: 'sim_sig',
                        visitor_id: state.visitorId
                    }, btn, origText, pack);
                }, 600);
            } else {
                // Live Razorpay
                openRazorpayCheckout(orderData, pack, btn, origText);
            }
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.textContent = origText;
            alert('Payment initialization failed. Please try again.');
        });
    }

    function recordOrderFailure(orderId, internalTxnId, reason, dropStage) {
        var wpNonce = getWpNonce();
        fetch(restBase + '/fail-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                order_id: orderId || '',
                internal_txn_id: internalTxnId || '',
                reason: reason || 'Payment cancelled or dismissed by user',
                drop_stage: dropStage || (reason && reason.indexOf('dismissed') !== -1 ? 'gateway_modal_dismissed' : ''),
                drop_url: window.location.href
            })
        }).catch(function () {});
    }

    function openRazorpayCheckout(orderData, pack, btn, origText) {
        var options = {
            key: orderData.key_id,
            amount: orderData.amount * 100,
            currency: 'INR',
            name: 'MeraRashifal',
            description: pack.title,
            order_id: orderData.order_id,
            prefill: {
                name: (state.birthData && state.birthData.name) ? state.birthData.name : ''
            },
            handler: function (response) {
                verifyPayment({
                    pack_key: pack.key,
                    order_id: response.razorpay_order_id,
                    internal_txn_id: orderData.internal_txn_id || '',
                    payment_id: response.razorpay_payment_id,
                    signature: response.razorpay_signature,
                    visitor_id: state.visitorId
                }, btn, origText, pack);
            },
            modal: {
                ondismiss: function () {
                    btn.disabled = false;
                    btn.textContent = origText;
                    recordOrderFailure(orderData.order_id, orderData.internal_txn_id, 'Payment dismissed by user', 'gateway_modal_dismissed');
                }
            },
            theme: { color: config.theme || '#d97706' }
        };

        var handleRzpInstance = function (rzpInstance) {
            if (rzpInstance.on) {
                rzpInstance.on('payment.failed', function (resp) {
                    var reason = (resp && resp.error && resp.error.description) ? resp.error.description : 'Payment failed at gateway';
                    recordOrderFailure(orderData.order_id, orderData.internal_txn_id, reason);
                });
            }
            rzpInstance.open();
        };

        if (window.Razorpay) {
            var rzp = new window.Razorpay(options);
            handleRzpInstance(rzp);
        } else {
            var script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = function () {
                var rzp = new window.Razorpay(options);
                handleRzpInstance(rzp);
            };
            document.body.appendChild(script);
        }
    }

    function verifyPayment(payload, btn, origText, pack) {
        var wpNonce = getWpNonce();
        var wpUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? window.niyatiConfig.user_id : 0;
        payload.user_id = wpUserId;

        fetch(restBase + '/verify-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.textContent = origText;

            if (data.success) {
                state.paidCredits = data.new_balance;
                if (data.validity) {
                    state.validity = data.validity;
                }
                updateQuotaUI();
                closeRechargeSheet();

                // Append success banner into chat
                var successDiv = document.createElement('div');
                successDiv.className = 'niyati-recharge-success-banner';
                if (state.lang === 'hi') {
                    successDiv.innerHTML = `🎉 <strong>रिचार्ज सफल (Recharge Successful!)</strong><br>₹${pack.offer_price} के भुगतान पर आपके वॉलेट में <strong>${data.credits_added} चैट क्रेडिट्स</strong> जोड़ दिए गए हैं। कुल बैलेंस: <strong>${data.new_balance} क्रेडिट्स</strong>।`;
                } else if (state.lang === 'hinglish') {
                    successDiv.innerHTML = `🎉 <strong>Recharge Successful!</strong><br>₹${pack.offer_price} payment par aapke wallet me <strong>${data.credits_added} Chat Credits</strong> add kar diye gaye hain. Total Balance: <strong>${data.new_balance} Credits</strong>.`;
                } else {
                    successDiv.innerHTML = `🎉 <strong>Recharge Successful!</strong><br>Payment of ₹${pack.offer_price} received. <strong>${data.credits_added} Chat Credits</strong> have been added to your wallet. Total Balance: <strong>${data.new_balance} Credits</strong>.`;
                }
                el.chatList.appendChild(successDiv);
                scrollToBottom();
            } else {
                alert(data.message || 'Payment verification failed.');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = origText;
            alert('Error verifying payment.');
        });
    }

    function fetchCities(query) {
        fetch(restBase + '/cities?q=' + encodeURIComponent(query))
            .then(function (res) { return res.json(); })
            .then(function (cities) {
                if (!cities || !cities.length) {
                    el.pobResults.style.display = 'none';
                    return;
                }
                el.pobResults.innerHTML = '';
                cities.forEach(function (c) {
                    var div = document.createElement('div');
                    div.className = 'niyati-city-item';
                    div.textContent = c.name;
                    div.addEventListener('click', function () {
                        el.inputPob.value = c.name;
                        el.inputLat.value = c.lat;
                        el.inputLon.value = c.lon;
                        el.inputTz.value = c.tz || 5.5;
                        el.pobResults.style.display = 'none';
                    });
                    el.pobResults.appendChild(div);
                });
                el.pobResults.style.display = 'block';
            })
            .catch(function () {
                el.pobResults.style.display = 'none';
            });
    }

    function handleBirthFormSubmit() {
        if (!state.isInitialized) {
            showInitializingToast();
            return;
        }
        if (state.isComputing) return;

        var name = el.inputName.value.trim();
        var dob = el.inputDob.value;
        var tob = el.inputTob.value;
        var pob = el.inputPob.value.trim();
        var lat = el.inputLat.value;
        var lon = el.inputLon.value;
        var tz = el.inputTz.value;
        var lang = el.inputLang.value;

        // Guard against unauthenticated users: preserve input data and open login modal
        if (!state.isLoggedIn) {
            var tempPayload = {
                name: name,
                dob: dob,
                tob: tob,
                pob: pob,
                lat: lat,
                lon: lon,
                tz: tz,
                lang: lang
            };
            try {
                localStorage.setItem('niyati_temp_birth_data', JSON.stringify(tempPayload));
            } catch (e) {}

            showLoginModal();
            return;
        }

        var t = i18n[lang] || i18n['en'];

        state.isComputing = true;
        el.btnCompute.disabled = true;
        el.btnCompute.textContent = t.btnComputing || '⏳ Calculating planetary positions...';

        var payload = {
            name: name,
            dob: dob,
            tob: tob,
            pob: pob,
            lat: lat,
            lon: lon,
            tz: tz,
            lang: lang,
            time_greeting: getTimeOfDayGreeting('en')
        };

        var wpNonce = getWpNonce();

        fetch(restBase + '/compute-chart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
        .then(function (res) {
            var freshNonce = res.headers ? res.headers.get('X-WP-Nonce') : null;
            if (freshNonce) {
                state.nonce = freshNonce;
                if (window.niyatiConfig) window.niyatiConfig.nonce = freshNonce;
            }
            return res.json();
        })
        .then(function (data) {
            state.isComputing = false;
            el.btnCompute.disabled = false;
            el.btnCompute.textContent = t.btnCompute || '✨ Analyze Chart & Start Chat';

            if (data.code === 'login_required' || (data.data && data.data.status === 401)) {
                state.isLoggedIn = false;
                updateLoginNoticeUI();
                showLoginModal();
                return;
            }

            if (data.success) {
                var now = Date.now();
                state.chartToken = data.chart_token;
                state.birthData = payload;
                state.lang = lang;
                localStorage.setItem('niyati_chart_token', state.chartToken);
                localStorage.setItem('niyati_birth_data', JSON.stringify(payload));
                localStorage.setItem('niyati_lang', lang);
                localStorage.setItem('niyati_last_active_time', now.toString());
                localStorage.setItem('niyati_last_greeting_time', now.toString());
                localStorage.setItem('niyati_last_greeting_date', new Date().toDateString());
                try {
                    localStorage.removeItem('niyati_temp_birth_data');
                } catch (e) {}

                // Add to saved profiles list if new
                if (data.profile) {
                    var exists = false;
                    for (var i = 0; i < state.savedProfiles.length; i++) {
                        var sp = state.savedProfiles[i];
                        if (sp.name && sp.name.toLowerCase() === (data.profile.name || '').toLowerCase() && sp.dob === data.profile.dob) {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists) {
                        state.savedProfiles.push(data.profile);
                    }
                }

                // Clear prior history and show initial greeting
                state.history = [{ role: 'model', text: data.greeting }];
                localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));

                showView('chat');
                renderStoredHistory();

                if (state.pendingReportContext) {
                    var prc = state.pendingReportContext;
                    state.pendingReportContext = null;
                    applyReportContext(prc.slug, prc.title, prc.question);
                }
            } else {
                alert(data.message || (t.serverError || 'Error calculating chart. Please check details.'));
            }
        })
        .catch(function (err) {
            state.isComputing = false;
            el.btnCompute.disabled = false;
            el.btnCompute.textContent = t.btnCompute || '✨ Analyze Chart & Start Chat';
            alert(t.serverError || 'Unable to connect to the server. Please try again.');
        });
    }

    function getTimeOfDayGreeting(lang) {
        var hour = new Date().getHours();
        if (lang === 'hi') {
            if (hour >= 4 && hour < 12) return 'शुभ प्रभात';
            if (hour >= 12 && hour < 17) return 'शुभ दोपहर';
            return 'शुभ संध्या';
        }
        // English default
        if (hour >= 4 && hour < 12) {
            return 'Good morning';
        } else if (hour >= 12 && hour < 17) {
            return 'Good afternoon';
        } else {
            return 'Good evening';
        }
    }

    function checkAndApplyReturnGreeting() {
        if (!state.chartToken || !state.birthData || !state.history || !state.history.length) {
            return;
        }

        var now = Date.now();
        var todayDate = new Date().toDateString();

        var lastActiveTime = parseInt(localStorage.getItem('niyati_last_active_time') || '0', 10);
        var lastGreetingDate = localStorage.getItem('niyati_last_greeting_date') || '';

        // If never set before, initialize now and do not append duplicate greeting
        if (!lastActiveTime) {
            localStorage.setItem('niyati_last_active_time', now.toString());
            localStorage.setItem('niyati_last_greeting_time', now.toString());
            localStorage.setItem('niyati_last_greeting_date', todayDate);
            return;
        }

        var diffMs = now - lastActiveTime;
        var threeHoursMs = 3 * 60 * 60 * 1000; // 3 hours in milliseconds
        var hasPassedThreeHours = (diffMs >= threeHoursMs);
        var isNewDay = (todayDate !== lastGreetingDate);

        // Rule: welcome & greeting message only once a day OR if user has rejoined after 3 hours
        if (!hasPassedThreeHours && !isNewDay) {
            // Active within the last 3 hours on the same day -> Do not repeat greeting
            localStorage.setItem('niyati_last_active_time', now.toString());
            return;
        }

        // Avoid adding consecutive return greetings if the very last message was already a return greeting
        var lastMsg = state.history[state.history.length - 1];
        if (lastMsg && lastMsg.role === 'model' && lastMsg.is_return_greeting) {
            localStorage.setItem('niyati_last_active_time', now.toString());
            return;
        }

        var name = (state.birthData && state.birthData.name) ? state.birthData.name.trim() : '';
        var displayName = name || (state.lang === 'hi' ? 'प्रिय' : 'Friend');

        var timeGreetingEn = getTimeOfDayGreeting('en');
        var returnGreeting = '';

        if (state.lang === 'hi') {
            returnGreeting = `नमस्ते **${displayName} जी**! आपका पुनः स्वागत है। मैं आपकी जन्म कुंडली के आधार पर आपका मार्गदर्शन करने के लिए उपस्थित हूँ। आज आप किस विषय (करियर, विवाह, वित्त या स्वास्थ्य) पर चर्चा करना चाहते हैं?`;
        } else if (state.lang === 'hinglish') {
            returnGreeting = `Namaste **${displayName} ji**! Welcome back. Main aapki kundli guidance ke liye yahan hoon. Aaj aap kis topic (career, marriage, finance ya health) ke baare mein discuss karna chahte hain?`;
        } else {
            returnGreeting = `${timeGreetingEn}, **${displayName}**! Welcome back. How may the stars guide your journey today? Feel free to ask about your career, marriage, finances, or anything on your mind.`;
        }

        // Update tracking timestamps
        localStorage.setItem('niyati_last_active_time', now.toString());
        localStorage.setItem('niyati_last_greeting_time', now.toString());
        localStorage.setItem('niyati_last_greeting_date', todayDate);

        // Push to history and append bubble
        state.history.push({ role: 'model', text: returnGreeting, is_return_greeting: true });
        localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));
        appendMessageBubble('model', returnGreeting);
        scrollToBottom();
    }

    function renderStoredHistory() {
        el.chatList.innerHTML = '';
        state.history.forEach(function (msg) {
            appendMessageBubble(msg.role, msg.text, { orderBtn: msg.order_btn });
        });
        renderSuggestions();
        scrollToBottom();
    }

    function hasUserStartedChat() {
        if (!state.history || !state.history.length) return false;
        for (var i = 0; i < state.history.length; i++) {
            if (state.history[i] && state.history[i].role === 'user') {
                return true;
            }
        }
        return false;
    }

    function renderSuggestions() {
        if (!el.suggestions) {
            el.suggestions = document.createElement('div');
            el.suggestions.id = 'niyati-suggestions';
            el.suggestions.className = 'niyati-suggestions-wrap';
        }

        var list = state.reportCustomSuggestions || null;

        if (!list) {
            // Once the user has started the chat, starter questions need NOT be shown
            if (hasUserStartedChat()) {
                el.suggestions.style.display = 'none';
                el.suggestions.innerHTML = '';
                return;
            }
            list = state.suggestions[state.lang] || state.suggestions['en'] || state.suggestions['hi'] || [];
        }

        if (!list || !list.length) {
            el.suggestions.style.display = 'none';
            el.suggestions.innerHTML = '';
            return;
        }

        el.suggestions.innerHTML = '';
        el.suggestions.style.display = 'flex';
        list.slice(0, 5).forEach(function (q) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'niyati-chip';
            chip.innerHTML = '<span class="niyati-chip-text">' + escapeHTML(q) + '</span><span class="niyati-chip-arrow">›</span>';
            chip.addEventListener('click', function () {
                if (!state.isInitialized) {
                    showInitializingToast();
                    return;
                }
                el.userInput.value = q;
                sendMessage();
            });
            el.suggestions.appendChild(chip);
        });

        // Always keep suggestions inside the single chat stream window at the end
        if (el.chatList) {
            el.chatList.appendChild(el.suggestions);
        }
    }

    function sendMessage() {
        if (!state.isInitialized) {
            showInitializingToast();
            return;
        }

        // Must be logged in to chat
        if (!state.isLoggedIn) {
            showLoginModal();
            return;
        }

        var text = el.userInput.value.trim();
        if (!text || state.isSending) return;

        // Hide starter questions immediately once chat has started
        state.reportCustomSuggestions = null;
        if (el.suggestions) {
            el.suggestions.style.display = 'none';
            el.suggestions.innerHTML = '';
        }

        var freeRem = (state.quota && typeof state.quota.remaining === 'number') ? state.quota.remaining : 0;
        var paidRem = state.paidCredits || 0;

        // Check if both free and paid credits are 0 (allow free site navigation queries)
        if (freeRem <= 0 && paidRem <= 0) {
            if (!isNavigationQuery(text)) {
                appendLimitExceededCard();
                openRechargeSheet();
                return;
            }
        }

        // Append User Bubble
        appendMessageBubble('user', text);
        state.history.push({ role: 'user', text: text });
        localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));
        localStorage.setItem('niyati_last_active_time', Date.now().toString());
        el.userInput.value = '';

        // Show typing indicator with dynamic message rotation
        showTyping(true, text);
        state.isSending = true;
        el.btnSend.disabled = true;

        var wpNonce = getWpNonce();
        var wpUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? window.niyatiConfig.user_id : 0;

        fetch(restBase + '/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpNonce
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                message: text,
                chart_token: state.chartToken,
                lang: state.lang,
                user_id: wpUserId,
                history: state.history.slice(-6)
            })
        })
        .then(function (res) {
            var freshNonce = res.headers ? res.headers.get('X-WP-Nonce') : null;
            if (freshNonce) {
                state.nonce = freshNonce;
                if (window.niyatiConfig) window.niyatiConfig.nonce = freshNonce;
            }
            return res.json();
        })
        .then(function (data) {
            showTyping(false);
            state.isSending = false;
            el.btnSend.disabled = false;
            localStorage.setItem('niyati_last_active_time', Date.now().toString());

            if (data.code === 'login_required' || (data.data && data.data.status === 401)) {
                state.isLoggedIn = false;
                updateLoginNoticeUI();
                showLoginModal();
                return;
            }

            if (data.quota) {
                state.quota = data.quota;
            }
            if (typeof data.paid_credits !== 'undefined') {
                state.paidCredits = data.paid_credits;
            }
            updateQuotaUI();

            if (data.quota_exceeded) {
                appendLimitExceededCard(data.reply);
                openRechargeSheet();
                return;
            }

            if (data.success && data.reply) {
                appendMessageBubble('model', data.reply);
                state.history.push({ role: 'model', text: data.reply });
                localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));
            } else {
                var curT = i18n[state.lang] || i18n['en'];
                appendMessageBubble('model', data.reply || curT.serverError);
            }
            scrollToBottom();
        })
        .catch(function () {
            showTyping(false);
            state.isSending = false;
            el.btnSend.disabled = false;
            var curT = i18n[state.lang] || i18n['en'];
            appendMessageBubble('model', curT.netError || 'Internet connection error. Please try again.');
            scrollToBottom();
        });
    }

    function appendMessageBubble(role, text, options) {
        if (!text) return;

        // If user message, render as single bubble
        if (role === 'user') {
            var userDiv = document.createElement('div');
            userDiv.className = 'niyati-msg user';
            var userBubble = document.createElement('div');
            userBubble.className = 'niyati-msg-bubble';
            userBubble.innerHTML = formatMarkdown(text);
            userDiv.appendChild(userBubble);
            el.chatList.appendChild(userDiv);
            scrollToBottom();
            return;
        }

        // For bot/model responses, split multi-paragraph text into clean, digestible chat bubbles
        var rawParagraphs = text.split(/\n{2,}/);
        var paragraphs = [];
        rawParagraphs.forEach(function (p) {
            var trimmed = p.trim();
            if (trimmed) {
                paragraphs.push(trimmed);
            }
        });

        var avatarUrl = state.avatarUrl || (config && config.avatar_url) || '/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg';
        var botName = escapeHTML(config.bot_name || 'Niyati');

        var msgDiv = document.createElement('div');
        msgDiv.className = 'niyati-msg bot';

        // Avatar with Niyati's profile photo
        var avatarWrap = document.createElement('div');
        avatarWrap.className = 'niyati-msg-avatar';
        var avatarImg = document.createElement('img');
        avatarImg.src = avatarUrl;
        avatarImg.alt = botName;
        avatarImg.className = 'niyati-msg-avatar-img';
        avatarWrap.appendChild(avatarImg);
        msgDiv.appendChild(avatarWrap);

        // Body containing author name/title header and the message bubble(s)
        var bodyWrap = document.createElement('div');
        bodyWrap.className = 'niyati-msg-body';

        var authorHeader = document.createElement('div');
        authorHeader.className = 'niyati-msg-author';
        authorHeader.innerHTML = '<span class="niyati-author-name">' + botName + '</span>';
        bodyWrap.appendChild(authorHeader);

        if (paragraphs.length <= 1) {
            var bubble = document.createElement('div');
            bubble.className = 'niyati-msg-bubble';
            bubble.innerHTML = formatMarkdown(text);
            bodyWrap.appendChild(bubble);
        } else {
            paragraphs.forEach(function (para, idx) {
                var bubble = document.createElement('div');
                bubble.className = 'niyati-msg-bubble' + (idx > 0 ? ' niyati-msg-bubble-followup' : '');
                bubble.innerHTML = formatMarkdown(para);
                bodyWrap.appendChild(bubble);
            });
        }

        // Assisted Order Dossier CTA card
        if (options && options.orderBtn) {
            var ob = options.orderBtn;
            var orderCard = document.createElement('div');
            orderCard.className = 'niyati-assisted-order-card';
            orderCard.style.cssText = 'margin-top:10px; padding:12px 14px; background:#FFF7ED; border:1px solid #FDBA74; border-radius:14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;';
            orderCard.innerHTML = '<div style="font-size:13px; font-weight:600; color:#7A1F3D;">' + escapeHTML(ob.label || 'Looking for the complete written dossier?') + '</div>' +
                '<button type="button" class="niyati-order-action-btn" style="background:#F58220; color:#fff; border:none; padding:8px 16px; border-radius:18px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 3px 10px rgba(245,130,32,0.35);">' + escapeHTML(ob.btnText || 'Order Full Dossier →') + '</button>';
            
            orderCard.querySelector('.niyati-order-action-btn').addEventListener('click', function() {
                if (window.openDetailsModal) {
                    window.openDetailsModal(ob.slug, ob.title);
                } else {
                    var b = document.querySelector('.mr-btn-buy-alacarte');
                    if (b) b.click();
                }
            });
            bodyWrap.appendChild(orderCard);
        }

        msgDiv.appendChild(bodyWrap);
        el.chatList.appendChild(msgDiv);

        // If suggestions are currently active, keep them at the end of the stream
        if (el.suggestions && el.suggestions.style.display !== 'none' && el.suggestions.parentNode === el.chatList) {
            el.chatList.appendChild(el.suggestions);
        }

        scrollToBottom();
    }

    function appendLimitExceededCard(customMsg) {
        var t = i18n[state.lang] || i18n['en'];
        var div = document.createElement('div');
        div.className = 'niyati-limit-box';
        var msg = customMsg || t.limitReachedMsg;
        div.innerHTML = `
            <strong>${t.limitReachedTitle}</strong>
            <p>${escapeHTML(msg)}</p>
            <button id="niyati-btn-trigger-recharge" class="niyati-btn-submit" style="margin-top:8px; padding:8px 16px; font-size:12px; width:auto;">
                ${t.btnViewPacks}
            </button>
        `;
        div.querySelector('#niyati-btn-trigger-recharge').addEventListener('click', openRechargeSheet);
        el.chatList.appendChild(div);
        scrollToBottom();
    }

    function isNavigationQuery(text) {
        if (!text) return false;
        var lower = text.toLowerCase();
        var navKeywords = [
            'kundali report', 'kundli report', 'birth chart report', 'janam patri',
            'matching', 'matchmaking', 'gun milan', 'kundali milan', 'kundli milan', 'compatibility',
            'dosh', 'mangal dosh', 'kaal sarp', 'pitra dosh', 'manglik',
            'dasha', 'mahadasha', 'antardasha',
            'panchang', 'choghadiya', 'muhurat', 'rahukaal', 'abhijit',
            'baby name', 'baby names', 'naam', 'boy name', 'girl name',
            'palm reading', 'hastrekha', 'remedies', 'upay',
            'pricing', 'recharge', 'credits', 'pack', 'passbook', 'account',
            'sitemap', 'site directory', 'website', 'pages', 'reports'
        ];
        var actionWords = [
            'link', 'page', 'url', 'site', 'kahan', 'kaise', 'where', 'how', 'download',
            'pdf', 'button', 'view', 'dekhein', 'dekhna', 'bhejo', 'batao', 'chahiye',
            'generate', 'create', 'open', 'show', 'list', 'aaj', 'today', 'options', 'search'
        ];

        if (/kundali report|kundli report|birth chart report|gun milan|matchmaking|panchang|muhurat|pricing|recharge|passbook|sitemap/i.test(lower)) {
            return true;
        }

        for (var i = 0; i < navKeywords.length; i++) {
            var kw = navKeywords[i];
            if (lower.indexOf(kw) !== -1) {
                for (var j = 0; j < actionWords.length; j++) {
                    if (lower.indexOf(actionWords[j]) !== -1) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    var typingTimer = null;
    var currentTypingIndex = 0;

    function showTyping(show, userText) {
        var existing = document.getElementById('niyati-typing-indicator');

        if (typingTimer) {
            clearInterval(typingTimer);
            typingTimer = null;
        }

        if (show) {
            if (existing) {
                existing.remove();
            }

            var lang = state.lang || 'en';
            var isNav = isNavigationQuery(userText || '');
            var msgPool = [];

            if (isNav) {
                if (lang === 'hi') {
                    msgPool = [
                        '🔍 MeraRashifal.com से आपके लिए विकल्प खोजे जा रहे हैं...',
                        '📑 MeraRashifal.com से सही रिपोर्ट और टूल ढूंढे जा रहे हैं...',
                        '🧭 MeraRashifal.com की आधिकारिक सेवाओं से लिंक प्राप्त हो रहे हैं...',
                        '✨ आपके लिए सही पेज ढूंढा जा रहा है...'
                    ];
                } else if (lang === 'hinglish') {
                    msgPool = [
                        '🔍 MeraRashifal.com se aapke liye options search ho rahe hain...',
                        '📑 MeraRashifal.com se best report dhoondh rahe hain...',
                        '🧭 MeraRashifal.com ke tools aur pages fetch kiye ja rahe hain...',
                        '✨ Exact page link generate ho raha hai...'
                    ];
                } else {
                    msgPool = [
                        '🔍 Searching options from MeraRashifal.com...',
                        '📑 Finding the best report on MeraRashifal.com...',
                        '🧭 Fetching official tools & pages from MeraRashifal.com...',
                        '✨ Locating the exact page on MeraRashifal.com...'
                    ];
                }
            } else {
                if (lang === 'hi') {
                    msgPool = [
                        '✨ नियति आपकी जन्मकुंडली का गहन अध्ययन कर रही हैं...',
                        '🌟 आपके ग्रहों की शुभ स्थिति और गोचर का अवलोकन हो रहा है...',
                        '🪐 आपकी वर्तमान महादशा और नक्षत्रों की गणना जारी है...',
                        '🔮 आपके लिए सकारात्मक वैदिक मार्गदर्शन तैयार हो रहा है...',
                        '☀️ भावों के बल और ग्रहों के प्रभावों की गणना हो रही है...',
                        '💫 आपके लिए शुभ सात्विक उपाय तैयार किए जा रहे हैं...'
                    ];
                } else if (lang === 'hinglish') {
                    msgPool = [
                        '✨ Niyati aapki authentic birth chart analyze kar rahi hain...',
                        '🌟 Planetary alignments aur yogas check ho rahe hain...',
                        '🪐 Active Dasha aur Nakshatra positions calculate ho rahi hain...',
                        '🔮 Aapke liye positive Vedic guidance ready ho rahi hai...',
                        '☀️ Planetary strengths aur house influences calculate ho rahe hain...',
                        '💫 Accurate prediction aur Sattvic remedies prepare ho rahi hain...'
                    ];
                } else {
                    msgPool = [
                        '✨ Niyati is analyzing your authentic birth chart...',
                        '🌟 Connecting with your planetary alignments...',
                        '🪐 Reading active Dashas & Nakshatra transits...',
                        '🔮 Unlocking positive cosmic opportunities for you...',
                        '☀️ Calculating planetary strengths & house influences...',
                        '💫 Channeling pure Vedic wisdom & Sattvic remedies...'
                    ];
                }
            }

            currentTypingIndex = 0;
            var avatarUrl = state.avatarUrl || (config && config.avatar_url) || '/wp-content/plugins/niyati-ai-astrology-chat/assets/images/niyati-avatar.jpg';
            var div = document.createElement('div');
            div.id = 'niyati-typing-indicator';
            div.className = 'niyati-typing';
            div.innerHTML = `
                <div class="niyati-msg-avatar" style="width:26px; height:26px; min-width:26px; margin:0;">
                    <img src="${avatarUrl}" alt="Niyati" class="niyati-msg-avatar-img">
                </div>
                <span class="niyati-typing-text">${escapeHTML(msgPool[0])}</span>
                <span class="niyati-typing-dots"><span></span><span></span><span></span></span>
            `;
            el.chatList.appendChild(div);
            scrollToBottom();

            // Rotate message every 2200ms with a smooth fade
            if (msgPool.length > 1) {
                typingTimer = setInterval(function () {
                    var textEl = div.querySelector('.niyati-typing-text');
                    if (!textEl) return;
                    currentTypingIndex = (currentTypingIndex + 1) % msgPool.length;
                    textEl.style.opacity = '0';
                    setTimeout(function () {
                        if (textEl) {
                            textEl.textContent = msgPool[currentTypingIndex];
                            textEl.style.opacity = '1';
                        }
                    }, 200);
                }, 2200);
            }
        } else {
            if (existing) {
                existing.remove();
            }
        }
    }

    function resetKundli() {
        var wpNonce = getWpNonce();
        var wpUserId = (window.niyatiConfig && window.niyatiConfig.user_id) ? window.niyatiConfig.user_id : 0;

        // Archive active chat session on server disk (.json.gz) in background
        if (wpUserId) {
            fetch(restBase + '/reset-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpNonce
                },
                credentials: 'same-origin',
                body: JSON.stringify({ user_id: wpUserId })
            }).catch(function () {});
        }

        clearUserSessionData();
        showView('onboarding');
    }

    function scrollToBottom() {
        setTimeout(function () {
            el.chatList.scrollTop = el.chatList.scrollHeight;
        }, 60);
    }

    var URL_TITLE_MAP = {
        'kundali-matching-detailed': 'Detailed Kundali Matching',
        'kundali-lagan-matching': 'Detailed Kundali Matching',
        'matchmaking-compatibility-check': 'Kundali Matchmaking',
        'kundali-details-report': 'Detailed Kundali Report',
        'kundali-details': 'Janam Kundali Report',
        'create-chart': 'Create Birth Chart',
        'compatibility-check': 'Sun Sign Compatibility',
        'mangal-dosh': 'Mangal Dosh Check',
        'kaal-sarp-dosh': 'Kaal Sarp Dosh Check',
        'pitra-dosh': 'Pitra Dosh Check',
        'dosh': 'Vedic Dosh Overview',
        'current-mahadasha-full': 'Mahadasha Full Report',
        'current-mahadasha': 'Current Mahadasha Check',
        'maha-dasha-predictions': 'Mahadasha Predictions',
        'antar-dasha': 'Antardasha Predictions',
        'dasha': 'Kundali Dasha',
        'yearly-horoscope-prediction': 'Yearly Horoscope Prediction',
        'horoscope': 'Daily Horoscope',
        'panchang/abhijit-muhurat': 'Abhijit Muhurat Today',
        'panchang/rahukaal': 'Rahukaal Timings Today',
        'panchang': 'Daily Hindu Panchang',
        'daily-choghadiya-muhurat': 'Daily Choghadiya Muhurat',
        'muhurat-dates': 'Shubh Muhurat Dates',
        'festivals': 'Hindu Festivals Calendar',
        'baby-names/boy-names': 'Baby Boy Names',
        'baby-names/girl-names': 'Baby Girl Names',
        'baby-names': 'Baby Names Directory',
        'name-suitability': 'Name Suitability Calculator',
        'palm-reading': 'Palm Reading Consultation',
        'remedies-astrological': 'Vedic Remedies & Upay',
        'passbook': 'My Wallet Passbook',
        'my-account': 'My Account'
    };

    function getCleanButtonTitle(url, rawLabel) {
        var label = (rawLabel || '').trim();
        // Remove leading emojis, bullets, arrow pointers like 👉, ✨, 🔗, 📜, 🔍, 📊, >, -, •
        label = label.replace(/^[\s👉✨🔗📜🔍📊\-\>•\]\)]+/g, '').replace(/[\s\[\(\<\>]+$/g, '').trim();

        // Check if label itself is a raw URL or empty
        var isUrlAsLabel = !label || /^https?:\/\//i.test(label) || /^www\./i.test(label) || label.indexOf('localhost') !== -1 || label.indexOf('merarashifal') !== -1;

        if (isUrlAsLabel) {
            var lowerUrl = (url || '').toLowerCase();
            for (var slug in URL_TITLE_MAP) {
                if (lowerUrl.indexOf(slug.toLowerCase()) !== -1) {
                    return URL_TITLE_MAP[slug];
                }
            }
            return 'View Details';
        }

        // Clean out any stray markdown brackets from corrupted LLM outputs
        label = label.replace(/[\[\]\(\)]+/g, '').replace(/\s+/g, ' ').trim();
        return label;
    }

    function formatMarkdown(text) {
        if (!text) return '';

        // Clean any double-nested markdown link corruptions: e.g. [A [B](url1) C](url2)
        text = text.replace(/\[([^\]]*?)\[([^\]]+)\]\((https?:\/\/[^\s\)\"\']+)\)([^\]]*?)\]\((https?:\/\/[^\s\)\"\']+)\)/gi, function(match, p1, p2, u1, p3, u2) {
            var clean = (p1 + ' ' + p2 + ' ' + p3).replace(/\s+/g, ' ').trim();
            return '[' + clean + '](' + (u2 || u1) + ')';
        });

        var links = [];

        // 1. Extract existing HTML links <a ...>Label</a>
        var processed = text.replace(/<a\s+(?:[^>]*?\s+)?href=["'](https?:\/\/[^"']+)["'][^>]*>(.*?)<\/a>/gi, function(match, url, label) {
            var token = '___NIYATI_LINK_' + links.length + '___';
            var cleanLabel = getCleanButtonTitle(url, label.replace(/<[^>]*>/g, ''));
            links.push({ label: cleanLabel, url: url, isButton: true });
            return token;
        });

        // 2. Extract markdown links [Label](URL) with optional leading pointer emojis
        processed = processed.replace(/(?:👉|✨|🔗|🔍)?[ \t]*\[([^\]]+)\][ \t]*\((https?:\/\/[^\s\)\"\']+)\)/gi, function(match, label, url) {
            var token = '___NIYATI_LINK_' + links.length + '___';
            var cleanLabel = getCleanButtonTitle(url, label);
            links.push({ label: cleanLabel, url: url, isButton: true });
            return token;
        });

        // 3. Extract bare URLs (https://... or http://...) that are not already tokenized
        // Also handle cases where a bare URL is wrapped in brackets/parentheses e.g. (http://...) or [http://...]
        processed = processed.replace(/(?:\[|\()?\s*(https?:\/\/[^\s\<\>\"\'\)]+)\s*(?:\]|\))?/gi, function(match, url) {
            if (url.indexOf('___NIYATI_LINK_') !== -1) return url;
            var cleanUrl = url.replace(/[.,:;!?]+$/, '');
            var isInternal = cleanUrl.indexOf('localhost') !== -1 || cleanUrl.indexOf('merarashifal') !== -1;
            var cleanLabel = isInternal ? getCleanButtonTitle(cleanUrl, '') : cleanUrl;
            var token = '___NIYATI_LINK_' + links.length + '___';
            links.push({ label: cleanLabel, url: cleanUrl, isButton: isInternal });
            return token;
        });

        // Clean any leftover brackets/parentheses around replaced tokens
        processed = processed.replace(/\[\s*(___NIYATI_LINK_\d+___)\s*\]/g, '$1')
                             .replace(/\(\s*(___NIYATI_LINK_\d+___)\s*\)/g, '$1');

        // 4. Escape HTML for security against XSS
        var escaped = escapeHTML(processed);

        // 5. Restore links as valid clickable buttons / <a> tags
        links.forEach(function(item, idx) {
            var token = '___NIYATI_LINK_' + idx + '___';
            var safeLabel = escapeHTML(item.label);
            var safeUrl = item.url.replace(/"/g, '&quot;');
            var isAction = item.isButton || /👉|✨|📜|🔍|📊|🔗|view|check|open|dekhein|dekh|explore|report|kundali|matchmaking|panchang|muhurat|horoscope|calculator|remedies/i.test(item.label);
            var btnClass = isAction ? 'niyati-auto-link niyati-action-btn' : 'niyati-auto-link';
            var html = '<a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer" class="' + btnClass + '">' + safeLabel + '<span class="niyati-link-icon">↗</span></a>';
            escaped = escaped.split(token).join(html);
        });

        // 6. Bold: **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 7. Auto-Highlight Vedic entities (Planets, Deities, Days, Mantras, Gemstones, Months, Festivals)
        escaped = highlightVedicTerms(escaped);

        // 8. Bullet points: * or -
        escaped = escaped.replace(/(?:^|\n)[*-]\s+(.+)/g, '<br>• $1');
        // 9. Line breaks
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }

    function highlightVedicTerms(html) {
        if (!html) return '';

        // 1. Temporarily protect existing <strong>...</strong> and <a ...>...</a> blocks
        var preserved = [];
        var text = html.replace(/<(strong|a)\b[^>]*>.*?<\/\1>/gi, function(match) {
            var token = '___NIYATI_PRESERVED_' + preserved.length + '___';
            preserved.push(match);
            return token;
        });

        // 2. Protect other HTML tags (<br>, <span>, etc.)
        var tags = [];
        text = text.replace(/<[^>]+>/g, function(tag) {
            var token = '___NIYATI_TAG_' + tags.length + '___';
            tags.push(tag);
            return token;
        });

        // Token bucket for words to bold
        var boldItems = [];
        function toBold(str) {
            var token = '___NIYATI_BOLD_' + boldItems.length + '___';
            boldItems.push(str);
            return token;
        }

        function replaceTerms(patternStr) {
            try {
                var re = new RegExp('(?<![\\p{L}\\p{N}])(' + patternStr + ')(?![\\p{L}\\p{N}])', 'giu');
                text = text.replace(re, function(match) {
                    return toBold(match);
                });
            } catch (e) {
                var fbRe = new RegExp('\\b(' + patternStr + ')\\b', 'gi');
                text = text.replace(fbRe, function(match) {
                    return toBold(match);
                });
            }
        }

        // 3. Highlight Named Mantras & Sacred Invocations (longer phrases first)
        var mantras = 'Aditya Hridaya Stotra|Vishnu Sahasranama|Durga Saptashati|Mahamrityunjaya Mantra|Om Namah Shivaya|Gayatri Mantra|Hanuman Chalisa|Navarna Mantra|Om Hanumate Namah|Om Namo Bhagavate Vasudevaya|Om Sham Shanaicharaya Namah|Om Dum Durgayei Namah|Om Gam Ganapataye Namah|ॐ नमः शिवाय|गायत्री मंत्र|महामृत्युंजय मंत्र|हनुमान चालीसा|आदित्य हृदय स्तोत्र|विष्णु सहस्त्रनाम|दुर्गा सप्तशती|ॐ शं शनैश्चराय नमः|ॐ नमो भगवते वासुदेवाय|ॐ हनुमते नमः|ॐ गं गणपतये नमः';
        replaceTerms(mantras);

        // 4. Highlight Mantras in quotes: "Om ...", 'Om ...', "ॐ ...", etc.
        text = text.replace(/(["'“‘])(Om\s+[^"'”’<]+|ॐ\s+[^"'”’<]+)(["'”’])/gi, function(match) {
            return toBold(match);
        });

        // 5. Highlight Deities / Cosmic Powers / Sacred Symbols (longer phrases first)
        var deities = 'Shiva Lingam|Shiv Lingam|Shivling|Lord Shiva|Shiva|Mahadev|Bholenath|Shankar|Maa Parvati|Parvati|Maa Durga|Durga|Maa Lakshmi|Lakshmi|Maa Saraswati|Saraswati|Lord Ganesha|Ganesha|Ganpati|Lord Hanuman|Hanuman Ji|Hanuman|Bajrangbali|Lord Vishnu|Vishnu|Lord Krishna|Krishna|Lord Rama|Rama|Kartikeya|Kaal Bhairav|Bhairav|Surya Dev|Suryadev|Rudraksha|Shree Yantra|Peepal tree|Pipal tree|Tulsi plant|Tulsi|Belpatra|Bilva Patra|शिवलिंग|शिव लिंग|भगवान शिव|शिवजी|भोलेनाथ|महादेव|माता पार्वती|माँ दुर्गा|माँ लक्ष्मी|लक्ष्मी जी|भगवान गणेश|गणेशजी|गणपति|भगवान हनुमान|हनुमान जी|हनुमान|भगवान विष्णु|विष्णु जी|श्रीकृष्ण|भगवान राम|पीपल|तुलसी|बेलपत्र';
        replaceTerms(deities);

        // 6. Highlight Festivals & Fasting Days (Utsav & Vrat)
        var festivals = 'Maha Shivratri|Shivratri|Sawan Shivratri|Krishna Janmashtami|Janmashtami|Ganesh Chaturthi|Vinayaka Chaturthi|Chaitra Navratri|Sharad Navratri|Navratri|Durga Puja|Vijayadashami|Dussehra|Diwali|Deepavali|Dhanteras|Bhai Dooj|Govardhan Puja|Holi|Holika Dahan|Chhath Puja|Chhath|Makar Sankranti|Raksha Bandhan|Karwa Chauth|Ahoi Ashtami|Ram Navami|Hanuman Jayanti|Guru Purnima|Akshaya Tritiya|Dev Uthani Ekadashi|Nirjala Ekadashi|Ekadashi|Pradosh Vrat|Pradosh|Somwar Vrat|Somvati Amavasya|Sharad Purnima|महाशिवरात्रि|शिवरात्रि|सावन शिवरात्रि|जन्माष्टमी|कृष्ण जन्माष्टमी|गणेश चतुर्थी|विनायक चतुर्थी|चैत्र नवरात्रि|शारदीय नवरात्रि|नवरात्रि|दुर्गा पूजा|दशहरा|विजयादशमी|दीपावली|दिवाली|धनतेरस|भाई दूज|गोवर्धन पूजा|होली|होलिका दहन|छठ पूजा|छठ|मकर संक्रांति|रक्षाबंधन|करवा चौथ|अहोई अष्टमी|रामनवमी|हनुमान जयंती|गुरु पूर्णिमा|अक्षय तृतीया|देवउठनी एकादशी|निर्जला एकादशी|एकादशी|प्रदोष व्रत|प्रदोष|सोमवार व्रत|सोमवती अमावस्या|अमावस्या|शरद पूर्णिमा|पूर्णिमा';
        replaceTerms(festivals);

        // 7. Highlight Events, Muhurats & Life Milestones
        var events = 'Abhijit Muhurat|Amrit Kaal|Brahma Muhurat|Shubh Muhurat|Muhurat|Solar Eclipse|Surya Grahan|Lunar Eclipse|Chandra Grahan|Guru Gochar|Shani Gochar|Rahu Gochar|Ketu Gochar|Gochar|Transit|Griha Pravesh|Housewarming|Bhoomi Pujan|Namkaran|Mundan|Upanayana|Janeu|Vivah|Marriage|Shaadi|Sagai|Engagement|Roka|अभिजीत मुहूर्त|अमृत काल|ब्रह्म मुहूर्त|शुभ मुहूर्त|मुहूर्त|सूर्य ग्रहण|चंद्र ग्रहण|गुरु गोचर|शनि गोचर|राहु गोचर|केतु गोचर|गोचर|गृह प्रवेश|भूमि पूजन|नामकरण|मुंडन|उपनयन|जनेऊ|विवाह|शादी|सगाई|रोका';
        replaceTerms(events);

        // 8. Highlight Gemstones (Ratnas)
        var gemstones = 'Yellow Sapphire|Blue Sapphire|White Sapphire|Red Coral|Cat\'s Eye|Hessonite Garnet|Hessonite|Ruby|Emerald|Diamond|Pearl|Opal|Turquoise|Amethyst|Moonstone|Pukhraj|Neelam|Manikya|Manik|Moti|Moonga|Panna|Heera|Gomed|Gomedh|Lehsuniya|Firoza|Jamunia|पुखराज|नीलम|माणिक्य|माणिक|मोती|मूंगा|पन्ना|हीरा|गोमेद|लहसुनिया|फिरोजा|जमुनिया';
        replaceTerms(gemstones);

        // 9. Highlight Days of Week (including morning/evening combinations)
        var days = 'Monday mornings?|Tuesday mornings?|Wednesday mornings?|Thursday mornings?|Friday mornings?|Saturday mornings?|Sunday mornings?|Monday evenings?|Tuesday evenings?|Wednesday evenings?|Thursday evenings?|Friday evenings?|Saturday evenings?|Sunday evenings?|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Somwar|Mangalwar|Budhwar|Guruwar|Brihaspatiwar|Shukrawar|Shaniwar|Raviwar|सोमवार|मंगलवार|बुधवार|गुरुवार|बृहस्पतिवार|शुक्रवार|शनिवार|रविवार';
        replaceTerms(days);

        // 10. Highlight Months (Gregorian & Vedic Lunar, with optional year: "January 2027", "कार्तिक 2026", etc.)
        var monthNames = 'January|February|March|April|May|June|July|August|September|October|November|December|Chaitra|Vaishakha|Jyeshtha|Ashadha|Shravana|Bhadrapada|Ashwin|Kartik|Margashirsha|Pausha|Phalguna|Sawan|Bhado|Aghan|Poos|जनवरी|फ़रवरी|फरवरी|मार्च|अप्रैल|मई|जून|जुलाई|अगस्त|सितंबर|अक्टूबर|नवंबर|दिसंबर|चैत्र|वैशाख|ज्येष्ठ|आषाढ़|श्रावण|सावन|भाद्रपद|भादो|अश्विन|कार्तिक|मार्गशीर्ष|पौष|माघ|फाल्गुन';
        try {
            var mRe = new RegExp('(?<![\\p{L}\\p{N}])((' + monthNames + ')(\\s+\\d{4})?)(?![\\p{L}\\p{N}])', 'giu');
            text = text.replace(mRe, function(match) {
                return toBold(match);
            });
        } catch(e) {
            var fbMRe = new RegExp('\\b((' + monthNames + ')(\\s+\\d{4})?)\\b', 'gi');
            text = text.replace(fbMRe, function(match) {
                return toBold(match);
            });
        }

        // 11. Highlight Planetary & Celestial Bodies, Houses, Dashas, Nakshatras (longer phrases first)
        var planets = 'Guru Chandal Dosh|Kendra Trikona Raj Yoga|Gajakesari Yoga|Budhaditya Yoga|Kaal Sarp Dosh|Manglik Dosh|Mangal Dosh|Pitra Dosh|Dhan Yoga|Pratyantardasha|Vimshottari|Mahadasha|Antardasha|Sade Sati|Dhaiya|Ascendant|Sun|Moon|Mars|Mercury|Jupiter|Venus|Saturn|Rahu|Ketu|Lagna|Surya|Chandra|Brihaspati|Shukra|Shani Dev|Shanidev|Ashwini|Bharani|Krittika|Rohini|Mrigashira|Ardra|Punarvasu|Pushya|Ashlesha|Magha|Purva Phalguni|Uttara Phalguni|Hasta|Chitra|Swati|Vishakha|Anuradha|Jyeshtha|Mula|Purva Ashadha|Uttara Ashadha|Shravana|Dhanishta|Shatabhisha|Purva Bhadrapada|Uttara Bhadrapada|Revati|सूर्य|चंद्र|चन्द्रमा|मंगल|बुध|बृहस्पति|गुरुदेव|शुक्र|शनिदेव|शनि|राहु|केतु|लग्न|महादशा|अंतर्दशा|साढ़ेसाती|कालसर्प दोष|मांगलिक दोष|पितृ दोष|गजकेसरी योग|अश्विनी|भरणी|रोहिणी|पुष्य|मघा|स्वाति|अनुराधा|श्रवण|रेवती';
        replaceTerms(planets);

        // Also highlight houses: 1st house, 2nd house ... 12th house
        text = text.replace(/\b((?:1st|2nd|3rd|4th|5th|6th|7th|8th|9th|10th|11th|12th)\s+house)\b/gi, function(match) {
            return toBold(match);
        });

        // 12. Restore bold items into <strong>...</strong>
        boldItems.forEach(function(item, idx) {
            text = text.split('___NIYATI_BOLD_' + idx + '___').join('<strong>' + item + '</strong>');
        });

        // 13. Restore tags
        tags.forEach(function(tag, idx) {
            text = text.split('___NIYATI_TAG_' + idx + '___').join(tag);
        });

        // 14. Restore preserved blocks (already strong and a)
        preserved.forEach(function(block, idx) {
            text = text.split('___NIYATI_PRESERVED_' + idx + '___').join(block);
        });

        return text;
    }

    function escapeHTML(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // =========================================================================
    // Report-Context Aware Consultation Engine (MeraRashifal Brand Integration)
    // =========================================================================
    var reportContextData = {
        'yearly-horoscope-report': {
            title: 'Yearly Horoscope Detailed Report',
            welcome: {
                en: 'Namaste **{name}**! I see you are exploring your **Yearly Horoscope Detailed Report**. Curious about how upcoming planetary transits (Jupiter, Saturn, Rahu-Ketu) will shape your career, finances, relationships, and health this year? Ask me below!',
                hi: 'नमस्ते **{name} जी**! मैं देख रही हूँ कि आप **वार्षिक विस्तृत राशिफल रिपोर्ट** का अध्ययन कर रहे हैं। इस वर्ष गुरु, शनि व राहु-केतु के गोचर से आपके करियर, धन और जीवन पर क्या प्रभाव पड़ेगा? नीचे पूछें!',
                hinglish: 'Namaste **{name} ji**! Main dekh rahi hoon aap **Yearly Horoscope Detailed Report** explore kar rahe hain. Is saal Jupiter, Saturn aur Rahu ke transits aapke career aur life ko kaise affect karenge? Poochiye!'
            },
            questions: {
                en: [
                    'How will major planetary transits affect my career this year?',
                    'What are my most auspicious months and caution periods?',
                    'Which classical Vedic remedies should I follow for difficult transits?',
                    'What is covered in the 26-page detailed dossier?'
                ],
                hi: [
                    'इस वर्ष प्रमुख ग्रहों का गोचर मेरे करियर को कैसे प्रभावित करेगा?',
                    'मेरे लिए सबसे शुभ महीने और सावधानी की अवधि कौन सी है?',
                    'कठिन गोचर के लिए कौन से वैदिक उपाय करने चाहिए?',
                    'इस 26 पृष्ठ की विस्तृत रिपोर्ट में क्या-क्या शामिल है?'
                ],
                hinglish: [
                    'Major planetary transits is saal mere career ko kaise affect karenge?',
                    'Mere liye most auspicious months aur caution periods kaun se hain?',
                    'Difficult transits ke liye kaun se Vedic remedies follow karein?',
                    'Is 26-page detailed dossier mein kya-kya covered hai?'
                ]
            }
        },
        'yearly-horoscope-snapshot-report': {
            title: 'Yearly Horoscope Snapshot Report',
            welcome: {
                en: 'Namaste **{name}**! Reviewing the **Yearly Horoscope Snapshot Report**? I can give you a rapid overview of your key quarters, primary planetary trends, and milestone dates. What would you like to check?',
                hi: 'नमस्ते **{name} जी**! **वार्षिक स्नैपशॉट रिपोर्ट** के विषय में क्या जानना चाहते हैं? मैं आपके मुख्य तिमाही रुझानों और महत्वपूर्ण तिथियों का त्वरित विश्लेषण बता सकती हूँ।',
                hinglish: 'Namaste **{name} ji**! **Yearly Snapshot Report** ke baare mein kya poochna chahte hain? Main aapke major quarterly trends aur milestone dates discuss kar sakti hoon.'
            },
            questions: {
                en: [
                    'What is the single biggest planetary influence for me this year?',
                    'Which quarter looks most promising for financial gains?',
                    'How does this snapshot differ from the 26-page detailed report?'
                ],
                hi: [
                    'इस वर्ष मुझ पर सबसे बड़ा ग्रह प्रभाव किसका रहेगा?',
                    'वित्तीय लाभ के लिए कौन सी तिमाही सबसे शुभ है?',
                    'यह स्नैपशॉट विस्तृत 26 पृष्ठ की रिपोर्ट से कैसे अलग है?'
                ],
                hinglish: [
                    'Is saal mere liye sabse bada planetary influence kya hai?',
                    'Financial gains ke liye kaun sa quarter sabse promising hai?',
                    'Yeh snapshot detailed 26-page report se kaise differ karta hai?'
                ]
            }
        },
        'janam-kundali-master-report': {
            title: 'Master Janma Kundali & Harmonics Report',
            welcome: {
                en: 'Namaste **{name}**! Exploring your **Master Janma Kundali & Harmonics Report**? Your birth chart is your cosmic blueprint — from D1 Lagna to D60 Shashtiamsha. Ask me about your core Raj Yogas, planetary strengths, or house placements!',
                hi: 'नमस्ते **{name} जी**! अपनी **जन्म कुंडली एवं वर्ग कुंडलियों (D1 से D60)** के बारे में जानना चाहते हैं? आपकी कुंडली आपके जीवन का दिव्य नक्शा है। अपने राजयोग, ग्रह स्थिति और भावों के विषय में पूछें!',
                hinglish: 'Namaste **{name} ji**! Apni **Master Janma Kundali & Harmonics Report** explore kar rahe hain? Apne core Raj Yogas, planet strengths aur house placements ke baare mein poochein!'
            },
            questions: {
                en: [
                    'What are the strongest Raj Yogas present in my Kundali?',
                    'What does my Navamsha (D9) chart indicate about my marriage & destiny?',
                    'Which planet is the Yogakaraka (most beneficial) for my Ascendant?',
                    'What exhaustive insights are included in this 45-page master dossier?'
                ],
                hi: [
                    'मेरी कुंडली में कौन से शक्तिशाली राजयोग उपस्थित हैं?',
                    'मेरा नवमांश (D9) चार्ट विवाह और भाग्य के बारे में क्या दर्शाता है?',
                    'मेरे लग्न के लिए कौन सा ग्रह योगकारक (सबसे शुभ) है?',
                    'इस 45 पृष्ठ की मास्टर रिपोर्ट में क्या-क्या विस्तार से दिया गया है?'
                ],
                hinglish: [
                    'Meri Kundali mein kaun se strong Raj Yogas hain?',
                    'Navamsha (D9) chart meri marriage aur destiny ke baare mein kya kehta hai?',
                    'Mere lagna ke liye kaun sa planet Yogakaraka hai?',
                    'Is 45-page master dossier mein kya-kya cover hota hai?'
                ]
            }
        },
        'dasha-timing-report': {
            title: 'Master Vedic Dasha Life Timing Report',
            welcome: {
                en: 'Namaste **{name}**! In Vedic astrology, timing is everything. Reviewing the **Master Vedic Dasha Life Timing Report**? Ask me which Mahadasha or Antardasha you are running right now, and when your major turning points or golden phases arrive!',
                hi: 'नमस्ते **{name} जी**! वैदिक ज्योतिष में सही समय का ज्ञान ही सर्वोपरि है। **वैदिक महादशा एवं जीवन काल-चक्र रिपोर्ट** के संदर्भ में, आप वर्तमान में किस महादशा/अंतर्दशा में हैं और आपके जीवन का स्वर्णिम समय कब आएगा, पूछें!',
                hinglish: 'Namaste **{name} ji**! Vedic astrology mein timing hi sabse important hai. Aap abhi kaunsi Mahadasha ya Antardasha mein chal rahe hain aur golden phase kab aayega, poochein!'
            },
            questions: {
                en: [
                    'Which Mahadasha and Antardasha am I currently running?',
                    'When will my current difficult dasha phase end?',
                    'When is the most favorable time for career growth or relocation?',
                    'How does this 24-page dasha timeline guide my critical life decisions?'
                ],
                hi: [
                    'मेरी वर्तमान में कौन सी महादशा और अंतर्दशा चल रही है?',
                    'मेरी कठिन दशा का समय कब समाप्त होगा?',
                    'करियर में बदलाव या पदोन्नति के लिए सबसे अनुकूल समय कब है?',
                    'यह 24 पृष्ठ की दशा टाइमलाइन जीवन के निर्णयों में कैसे सहायक है?'
                ],
                hinglish: [
                    'Meri current Mahadasha aur Antardasha kaunsi chal rahi hai?',
                    'Current challenging dasha phase kab end hoga?',
                    'Career change ya relocation ke liye best favorable window kab hai?',
                    'Yeh 24-page dasha timeline mere decisions mein kaise help karegi?'
                ]
            }
        },
        'kundali-dosh-report': {
            title: 'Master Kundali Dosh & Remedial Audit Report',
            welcome: {
                en: 'Namaste **{name}**! Examining the **Master Kundali Dosh & Remedial Audit Report**? Concerned about Manglik Dosh, Kaal Sarp, Pitra Dosh, or Sade Sati? Ask me how these afflictions operate in your chart and which classical satvik remedies can resolve them.',
                hi: 'नमस्ते **{name} जी**! **कुंडली दोष एवं शास्त्रीय उपाय ऑडिट रिपोर्ट** के संदर्भ में, क्या आप मांगलिक दोष, कालसर्प, पितृ दोष अथवा साढ़ेसाती के प्रभाव के विषय में जानना चाहते हैं? निसंकोच पूछें!',
                hinglish: 'Namaste **{name} ji**! **Kundali Dosh & Remedial Audit Report** explore kar rahe hain? Manglik Dosh, Kaal Sarp, Pitra Dosh ya Sade Sati ke remedies ke baare mein poochna chahte hain? Poochein!'
            },
            questions: {
                en: [
                    'Do I have Manglik Dosh, and does it get cancelled in my chart?',
                    'Is Kaal Sarp or Pitra Dosh causing delays in my growth?',
                    'Which Vedic mantras, satvik fasts, or gemstones will neutralize my doshas?',
                    'What is covered in this 28-page remedial audit?'
                ],
                hi: [
                    'क्या मेरी कुंडली में मांगलिक दोष है, और क्या इसका परिहार हो रहा है?',
                    'क्या कालसर्प या पितृ दोष के कारण मेरे कार्यों में रुकावटें आ रही हैं?',
                    'दोष निवारण हेतु कौन से वैदिक मंत्र, व्रत अथवा रत्न धारण करने चाहिए?',
                    'इस 28 पृष्ठ की विस्तृत उपाय रिपोर्ट में क्या-क्या शामिल है?'
                ],
                hinglish: [
                    'Kya meri chart mein Manglik Dosh hai aur kya uska cancelation ho raha hai?',
                    'Kya Kaal Sarp ya Pitra Dosh se meri progress mein delay ho raha hai?',
                    'Dosha shanti ke liye best Vedic mantras aur remedies kya hain?',
                    'Is 28-page remedial audit report mein kya-kya diya gaya hai?'
                ]
            }
        },
        'kundali-and-gun-matching-report': {
            title: 'Master Kundali Matching Report',
            welcome: {
                en: 'Namaste **{name}**! Looking into **Kundali Milan & 36 Guna Matching**? Ask me how Ashta Koota compatibility works, whether Nadi Dosh or Bhakoot Dosh can be neutralized, or how mutual mental harmony is scored for marriage.',
                hi: 'नमस्ते **{name} जी**! **कुंडली मिलान एवं 36 गुण मिलान रिपोर्ट** के संदर्भ में, अष्टकूट मिलान, नाड़ी दोष, भकूट दोष परिहार अथवा वैवाहिक सुख की अनुकूलता के बारे में आपका क्या प्रश्न है?',
                hinglish: 'Namaste **{name} ji**! **Kundali & 36 Guna Matching** ke baare mein discuss karna chahte hain? Ashta Koota score, Nadi Dosh cancelation aur mental compatibility ke baare mein poochein!'
            },
            questions: {
                en: [
                    'What happens if the 36 Guna matching score is below 18?',
                    'How serious is Nadi Dosh, and can it be cancelled?',
                    'Can strong mental & planetary friendship override minor doshas?',
                    'What comprehensive analysis is included in this 35-page matching report?'
                ],
                hi: [
                    'यदि गुण मिलान का स्कोर 18 से कम हो तो क्या विवाह संभव है?',
                    'नाड़ी दोष कितना गंभीर होता है और क्या इसका कोई परिहार है?',
                    'क्या ग्रहों की मित्रता और मानसिक अनुकूलता दोषों को कम कर सकती है?',
                    'इस 35 पृष्ठ की मैचिंग रिपोर्ट में दोनों कुंडलियों का क्या विश्लेषण है?'
                ],
                hinglish: [
                    'Agar Guna matching score 18 se kam ho toh kya hota hai?',
                    'Nadi Dosh kitna serious hota hai aur kya iska cancelation possible hai?',
                    'Kya planetary friendship minor doshas ko override kar sakti hai?',
                    'Is 35-page matching report mein dono partners ka kya analysis milta hai?'
                ]
            }
        },
        'numerology-report': {
            title: 'Master Numerology & Vedic Integration Report',
            welcome: {
                en: 'Namaste **{name}**! Exploring the **Master Numerology & Vedic Integration Report**? Discover how your Mulank (Birth Number), Bhagyank (Destiny Number), and Name vibrations harmonize with your ruling planetary deities. Ask me anything!',
                hi: 'नमस्ते **{name} जी**! **मास्टर अंकशास्त्र एवं वैदिक समन्वय रिपोर्ट** में आपका स्वागत है। आपका मूलांक, भाग्यांक और नाम का संख्याबल आपके ग्रहों के साथ कैसा सामंजस्य रखता है, पूछें!',
                hinglish: 'Namaste **{name} ji**! **Master Numerology & Vedic Integration Report** ke baare mein poochein. Mulank, Bhagyank aur Name vibrations aapke ruling planets ke sath kaise align hote hain?'
            },
            questions: {
                en: [
                    'What are my primary lucky numbers, colors, and favorable days?',
                    'Does my current name spelling vibrate harmoniously with my destiny number?',
                    'Which age milestones and calendar years will be major turning points for me?',
                    'How does Vedic numerology integrate with my birth chart planets?'
                ],
                hi: [
                    'मेरे मुख्य शुभ अंक, रंग और अनुकूल दिन कौन से हैं?',
                    'क्या मेरी वर्तमान नाम वर्तनी (Spelling) मेरे भाग्यांक के अनुकूल है?',
                    'मेरी आयु के कौन से वर्ष मेरे जीवन के सबसे बड़े टर्निंग पॉइंट सिद्ध होंगे?',
                    'वैदिक अंकशास्त्र मेरी जन्म कुंडली के ग्रहों के साथ कैसे तालमेल रखता है?'
                ],
                hinglish: [
                    'Mere lucky numbers, favorable days aur colors kaun se hain?',
                    'Kya meri name spelling mere destiny number ke sath match karti hai?',
                    'Life ke major turning points kaun se age milestones mein aayenge?',
                    'Vedic numerology meri janma kundali se kaise integrate hoti hai?'
                ]
            }
        },
        'monthly-horoscope-report': {
            title: 'Monthly Horoscope & Transit Report',
            welcome: {
                en: 'Namaste **{name}**! Checking your **Monthly Horoscope & Transit Report**? I can help you prepare for this month\'s planetary shifts, auspicious Muhurat dates, and sensitive days to avoid big risks. What\'s on your mind?',
                hi: 'नमस्ते **{name} जी**! **मासिक गोचर एवं राशिफल रिपोर्ट** के संदर्भ में, इस माह के ग्रह परिवर्तनों, शुभ मुहूर्तों और संवेदनशील दिनों के बारे में आपका क्या प्रश्न है?',
                hinglish: 'Namaste **{name} ji**! **Monthly Horoscope & Transit Report** explore kar rahe hain? Is month ke planetary shifts, auspicious dates aur caution days ke baare mein poochein!'
            },
            questions: {
                en: [
                    'What are the most auspicious dates for contracts or travel this month?',
                    'Which planetary transit is most critical for my Rashi this month?',
                    'How will this month\'s full moon and new moon affect my emotional & work state?',
                    'What actionable wisdom is packed in the 13-page monthly roadmap?'
                ],
                hi: [
                    'इस महीने महत्वपूर्ण कार्यों या यात्रा के लिए सबसे शुभ तिथियां कौन सी हैं?',
                    'मेरी राशि के लिए इस माह कौन सा ग्रह गोचर सबसे महत्वपूर्ण है?',
                    'इस माह की पूर्णिमा और अमावस्या मेरे मानसिक और कार्यक्षेत्र को कैसे प्रभावित करेंगी?',
                    'इस 13 पृष्ठ की मासिक रिपोर्ट में कौन से उपाय और दिशानिर्देश दिए गए हैं?'
                ],
                hinglish: [
                    'Is month deals, contracts ya travel ke liye most auspicious dates kaun si hain?',
                    'Meri Rashi ke liye is month kaun sa planetary transit most critical hai?',
                    'Full Moon aur New Moon is month mujhe kaise affect karenge?',
                    'Is 13-page monthly roadmap mein kya-kya actionable guidelines hain?'
                ]
            }
        },
        'palm-reading-report': {
            title: 'Master Hast Rekha & Palmistry Report',
            welcome: {
                en: 'Pranam **{name}**! Exploring the **Master Hast Rekha & Palmistry Report**? Samudrika Shastra reveals the living canvas of your mounts, life line, heart line, and destiny line. How can I guide your hand analysis?',
                hi: 'प्रणाम **{name} जी**! **हस्तरेखा एवं सामुद्रिक शास्त्र रिपोर्ट** के विषय में क्या जानना चाहते हैं? आपकी हथेलियों के पर्वत, जीवन रेखा, हृदय रेखा और भाग्य रेखा आपके जीवन का सजीव दर्पण हैं।',
                hinglish: 'Pranam **{name} ji**! **Master Hast Rekha & Palmistry Report** explore kar rahe hain? Aapki palm lines, mounts, life line aur fate line ke baare mein kya poochna chahte hain?'
            },
            questions: {
                en: [
                    'Can palm lines confirm or verify my birth chart dasha timing?',
                    'What does a strong Sun Line (Surya Rekha) or trident signify?',
                    'Which hand is analyzed for career progression vs. relationships?',
                    'What exhaustive insights are included in this 22-page palmistry dossier?'
                ],
                hi: [
                    'क्या हस्तरेखाएं मेरी कुंडली की महादशा और समय-चक्र की पुष्टि करती हैं?',
                    'हथेली में मजबूत सूर्य रेखा अथवा त्रिशूल चिह्न का क्या अर्थ होता है?',
                    'करियर और विवाह के विश्लेषण हेतु कौन से हाथ का अध्ययन किया जाता है?',
                    'इस 22 पृष्ठ की विस्तृत हस्तरेखा रिपोर्ट में क्या-क्या सम्मिलित है?'
                ],
                hinglish: [
                    'Kya palm lines meri Kundali dasha timing ko verify karti hain?',
                    'Strong Sun Line ya trident symbol ka palmistry mein kya meaning hai?',
                    'Career aur relationships ke liye dominant vs passive hand kaise dekhein?',
                    'Is 22-page palmistry dossier mein kya-kya covered hai?'
                ]
            }
        }
    };

    // Slugs and internal aliases
    reportContextData['landingpage'] = reportContextData['yearly-horoscope-report'];
    reportContextData['yearly_horoscope_long'] = reportContextData['yearly-horoscope-report'];
    reportContextData['yearly_horoscope_vedic'] = reportContextData['yearly-horoscope-snapshot-report'];
    reportContextData['kundli_predictions'] = reportContextData['janam-kundali-master-report'];
    reportContextData['mahadasha_timing'] = reportContextData['dasha-timing-report'];
    reportContextData['dosh_remedial'] = reportContextData['kundali-dosh-report'];
    reportContextData['kundli_matching'] = reportContextData['kundali-and-gun-matching-report'];
    reportContextData['numerology'] = reportContextData['numerology-report'];
    reportContextData['monthly_horoscope_vedic'] = reportContextData['monthly-horoscope-report'];
    reportContextData['palmistry_report'] = reportContextData['palm-reading-report'];

    function applyReportContext(slug, title, initialQuestion) {
        var ctx = reportContextData[slug] || null;
        if (!ctx) return;

        state.activeReportContext = { slug: slug, title: title || ctx.title };
        var lang = state.lang || 'en';
        var questions = (ctx.questions && ctx.questions[lang]) || (ctx.questions && ctx.questions['en']) || [];
        state.reportCustomSuggestions = questions;

        if (state.chartToken && state.birthData) {
            showView('chat');

            var displayName = (state.birthData && state.birthData.name) ? state.birthData.name : (config.user_name || 'Friend');
            var welcomeTemplate = (ctx.welcome && ctx.welcome[lang]) || (ctx.welcome && ctx.welcome['en']) || '';
            var welcomeText = welcomeTemplate.replace('{name}', displayName);

            var orderBtnData = {
                slug: slug,
                title: title || ctx.title,
                label: 'Looking for the complete written dossier?',
                btnText: 'Order Full Dossier →'
            };

            // Avoid duplicate greetings for the same report if it was the last message
            var lastMsg = state.history[state.history.length - 1];
            if (!lastMsg || lastMsg.report_slug !== slug) {
                state.history.push({
                    role: 'model',
                    text: welcomeText,
                    report_slug: slug,
                    is_report_greeting: true,
                    order_btn: orderBtnData
                });
                appendMessageBubble('model', welcomeText, { orderBtn: orderBtnData });
                try {
                    localStorage.setItem('niyati_chat_history', JSON.stringify(state.history));
                } catch(e) {}
            }

            renderSuggestions();
            scrollToBottom();

            if (initialQuestion && el.userInput) {
                el.userInput.value = initialQuestion;
                el.userInput.focus();
            }
        } else {
            showView('onboarding');
            var subEl = document.getElementById('niyati-onboarding-sub');
            if (subEl) {
                subEl.innerHTML = 'Enter your birth details for your <strong>' + escapeHTML(title || ctx.title) + '</strong> consultation';
            }
            state.pendingReportContext = { slug: slug, title: title || ctx.title, question: initialQuestion };
        }
    }

    // Expose global window.openNiyatiChat
    window.openNiyatiChat = function (reportSlug, reportTitle, initialQuestion) {
        if (!state.isInitialized) {
            state.pendingReportContext = { slug: reportSlug, title: reportTitle, question: initialQuestion };
            showInitializingToast();
            return;
        }

        if (!state.isOpen) {
            state.isOpen = true;
            hideNudgeBubble();
            el.container.classList.add('active');
        }

        if (reportSlug) {
            applyReportContext(reportSlug, reportTitle, initialQuestion);
        } else {
            scrollToBottom();
            if (el.userInput && !el.userInput.disabled) {
                el.userInput.focus();
            }
        }
    };

    // Expose global window.openNiyatiRecharge
    window.openNiyatiRecharge = function (packKey) {
        if (!state.isInitialized) {
            showInitializingToast();
            return;
        }
        if (!state.isOpen) {
            state.isOpen = true;
            hideNudgeBubble();
            el.container.classList.add('active');
        }
        openRechargeSheet();
        if (packKey) {
            setTimeout(function () {
                var btn = el.packsContainer.querySelector('[data-pack="' + packKey + '"]');
                if (btn) {
                    var card = btn.closest('.niyati-pack-card');
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.style.transform = 'scale(1.02)';
                        card.style.transition = 'transform 0.3s ease';
                        setTimeout(function () { card.style.transform = ''; }, 1000);
                    }
                }
            }, 250);
        }
    };

    // Expose direct buy function for pricing cards
    window.buyNiyatiPackDirect = function (packKey, triggerBtn) {
        if (!state.isInitialized) {
            showInitializingToast();
            return;
        }
        var foundPack = null;
        if (state.packs && state.packs.length) {
            for (var i = 0; i < state.packs.length; i++) {
                if (state.packs[i].key === packKey) {
                    foundPack = state.packs[i];
                    break;
                }
            }
        }
        if (!foundPack) {
            window.openNiyatiRecharge(packKey);
            return;
        }
        // If chat isn't open yet, open it
        if (!state.isOpen) {
            state.isOpen = true;
            hideNudgeBubble();
            el.container.classList.add('active');
        }
        openRechargeSheet();
        var internalBtn = el.packsContainer.querySelector('[data-pack="' + packKey + '"]') || triggerBtn;
        handleBuyPack(foundPack, internalBtn);
    };

    // Auto-init on DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
