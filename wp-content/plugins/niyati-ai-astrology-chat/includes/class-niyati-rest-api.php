<?php
/**
 * REST API Endpoints for Niyati Astrology Chat
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_REST_API {

    const NAMESPACE = 'niyati/v1';

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
        add_filter('rest_authentication_errors', [__CLASS__, 'handle_rest_auth_errors'], 101);
    }

    /**
     * Intercept stale nonce errors for Niyati endpoints.
     * Prevents 403 rest_cookie_invalid_nonce lockouts when page cache, social login redirects (NSL),
     * or stale browser tabs are used.
     */
    public static function handle_rest_auth_errors($errors) {
        if (is_wp_error($errors) && $errors->get_error_code() === 'rest_cookie_invalid_nonce') {
            $rest_route = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
            if (empty($rest_route) && isset($_SERVER['REQUEST_URI'])) {
                $rest_route = $_SERVER['REQUEST_URI'];
            }
            if (strpos($rest_route, 'niyati/v1') !== false) {
                // If user has a valid login auth cookie, authenticate them directly
                $cookie_user = wp_validate_auth_cookie('', 'logged_in');
                if ($cookie_user) {
                    wp_set_current_user($cookie_user);
                    return true;
                }
                // If guest, set current user to 0 and allow through to endpoint
                wp_set_current_user(0);
                return true;
            }
        }
        return $errors;
    }

    public static function register_routes() {
        // Init Config & Quota info
        register_rest_route(self::NAMESPACE, '/init', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'handle_init'],
            'permission_callback' => '__return_true',
        ]);

        // Compute Chart & Start Session
        register_rest_route(self::NAMESPACE, '/compute-chart', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_compute_chart'],
            'permission_callback' => '__return_true',
        ]);

        // Chat Message
        register_rest_route(self::NAMESPACE, '/chat', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_chat'],
            'permission_callback' => '__return_true',
        ]);

        // Create Payment Order for Credits
        register_rest_route(self::NAMESPACE, '/create-order', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_create_order'],
            'permission_callback' => '__return_true',
        ]);

        // Verify Payment & Credit Wallet
        register_rest_route(self::NAMESPACE, '/verify-payment', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_verify_payment'],
            'permission_callback' => '__return_true',
        ]);

        // Record Payment Failure or Drop-off
        register_rest_route(self::NAMESPACE, '/fail-order', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_fail_order'],
            'permission_callback' => '__return_true',
        ]);

        // City Search
        register_rest_route(self::NAMESPACE, '/cities', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'handle_city_search'],
            'permission_callback' => '__return_true',
        ]);

        // Reset & Archive Chat Session
        register_rest_route(self::NAMESPACE, '/reset-chat', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_reset_chat'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Get default settings with fallbacks
     */
    public static function get_settings() {
        $defaults = [
            'bot_name'              => 'Niyati',
            'bot_title'             => 'Vedic Astrologer (वैदिक ज्योतिषी)',
            'floating_enabled'      => '1',
            'theme_color'           => '#D97706',
            'free_limit_new_user'   => 3,
            'free_limit_old_user'   => 1,
            'limit_reset_period'    => 'daily',
            'welcome_msg_hi'        => "नमस्ते! मैं नियति हूँ, आपकी व्यक्तिगत वैदिक ज्योतिषी। आपकी जन्म कुंडली का अध्ययन कर मैं आपके जीवन, करियर, प्रेम और भाग्य के प्रश्नों का समाधान कर सकती हूँ।",
            'welcome_msg_en'        => "Namaste! I am Niyati, your personal Vedic Astrologer. By analyzing your authentic birth chart, I will guide you with accurate planetary insights, career prospects, and remedies.",
            'welcome_msg_hinglish'  => "Namaste! Main Niyati hoon, aapki personal Vedic Astrologer. Aapki kundli ke grah-nakshatra dekh kar main aapke career, shaadi aur life ke sawaalon ka sahi margdarshan karungi.",
            'questions_hi'          => "मेरी शादी कब तक होगी और वैवाहिक जीवन कैसा रहेगा?\nकरियर में प्रमोशन या नई नौकरी का सही समय कब है?\nक्या मुझ पर शनि की साढ़ेसाती या मंगल दोष का प्रभाव है?\nमेरी आर्थिक स्थिति कब सुधरेगी?\nमेरे जीवन के संकट दूर करने के लिए कौन सा सरल उपाय करें?",
            'questions_en'          => "When will I get married and how will my partner be?\nWhen is the ideal time for career advancement or job change?\nAm I undergoing Sade Sati or affected by Mangal Dosha?\nWhat does my chart say about financial prosperity?\nWhich Vedic remedies or mantras will bring me peace and success?",
            'questions_hinglish'    => "Meri shaadi kab tak hogi aur life partner kaisa hoga?\nJob change ya promotion ke liye favorable time kab aa raha hai?\nKya meri kundli mein Shani ki Sade Sati ya Manglik dosh hai?\nFinance aur income improve hone ke yoga kab hain?\nMeri current dasha ke liye sabse best upay kya hai?",
            'razorpay_key_id'       => '',
            'razorpay_key_secret'   => '',
            'razorpay_test_mode'    => '1',
            'archive_retention_days'=> 90,
        ];

        $saved = get_option('niyati_chat_settings', []);
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Get active Razorpay Key ID (prioritizes Central MR_Razorpay gateway)
     */
    public static function get_razorpay_key_id() {
        if (class_exists('MR_Razorpay')) {
            $key = MR_Razorpay::get_active_key_id();
            if (!empty($key)) {
                return $key;
            }
        }
        $settings = self::get_settings();
        $fallback = get_option('mr_razorpay_test_key_id', 'rzp_test_TpIAA4q3WP1EYF');
        return !empty($settings['razorpay_key_id']) ? trim($settings['razorpay_key_id']) : $fallback;
    }

    /**
     * Get active Razorpay Key Secret (prioritizes Central MR_Razorpay gateway)
     */
    public static function get_razorpay_key_secret() {
        if (class_exists('MR_Razorpay')) {
            $secret = MR_Razorpay::get_active_key_secret();
            if (!empty($secret)) {
                return $secret;
            }
        }
        $settings = self::get_settings();
        $fallback = get_option('mr_razorpay_test_key_secret', '4YJwgnmeWvKdYEaKmpiv9pH1');
        return !empty($settings['razorpay_key_secret']) ? trim($settings['razorpay_key_secret']) : $fallback;
    }

    /**
     * Get active Razorpay Gateway Mode ('test' or 'live')
     */
    public static function get_razorpay_mode() {
        if (class_exists('MR_Razorpay')) {
            return MR_Razorpay::get_mode();
        }
        $settings = self::get_settings();
        return (!empty($settings['razorpay_test_mode']) && $settings['razorpay_test_mode'] === '1') ? 'test' : 'live';
    }

    /**
     * Handle Init: returns bot data, current user quota & wallet balance
     */
    public static function handle_init($request) {
        $settings = self::get_settings();
        $visitor_id = Niyati_Limits::get_visitor_id();
        $user_id = get_current_user_id();
        if (!$user_id) {
            $cookie_user = wp_validate_auth_cookie('', 'logged_in');
            if ($cookie_user) {
                $user_id = $cookie_user;
                wp_set_current_user($user_id);
            }
        }
        $is_logged_in = ($user_id > 0) && is_user_logged_in();

        $quota = Niyati_Limits::check_quota($visitor_id);
        $paid_credits = Niyati_Wallet::get_balance($visitor_id, $user_id);

        $questions_by_lang = [
            'hi'       => array_filter(array_map('trim', explode("\n", $settings['questions_hi']))),
            'en'       => array_filter(array_map('trim', explode("\n", $settings['questions_en']))),
            'hinglish' => array_filter(array_map('trim', explode("\n", $settings['questions_hinglish']))),
        ];

        $packs = array_values(Niyati_Wallet::get_packs(true));

        $chat_history = [];
        $saved_profiles = [];
        $active_chart_token = '';
        if ($is_logged_in && class_exists('Niyati_Storage')) {
            $active_chat = Niyati_Storage::get_active_chat($user_id);
            if (!empty($active_chat['messages'])) {
                $chat_history = $active_chat['messages'];
            }
            if (!empty($active_chat['chart_token'])) {
                $active_chart_token = $active_chat['chart_token'];
            }
            $saved_kundlis = Niyati_Storage::get_saved_kundlis($user_id);
            if (!empty($saved_kundlis)) {
                $saved_profiles = array_values($saved_kundlis);
            }
        }

        return rest_ensure_response([
            'success'            => true,
            'bot_name'           => $settings['bot_name'],
            'bot_title'          => $settings['bot_title'],
            'avatar_url'         => NIYATI_PLUGIN_URL . 'assets/images/niyati-avatar.jpg',
            'theme'              => $settings['theme_color'],
            'welcome'            => [
                'hi'       => $settings['welcome_msg_hi'],
                'en'       => $settings['welcome_msg_en'],
                'hinglish' => $settings['welcome_msg_hinglish'],
            ],
            'questions'          => $questions_by_lang,
            'quota'              => $quota,
            'paid_credits'       => $paid_credits,
            'validity'           => Niyati_Wallet::get_validity_info($visitor_id, $user_id),
            'packs'              => $packs,
            'razorpay_key_id'    => self::get_razorpay_key_id(),
            'razorpay_test_mode' => self::get_razorpay_mode() === 'test',
            'visitor_id'         => $visitor_id,
            'nonce'              => wp_create_nonce('wp_rest'),
            'is_logged_in'       => $is_logged_in,
            'login_url'          => wp_login_url(wp_get_referer() ?: home_url()),
            'nudge'              => class_exists('Niyati_Nudge') ? Niyati_Nudge::get_active_nudge() : ['enabled' => false],
            'chat_history'       => $chat_history,
            'saved_profiles'     => $saved_profiles,
            'active_chart_token' => $active_chart_token,
        ]);
    }

    /**
     * Handle Chart Computation and Welcome Greeting
     */
    public static function handle_compute_chart($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $user_id = get_current_user_id();
        if (!$user_id && !empty($params['user_id'])) {
            $user_id = absint($params['user_id']);
        }

        $lang = sanitize_text_field($params['lang'] ?? 'en');

        // Non-logged in user will not get service till logged in
        if (!$user_id) {
            $login_err = ($lang === 'hi') 
                ? 'नियति ज्योतिष सेवा का उपयोग करने के लिए कृपया पहले अपने खाते में लॉगिन करें।' 
                : 'Please log in to your account first to access Niyati astrology consultations.';
            return new WP_Error('login_required', $login_err, [
                'status'    => 401,
                'login_url' => wp_login_url(wp_get_referer() ?: home_url())
            ]);
        }

        $name = sanitize_text_field($params['name'] ?? 'Friend');
        $dob  = sanitize_text_field($params['dob'] ?? '');
        $tob  = sanitize_text_field($params['tob'] ?? '12:00');
        $lat  = floatval($params['lat'] ?? 28.6139);
        $lon  = floatval($params['lon'] ?? 77.2090);
        $pob  = sanitize_text_field($params['pob'] ?? 'Delhi, India');
        $tz   = floatval($params['tz'] ?? 5.5);

        if (empty($dob)) {
            $dob_err = ($lang === 'hi') ? 'कृपया जन्म तिथि दर्ज करें।' : 'Please provide your Date of Birth.';
            return new WP_Error('missing_dob', $dob_err, ['status' => 400]);
        }

        $birth_input = [
            'name' => $name,
            'dob'  => $dob,
            'tob'  => $tob,
            'pob'  => $pob,
            'lat'  => $lat,
            'lon'  => $lon,
            'tz'   => $tz,
            'lang' => $lang,
        ];

        $profile = null;
        $profile_hash = '';

        // 1. Check if user already entered this family member before (0 VedicAstroAPI cost!)
        if (class_exists('Niyati_Storage') && $user_id > 0) {
            $profile_hash = Niyati_Storage::get_profile_hash($birth_input);
            $saved = Niyati_Storage::find_saved_kundli($user_id, $birth_input);
            if ($saved && is_array($saved)) {
                $profile = $saved;
                $profile['name'] = $name;
            }
        }

        // 2. If not already saved in user's family Kundli file, call VedicAstroAPI once & save to disk
        if (!$profile) {
            $profile = Niyati_Vedic_API::get_kundli_profile($birth_input);
            if (class_exists('Niyati_Storage') && $user_id > 0 && !empty($profile)) {
                Niyati_Storage::save_kundli_profile($user_id, $birth_input, $profile);
            }
        }

        // Generate session chart token (cached for 30 days)
        $chart_token = 'chk_' . md5($name . '_' . $dob . '_' . $tob . '_' . $user_id);
        set_transient('niyati_sess_' . $chart_token, $profile, 30 * DAY_IN_SECONDS);

        // Generate Niyati's personalized opening greeting
        $settings = self::get_settings();
        $bot_name = $settings['bot_name'] ?? 'Niyati';

        $asc = !empty($profile['ascendant']) ? $profile['ascendant'] : 'लग्न';
        $rashi = !empty($profile['moon_sign']) ? $profile['moon_sign'] : 'राशि';
        $dasha = !empty($profile['current_dasha']['dasha_string']) ? $profile['current_dasha']['dasha_string'] : ($profile['current_dasha']['mahadasha'] ?? '');

        // Time-of-day greeting for English
        $client_time_greeting = !empty($params['time_greeting']) ? sanitize_text_field($params['time_greeting']) : '';
        if (!empty($client_time_greeting)) {
            $time_greeting_en = $client_time_greeting;
        } else {
            $hour = (int) current_time('H');
            if ($hour >= 4 && $hour < 12) {
                $time_greeting_en = 'Good morning';
            } elseif ($hour >= 12 && $hour < 17) {
                $time_greeting_en = 'Good afternoon';
            } else {
                $time_greeting_en = 'Good evening';
            }
        }

        if ($lang === 'hi') {
            $greeting = "नमस्ते **{$name} जी**! मैं **{$bot_name}** हूँ, आपकी जन्म पत्रिका का विश्लेषण पूर्ण हो गया है।\n\n"
                      . "✨ **लग्न:** {$asc}\n"
                      . "🌙 **चंद्र राशि:** {$rashi}\n"
                      . ($dasha ? "🪐 **वर्तमान महादशा:** {$dasha} की महादशा सक्रिय है।\n\n" : "\n")
                      . "मैं आपकी कुंडली के आधार पर किस विषय (करियर, विवाह, स्वास्थ्य या आर्थिक स्थिति) पर आपका मार्गदर्शन करूँ?";
        } elseif ($lang === 'hinglish') {
            $greeting = "Namaste **{$name} ji**! Main **{$bot_name}** hoon, aapki kundli study kar li gayi hai.\n\n"
                      . "✨ **Lagna (Ascendant):** {$asc}\n"
                      . "🌙 **Moon Sign (Rashi):** {$rashi}\n"
                      . ($dasha ? "🪐 **Current Mahadasha:** {$dasha} ki dasha chal rahi hai.\n\n" : "\n")
                      . "Aap career, shaadi, finance ya dasha se jude kisi bhi sawaal ke baare mein pooch sakte hain.";
        } else {
            $greeting = "{$time_greeting_en}, **{$name}**! Namaste, I am **{$bot_name}**, your personal Vedic Astrologer. I have analyzed your birth chart.\n\n"
                      . "✨ **Ascendant (Lagna):** {$asc}\n"
                      . "🌙 **Moon Sign (Rashi):** {$rashi}\n"
                      . ($dasha ? "🪐 **Current Planetary Period:** {$dasha} Mahadasha is active.\n\n" : "\n")
                      . "What question is closest to your heart today? You can ask me about your career, marriage, finances, or life path.";
        }

        if (class_exists('Niyati_Linker')) {
            $greeting = Niyati_Linker::auto_link_response($greeting);
        }

        // Initialize active chat session on disk for cross-device synchronization
        if (class_exists('Niyati_Storage') && $user_id > 0) {
            Niyati_Storage::set_active_chat_session($user_id, $chart_token, $profile_hash, $name, $greeting);
        }

        return rest_ensure_response([
            'success'     => true,
            'chart_token' => $chart_token,
            'greeting'    => $greeting,
            'profile'     => [
                'name'      => $profile['name'],
                'ascendant' => $profile['ascendant'],
                'moon_sign' => $profile['moon_sign'],
                'dasha'     => $profile['current_dasha'] ?? null,
                'doshas'    => $profile['doshas'] ?? null,
            ]
        ]);
    }

    /**
     * Handle Chat Message: Checks Free Quota first, then Paid Wallet Credits
     */
    public static function handle_chat($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $message     = sanitize_text_field($params['message'] ?? '');
        $chart_token = sanitize_text_field($params['chart_token'] ?? '');
        $history     = $params['history'] ?? [];
        $lang        = sanitize_text_field($params['lang'] ?? 'en');
        if (class_exists('Niyati_Gemini_API')) {
            $lang = Niyati_Gemini_API::resolve_language($message, $lang);
        }

        if (empty($message)) {
            $empty_err = ($lang === 'hi') ? 'संदेश रिक्त नहीं हो सकता।' : 'Message cannot be empty.';
            return new WP_Error('empty_message', $empty_err, ['status' => 400]);
        }

        $visitor_id = Niyati_Limits::get_visitor_id();
        $user_id = get_current_user_id();
        if (!$user_id) {
            $cookie_user = wp_validate_auth_cookie('', 'logged_in');
            if ($cookie_user) {
                $user_id = $cookie_user;
                wp_set_current_user($user_id);
            }
        }
        if (!$user_id && !empty($params['user_id'])) {
            $user_id = absint($params['user_id']);
        }

        // Non-logged in user will not get service till logged in
        if (!$user_id) {
            $login_err = ($lang === 'hi') 
                ? 'नियति से चैट जारी रखने के लिए कृपया पहले अपने खाते में लॉगिन करें।' 
                : 'Please log in to your account first to continue chatting with Niyati.';
            return new WP_Error('login_required', $login_err, [
                'status'    => 401,
                'login_url' => wp_login_url(wp_get_referer() ?: home_url())
            ]);
        }

        // 1. Check Free Quota & Paid Balance
        $free_quota = Niyati_Limits::check_quota($visitor_id);
        $paid_balance = Niyati_Wallet::get_balance($visitor_id, $user_id);

        $has_free_credit = $free_quota['allowed'];
        $has_paid_credit = ($paid_balance > 0);

        // Pre-check for site directory navigation / report search intent
        $is_nav_query = self::is_navigation_query($message);

        // 2. TIER 1 GUARDRAIL CHECK: Fast Deterministic Interception for Sensitive Questions
        // Policy: Out-of-bounds questions are answered with ethical boundaries and NEVER deduct credits!
        if (class_exists('Niyati_Guardrails')) {
            $guardrail = Niyati_Guardrails::check_message($message, $lang);
            if (!empty($guardrail['triggered'])) {
                if (class_exists('Niyati_Storage') && $user_id > 0) {
                    Niyati_Storage::append_chat_turn($user_id, $message, $guardrail['reply']);
                }
                return rest_ensure_response([
                    'success'          => true,
                    'guardrail'        => true,
                    'category'         => $guardrail['category'],
                    'reply'            => $guardrail['reply'],
                    'used_credit_type' => 'none', // NO CREDIT DEDUCTED
                    'quota'            => $free_quota,
                    'paid_credits'     => $paid_balance
                ]);
            }
        }

        // 3. Quota check: Allow if user has credits OR if query is a free site directory navigation query
        if (!$has_free_credit && !$has_paid_credit && !$is_nav_query) {
            $msg = ($lang === 'hi') 
                ? ($free_quota['limit_exceeded_msg']['hi'] ?? 'आपकी निःशुल्क प्रश्न सीमा समाप्त हो चुकी है। कृपया चैट जारी रखने के लिए क्रेडिट्स रिचार्ज करें।')
                : ($free_quota['limit_exceeded_msg']['en'] ?? 'You have reached your free question limit. Please recharge credits to continue chatting with Niyati.');

            return rest_ensure_response([
                'success'        => false,
                'quota_exceeded' => true,
                'needs_recharge' => true,
                'reply'          => $msg,
                'quota'          => $free_quota,
                'paid_credits'   => 0,
                'packs'          => array_values(Niyati_Wallet::get_packs(true))
            ]);
        }

        // Retrieve cached Kundli profile
        $profile = get_transient('niyati_sess_' . $chart_token);
        if (!$profile || !is_array($profile)) {
            $profile = [
                'name'      => 'Jatak',
                'ascendant' => 'Vedic Chart',
                'moon_sign' => 'Rashi',
            ];
        }

        // Call Gemini (Tier 2 Guardrails & Site Navigation embedded in prompt)
        $res = Niyati_Gemini_API::generate_chat_response($message, $profile, $history, $lang);

        if ($res['success']) {
            // Check if Tier 2 (LLM) declined via ethical guardrail
            $is_llm_guardrail = false;
            if (preg_match('/\[DECL[A-Z_]*\]/i', $res['reply'])) {
                $is_llm_guardrail = true;
                $res['reply'] = trim(preg_replace('/\[DECL[A-Z_]*\]/i', '', $res['reply']));
                if (class_exists('Niyati_Guardrails')) {
                    $res['reply'] .= Niyati_Guardrails::get_credit_preservation_note($lang);
                }
            } elseif (class_exists('Niyati_Guardrails') && Niyati_Guardrails::is_guardrail_response($res['reply'])) {
                $is_llm_guardrail = true;
                $res['reply'] = trim(preg_replace('/\[DECL[A-Z_]*\]/i', '', $res['reply']));
                if (strpos($res['reply'], 'credit') === false && strpos($res['reply'], 'क्रेडिट') === false) {
                    $res['reply'] .= Niyati_Guardrails::get_credit_preservation_note($lang);
                }
            }

            // Check if response is Site Directory Navigation (100% Free - No credits deducted)
            // CRITICAL BUSINESS RULE: Credit is ONLY waived if the USER explicitly asked for site navigation ($is_nav_query is true)!
            // If the user asked an astrology question and Niyati optionally recommends a link, credit MUST BE DEDUCTED.
            $is_site_navigation = false;
            if (!$is_llm_guardrail && !empty($is_nav_query)) {
                if (self::is_site_navigation_response($res['reply'], $message)) {
                    $is_site_navigation = true;
                }
            }

            // Clean any internal protocol tags from the reply
            $res['reply'] = trim(preg_replace('/\[(?:DECL|NAV)[A-Z_]*\]/i', '', $res['reply']));

            if ($is_llm_guardrail) {
                if (class_exists('Niyati_Storage') && $user_id > 0) {
                    Niyati_Storage::append_chat_turn($user_id, $message, $res['reply']);
                }
                // DO NOT DEDUCT CREDIT for LLM ethical guardrail decline!
                return rest_ensure_response([
                    'success'          => true,
                    'guardrail'        => true,
                    'reply'            => $res['reply'],
                    'used_credit_type' => 'none',
                    'quota'            => $free_quota,
                    'paid_credits'     => $paid_balance
                ]);
            }

            if ($is_site_navigation) {
                // DO NOT DEDUCT CREDIT for User-Initiated Site Directory Navigation!
                if (strpos($res['reply'], 'credit') === false && strpos($res['reply'], 'क्रेडिट') === false) {
                    $res['reply'] .= self::get_navigation_credit_preservation_note($lang);
                }
                if (class_exists('Niyati_Storage') && $user_id > 0) {
                    Niyati_Storage::append_chat_turn($user_id, $message, $res['reply']);
                }
                return rest_ensure_response([
                    'success'          => true,
                    'navigation'       => true,
                    'reply'            => $res['reply'],
                    'used_credit_type' => 'none', // NO CREDIT DEDUCTED
                    'quota'            => $free_quota,
                    'paid_credits'     => $paid_balance
                ]);
            }

            // Fallback check: If user had 0 credits and query was neither guardrail nor genuine site navigation
            if (!$has_free_credit && !$has_paid_credit) {
                $msg = ($lang === 'hi') 
                    ? ($free_quota['limit_exceeded_msg']['hi'] ?? 'आपकी निःशुल्क प्रश्न सीमा समाप्त हो चुकी है। कृपया चैट जारी रखने के लिए क्रेडिट्स रिचार्ज करें।')
                    : ($free_quota['limit_exceeded_msg']['en'] ?? 'You have reached your free question limit. Please recharge credits to continue chatting with Niyati.');

                return rest_ensure_response([
                    'success'        => false,
                    'quota_exceeded' => true,
                    'needs_recharge' => true,
                    'reply'          => $msg,
                    'quota'          => $free_quota,
                    'paid_credits'   => 0,
                    'packs'          => array_values(Niyati_Wallet::get_packs(true))
                ]);
            }

            // Legitimate astrological answer: clean any accidental credit disclaimer note
            $res['reply'] = preg_replace('/\s*\*\s*\((?:Note|सूचना):[^\)]*(?:credit|क्रेडिट)[^\)]*\)\s*\*\s*/iu', '', $res['reply']);
            $res['reply'] = preg_replace('/\s*\*\s*\(Note:\s*Your chat credit has not been deducted[^\)]*\)\s*\*\s*/i', '', $res['reply']);
            $res['reply'] = preg_replace('/\s*\*\s*\(सूचना:\s*इस प्रश्न के लिए आपके चैट क्रेडिट[^\)]*\)\s*\*\s*/iu', '', $res['reply']);
            $res['reply'] = preg_replace('/\s*\*\s*\(Note:\s*Is sawaal ke liye aapka chat credit[^\)]*\)\s*\*\s*/i', '', $res['reply']);
            $res['reply'] = trim($res['reply']);

            $used_credit_type = 'free';

            if ($has_free_credit) {
                // Deduct from free quota
                $updated_quota = Niyati_Limits::increment_question_count($visitor_id);
                $remaining_paid = $paid_balance;
                $used_credit_type = 'free';
            } else {
                // Deduct 1 from paid wallet
                $remaining_paid = Niyati_Wallet::consume_credit($visitor_id, $user_id);
                $updated_quota = $free_quota;
                $used_credit_type = 'paid';
            }

            // Persist conversation turn to user's disk file (zero MySQL bloat)
            if (class_exists('Niyati_Storage') && $user_id > 0) {
                Niyati_Storage::append_chat_turn($user_id, $message, $res['reply']);
            }

            return rest_ensure_response([
                'success'          => true,
                'reply'            => $res['reply'],
                'used_credit_type' => $used_credit_type,
                'quota'            => $updated_quota,
                'paid_credits'     => $remaining_paid
            ]);
        }

        return rest_ensure_response([
            'success'      => false,
            'reply'        => $res['reply'],
            'error'        => $res['error'] ?? 'Unknown error',
            'quota'        => $free_quota,
            'paid_credits' => $paid_balance
        ]);
    }

    /**
     * Handle Reset Chat: Archives active chat session on disk to .json.gz and resets active session
     */
    public static function handle_reset_chat($request) {
        $params = $request->get_json_params() ?: $request->get_params();
        $user_id = get_current_user_id();
        if (!$user_id && !empty($params['user_id'])) {
            $user_id = absint($params['user_id']);
        }

        if ($user_id > 0 && class_exists('Niyati_Storage')) {
            Niyati_Storage::archive_active_chat($user_id);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Chat session archived and reset.'
        ]);
    }

    /**
     * Create Order for Credit Pack
     */
    public static function handle_create_order($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $pack_key   = sanitize_key($params['pack_key'] ?? '');
        $visitor_id = sanitize_text_field($params['visitor_id'] ?? Niyati_Limits::get_visitor_id());

        $packs = Niyati_Wallet::get_packs(true);
        if (!isset($packs[$pack_key])) {
            return new WP_Error('invalid_pack', 'अमान्य क्रेडिट पैक। Invalid credit pack.', ['status' => 400]);
        }

        $pack         = $packs[$pack_key];
        $key_id       = self::get_razorpay_key_id();
        $key_secret   = self::get_razorpay_key_secret();
        $gateway_mode = self::get_razorpay_mode();
        $user_id      = get_current_user_id();
        if (!$user_id && !empty($params['user_id'])) {
            $user_id = intval($params['user_id']);
        }

        // Customer Info snapshot
        $customer_name  = sanitize_text_field($params['customer_name'] ?? '');
        $customer_email = sanitize_email($params['customer_email'] ?? '');
        $customer_phone = sanitize_text_field($params['customer_phone'] ?? '');

        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                if (empty($customer_name)) {
                    $customer_name = $user->display_name ?: $user->user_login;
                }
                if (empty($customer_email)) {
                    $customer_email = $user->user_email;
                }
            }
        }
        if (empty($customer_name)) {
            $customer_name = 'Visitor ' . substr($visitor_id, 0, 8);
        }

        // Generate internal transaction reference
        $internal_txn_id = class_exists('MR_Transactions')
            ? MR_Transactions::generate_internal_id()
            : ('MR-TXN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)));

        // If Razorpay keys are not configured, fallback to offline simulation
        if (empty($key_id) || empty($key_secret)) {
            $sim_order_id = 'order_sim_' . wp_generate_uuid4();

            if (class_exists('MR_Transactions')) {
                MR_Transactions::create_transaction([
                    'internal_txn_id'  => $internal_txn_id,
                    'user_id'          => $user_id,
                    'customer_name'    => $customer_name,
                    'customer_email'   => $customer_email,
                    'customer_phone'   => $customer_phone,
                    'channel'          => 'chat_credit',
                    'item_key'         => $pack_key,
                    'item_name'        => 'Niyati Chat - ' . $pack['total_credits'] . ' Credits Pack',
                    'txn_type'         => 'top_up',
                    'gross_amount'     => $pack['offer_price'],
                    'discount_amount'  => 0.00,
                    'net_amount'       => $pack['offer_price'],
                    'currency'         => 'INR',
                    'gateway'          => 'razorpay',
                    'gateway_mode'     => $gateway_mode,
                    'gateway_order_id' => $sim_order_id,
                    'status'           => 'pending',
                    'metadata'         => [
                        'credits_added' => (int)$pack['total_credits'],
                        'validity_days' => (int)($pack['validity_days'] ?? 0),
                        'visitor_id'    => $visitor_id,
                    ]
                ]);
            }

            return rest_ensure_response([
                'success'         => true,
                'order_id'        => $sim_order_id,
                'internal_txn_id' => $internal_txn_id,
                'amount'          => $pack['offer_price'],
                'currency'        => 'INR',
                'pack'            => $pack,
                'simulated'       => true,
                'key_id'          => 'test_key',
            ]);
        }

        // Live Razorpay API Call
        $rzp_url = 'https://api.razorpay.com/v1/orders';
        $payload = [
            'amount'   => intval(round($pack['offer_price'] * 100)), // paise
            'currency' => 'INR',
            'receipt'  => $internal_txn_id,
            'notes'    => [
                'pack_key'        => $pack_key,
                'visitor_id'      => $visitor_id,
                'credits'         => $pack['total_credits'],
                'internal_txn_id' => $internal_txn_id,
                'channel'         => 'chat_credit'
            ]
        ];

        $response = wp_remote_post($rzp_url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($key_id . ':' . $key_secret),
                'Content-Type'  => 'application/json'
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 20
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('razorpay_error', $response->get_error_message(), ['status' => 500]);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['id'])) {
            return new WP_Error('razorpay_failed', $body['error']['description'] ?? 'Unable to create payment order.', ['status' => 500]);
        }

        $razorpay_order_id = $body['id'];

        // Step 1: Record pending transaction in universal passbook ledger
        if (class_exists('MR_Transactions')) {
            $drop_url = esc_url_raw($params['drop_url'] ?? (wp_get_referer() ?: home_url($_SERVER['REQUEST_URI'] ?? '/')));
            MR_Transactions::create_transaction([
                'internal_txn_id'  => $internal_txn_id,
                'user_id'          => $user_id,
                'customer_name'    => $customer_name,
                'customer_email'   => $customer_email,
                'customer_phone'   => $customer_phone,
                'channel'          => 'chat_credit',
                'item_key'         => $pack_key,
                'item_name'        => 'Niyati Chat - ' . $pack['total_credits'] . ' Credits Pack',
                'txn_type'         => 'top_up',
                'gross_amount'     => $pack['offer_price'],
                'discount_amount'  => 0.00,
                'net_amount'       => $pack['offer_price'],
                'currency'         => 'INR',
                'gateway'          => 'razorpay',
                'gateway_mode'     => $gateway_mode,
                'gateway_order_id' => $razorpay_order_id,
                'status'           => 'pending',
                'drop_url'         => $drop_url,
                'metadata'         => [
                    'credits_added' => (int)$pack['total_credits'],
                    'validity_days' => (int)($pack['validity_days'] ?? 0),
                    'visitor_id'    => $visitor_id,
                ]
            ]);
        }

        return rest_ensure_response([
            'success'         => true,
            'order_id'        => $razorpay_order_id,
            'internal_txn_id' => $internal_txn_id,
            'amount'          => $pack['offer_price'],
            'currency'        => 'INR',
            'pack'            => $pack,
            'simulated'       => false,
            'key_id'          => $key_id,
        ]);
    }

    /**
     * Verify Payment and Credit Wallet
     */
    public static function handle_verify_payment($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $pack_key        = sanitize_key($params['pack_key'] ?? '');
        $payment_id      = sanitize_text_field($params['payment_id'] ?? '');
        $order_id        = sanitize_text_field($params['order_id'] ?? '');
        $signature       = sanitize_text_field($params['signature'] ?? '');
        $internal_txn_id = sanitize_text_field($params['internal_txn_id'] ?? '');
        $visitor_id      = sanitize_text_field($params['visitor_id'] ?? Niyati_Limits::get_visitor_id());
        $phone           = sanitize_text_field($params['phone'] ?? '');
        $email           = sanitize_email($params['email'] ?? '');
        $user_id         = get_current_user_id();
        if (!$user_id && !empty($params['user_id'])) {
            $user_id = intval($params['user_id']);
        }

        $packs = Niyati_Wallet::get_packs(false);
        if (!isset($packs[$pack_key])) {
            return new WP_Error('invalid_pack', 'Invalid pack key.', ['status' => 400]);
        }

        $pack       = $packs[$pack_key];
        $key_secret = self::get_razorpay_key_secret();

        if (strpos($order_id, 'order_sim_') !== false) {
            // Simulated payment verification
            $verified = true;
            if (empty($payment_id)) {
                $payment_id = 'pay_sim_' . wp_generate_uuid4();
            }
        } else {
            // Real Razorpay signature verification
            $expected_sig = hash_hmac('sha256', $order_id . '|' . $payment_id, $key_secret);
            if (hash_equals($expected_sig, $signature)) {
                $verified = true;
            }
        }

        if (!$verified) {
            // Mark failed in universal ledger
            if (class_exists('MR_Transactions')) {
                $target_id = !empty($order_id) ? $order_id : $internal_txn_id;
                MR_Transactions::fail_transaction($target_id, 'Razorpay HMAC signature verification failed');
            }
            return new WP_Error('verification_failed', 'Payment verification failed.', ['status' => 400]);
        }

        $validity_days = intval($pack['validity_days'] ?? 0);

        // Add credits to wallet with full immutable pack snapshot and validity stacking
        $new_balance = Niyati_Wallet::add_credits(
            $visitor_id,
            $user_id,
            $pack['total_credits'],
            $pack['offer_price'],
            $payment_id,
            $pack_key,
            'razorpay',
            $params,
            $validity_days,
            $pack
        );

        // Step 2: Complete transaction in Universal Passbook
        if (class_exists('MR_Transactions')) {
            $target_id = !empty($order_id) ? $order_id : $internal_txn_id;
            MR_Transactions::complete_transaction(
                $target_id,
                $payment_id,
                $signature,
                [
                    'credits_added'        => (int)$pack['total_credits'],
                    'validity_days'        => (int)$validity_days,
                    'wallet_balance_after' => (int)$new_balance,
                    'verified_at'          => current_time('mysql'),
                ]
            );
        }

        $validity_info = Niyati_Wallet::get_validity_info($visitor_id, $user_id);

        return rest_ensure_response([
            'success'       => true,
            'message'       => 'क्रेडिट्स सफलतापूर्वक जुड़ गए हैं! (Credits added successfully!)',
            'credits_added' => $pack['total_credits'],
            'new_balance'   => $new_balance,
            'validity'      => $validity_info,
            'pack_title'    => $pack['title'],
        ]);
    }

    /**
     * Record payment failure or abandonment (Step 3)
     */
    public static function handle_fail_order($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $order_id        = sanitize_text_field($params['order_id'] ?? '');
        $internal_txn_id = sanitize_text_field($params['internal_txn_id'] ?? '');
        $reason          = sanitize_text_field($params['reason'] ?? 'Payment cancelled or dismissed by user');
        $drop_stage      = sanitize_text_field($params['drop_stage'] ?? '');
        $drop_url        = esc_url_raw($params['drop_url'] ?? (wp_get_referer() ?: ''));

        $target_id = !empty($order_id) ? $order_id : $internal_txn_id;

        $is_dismissed = ($drop_stage === 'gateway_modal_dismissed')
            || stripos($reason, 'dismissed') !== false
            || stripos($reason, 'cancelled') !== false
            || stripos($reason, 'closed') !== false;

        if (!empty($target_id) && class_exists('MR_Transactions')) {
            if ($is_dismissed) {
                MR_Transactions::mark_abandoned($target_id, 'gateway_modal_dismissed', $drop_url, [
                    'drop_reason' => $reason,
                    'client_type' => 'niyati_chat'
                ]);
            } else {
                MR_Transactions::fail_transaction($target_id, $reason);
            }
        }

        return rest_ensure_response([
            'success'   => true,
            'status'    => $is_dismissed ? 'abandoned' : 'failed',
            'message'   => $is_dismissed ? 'Abandoned cart drop-off recorded' : 'Transaction failure recorded'
        ]);
    }

    /**
     * City Search with Top Indian Cities + OSM geocoding
     */
    public static function handle_city_search($request) {
        $query = sanitize_text_field($request->get_param('q') ?? '');
        $query = trim(strtolower($query));

        if (empty($query)) {
            return rest_ensure_response([]);
        }

        $common_cities = [
            ['name' => 'New Delhi, Delhi, India', 'lat' => 28.6139, 'lon' => 77.2090, 'tz' => 5.5],
            ['name' => 'Mumbai, Maharashtra, India', 'lat' => 19.0760, 'lon' => 72.8777, 'tz' => 5.5],
            ['name' => 'Bengaluru (Bangalore), Karnataka, India', 'lat' => 12.9716, 'lon' => 77.5946, 'tz' => 5.5],
            ['name' => 'Kolkata, West Bengal, India', 'lat' => 22.5726, 'lon' => 88.3639, 'tz' => 5.5],
            ['name' => 'Chennai, Tamil Nadu, India', 'lat' => 13.0827, 'lon' => 80.2707, 'tz' => 5.5],
            ['name' => 'Hyderabad, Telangana, India', 'lat' => 17.3850, 'lon' => 78.4867, 'tz' => 5.5],
            ['name' => 'Ahmedabad, Gujarat, India', 'lat' => 23.0225, 'lon' => 72.5714, 'tz' => 5.5],
            ['name' => 'Pune, Maharashtra, India', 'lat' => 18.5204, 'lon' => 73.8567, 'tz' => 5.5],
            ['name' => 'Jaipur, Rajasthan, India', 'lat' => 26.9124, 'lon' => 75.7873, 'tz' => 5.5],
            ['name' => 'Lucknow, Uttar Pradesh, India', 'lat' => 26.8467, 'lon' => 80.9462, 'tz' => 5.5],
            ['name' => 'Kanpur, Uttar Pradesh, India', 'lat' => 26.4499, 'lon' => 80.3319, 'tz' => 5.5],
            ['name' => 'Nagpur, Maharashtra, India', 'lat' => 21.1458, 'lon' => 79.0882, 'tz' => 5.5],
            ['name' => 'Indore, Madhya Pradesh, India', 'lat' => 22.7196, 'lon' => 75.8577, 'tz' => 5.5],
            ['name' => 'Bhopal, Madhya Pradesh, India', 'lat' => 23.2599, 'lon' => 77.4126, 'tz' => 5.5],
            ['name' => 'Patna, Bihar, India', 'lat' => 25.5941, 'lon' => 85.1376, 'tz' => 5.5],
            ['name' => 'Vadodara, Gujarat, India', 'lat' => 22.3072, 'lon' => 73.1812, 'tz' => 5.5],
            ['name' => 'Varanasi, Uttar Pradesh, India', 'lat' => 25.3176, 'lon' => 82.9739, 'tz' => 5.5],
            ['name' => 'Meerut, Uttar Pradesh, India', 'lat' => 28.9845, 'lon' => 77.7064, 'tz' => 5.5],
            ['name' => 'Noida, Uttar Pradesh, India', 'lat' => 28.5355, 'lon' => 77.3910, 'tz' => 5.5],
            ['name' => 'Gurugram (Gurgaon), Haryana, India', 'lat' => 28.4595, 'lon' => 77.0266, 'tz' => 5.5],
            ['name' => 'Agra, Uttar Pradesh, India', 'lat' => 27.1767, 'lon' => 78.0081, 'tz' => 5.5],
            ['name' => 'Chandigarh, India', 'lat' => 30.7333, 'lon' => 76.7794, 'tz' => 5.5],
            ['name' => 'Surat, Gujarat, India', 'lat' => 21.1702, 'lon' => 72.8311, 'tz' => 5.5],
            ['name' => 'Dehradun, Uttarakhand, India', 'lat' => 30.3165, 'lon' => 78.0322, 'tz' => 5.5],
            ['name' => 'Haridwar, Uttarakhand, India', 'lat' => 29.9457, 'lon' => 78.1642, 'tz' => 5.5],
            ['name' => 'Prayagraj (Allahabad), Uttar Pradesh, India', 'lat' => 25.4358, 'lon' => 81.8463, 'tz' => 5.5],
            ['name' => 'Amritsar, Punjab, India', 'lat' => 31.6340, 'lon' => 74.8723, 'tz' => 5.5],
            ['name' => 'Ludhiana, Punjab, India', 'lat' => 30.9010, 'lon' => 75.8573, 'tz' => 5.5],
            ['name' => 'Ranchi, Jharkhand, India', 'lat' => 23.3441, 'lon' => 85.3096, 'tz' => 5.5],
            ['name' => 'Raipur, Chhattisgarh, India', 'lat' => 21.2514, 'lon' => 81.6296, 'tz' => 5.5],
            ['name' => 'Guwahati, Assam, India', 'lat' => 26.1445, 'lon' => 91.7362, 'tz' => 5.5],
            ['name' => 'Bhubaneswar, Odisha, India', 'lat' => 20.2961, 'lon' => 85.8245, 'tz' => 5.5],
            ['name' => 'Dubai, United Arab Emirates', 'lat' => 25.2048, 'lon' => 55.2708, 'tz' => 4.0],
            ['name' => 'London, United Kingdom', 'lat' => 51.5074, 'lon' => -0.1278, 'tz' => 0.0],
            ['name' => 'New York, United States', 'lat' => 40.7128, 'lon' => -74.0060, 'tz' => -5.0],
            ['name' => 'Toronto, Canada', 'lat' => 43.6532, 'lon' => -79.3832, 'tz' => -5.0],
            ['name' => 'Singapore', 'lat' => 1.3521, 'lon' => 103.8198, 'tz' => 8.0],
            ['name' => 'Sydney, Australia', 'lat' => -33.8688, 'lon' => 151.2093, 'tz' => 10.0],
        ];

        $results = [];
        foreach ($common_cities as $c) {
            if (stripos($c['name'], $query) !== false) {
                $results[] = $c;
                if (count($results) >= 8) break;
            }
        }

        // If not found in common list, call OpenStreetMap Nominatim
        if (empty($results) && strlen($query) >= 3) {
            $osm_url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($query) . '&limit=5&addressdetails=1';
            $osm_res = wp_remote_get($osm_url, [
                'headers' => ['User-Agent' => 'MeraRashifal-NiyatiBot/1.0'],
                'timeout' => 5
            ]);

            if (!is_wp_error($osm_res)) {
                $osm_data = json_decode(wp_remote_retrieve_body($osm_res), true);
                if (is_array($osm_data)) {
                    foreach ($osm_data as $item) {
                        $results[] = [
                            'name' => $item['display_name'] ?? $query,
                            'lat'  => floatval($item['lat']),
                            'lon'  => floatval($item['lon']),
                            'tz'   => 5.5
                        ];
                    }
                }
            }
        }

        return rest_ensure_response($results);
    }

    /**
     * Check if user message indicates an intent to navigate the site or find a report/tool/page
     */
    public static function is_navigation_query($message) {
        $text = trim($message);
        if (empty($text)) {
            return false;
        }

        $lower = mb_strtolower($text, 'UTF-8');

        // Direct report or feature query patterns
        $report_patterns = [
            'kundali report', 'kundli report', 'birth chart report', 'janam patri report',
            'matching report', 'kundali matching', 'kundli matching', 'gun milan', 'matchmaking', 'kundali milan', 'kundli milan', 'compatibility',
            'dosh report', 'mangal dosh', 'kaal sarp', 'pitra dosh', 'manglik',
            'dasha report', 'mahadasha report', 'antardasha report', 'current dasha',
            'panchang', 'choghadiya', 'muhurat', 'rahukaal', 'abhijit muhurat',
            'baby name', 'baby names', 'naam karan', 'name suitability', 'boy names', 'girl names',
            'palm reading', 'hastrekha', 'remedies', 'upay',
            'pricing', 'recharge', 'credits pack', 'buy credits', 'passbook',
            'site map', 'sitemap', 'site directory', 'website pages', 'all reports'
        ];

        foreach ($report_patterns as $rp) {
            if (mb_strpos($lower, $rp) !== false) {
                // If it mentions navigation words OR asks for the report/page/link
                if (preg_match('/\b(link|page|url|site|website|kahan|kaise|where|how to|download|pdf|button|click|view|dekhein|dekhna|bhejo|batao|chahiye|generate|create|open|navigate|show me|list|today|aaj)\b/iu', $lower)) {
                    return true;
                }
                // Specific phrases that are standalone navigation requests
                if (preg_match('/\b(kundali report|kundli report|birth chart report|gun milan|matchmaking|panchang|muhurat|pricing|recharge|passbook|sitemap)\b/iu', $lower)) {
                    return true;
                }
            }
        }

        // Generic navigation queries
        if (preg_match('/\b(where can i find|where is the|how can i get|give me link|send me link|page link|report link|website menu|site directory)\b/i', $lower)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a response provides site navigation or links to Merarashifal pages
     */
    public static function is_site_navigation_response($reply, $message = '') {
        if (empty($reply)) {
            return false;
        }

        // 1. Explicit protocol tag from Gemini
        if (preg_match('/\[NAV_[A-Z_]*\]/i', $reply)) {
            return true;
        }

        // 2. Contains link to Merarashifal site directory pages
        $site_base = untrailingslashit(home_url());
        $escaped_base = preg_quote($site_base, '#');

        $dir_paths = [
            'kundali-details',
            'reports-catalog',
            'reports',
            'my-account-reports',
            'matchmaking-compatibility-check',
            'compatibility-check',
            'dosh',
            'dasha',
            'horoscope',
            'yearly-horoscope-prediction',
            'panchang',
            'daily-choghadiya-muhurat',
            'muhurat-dates',
            'festivals',
            'baby-names',
            'name-suitability',
            'palm-reading',
            'remedies-astrological',
            'pricing',
            'passbook',
            'my-account'
        ];

        $paths_regex = implode('|', array_map(function($p) { return preg_quote($p, '#'); }, $dir_paths));

        // Matches full URL or relative link to directory paths
        if (preg_match('#(?:' . $escaped_base . '|merarashifal\.com|https?://[^/\s]+)/(?:' . $paths_regex . ')#i', $reply)) {
            return true;
        }

        // 3. Contains action button markdown or CTA link
        if (preg_match('/\[(?:👉|✨|📜|🔍|🔗)[^\]]+\]\s*\((https?:\/\/[^\)]+)\)/u', $reply)) {
            return true;
        }

        // 4. Intent matched AND reply contains any markdown link or CTA
        if (!empty($message) && self::is_navigation_query($message) && preg_match('/\[[^\]]+\]\s*\((https?:\/\/[^\)]+)\)/i', $reply)) {
            return true;
        }

        return false;
    }

    /**
     * Credit preservation note for complimentary site navigation queries
     */
    public static function get_navigation_credit_preservation_note($lang = 'en') {
        if ($lang === 'hi') {
            return "\n\n*(सूचना: वेबसाइट डायरेक्टरी और रिपोर्ट नेविगेशन निःशुल्क है। इसके लिए कोई चैट क्रेडिट नहीं काटा गया है।)*";
        } elseif ($lang === 'hinglish') {
            return "\n\n*(Note: Site directory aur report navigation complimentary hai. Iske liye koi chat credit deduct nahi kiya gaya hai.)*";
        }
        return "\n\n*(Note: Site directory navigation is complimentary. No chat credit was deducted.)*";
    }
}
