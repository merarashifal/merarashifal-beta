<?php
/**
 * Niyati Limits & User Tracking Engine
 * Manages question quotas for new vs regular (old) users.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Limits {

    const COOKIE_NAME = 'niyati_visitor_token';

    /**
     * Get or create a unique visitor ID
     */
    public static function get_visitor_id() {
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        }

        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            return sanitize_text_field($_COOKIE[self::COOKIE_NAME]);
        }

        // If not in cookie, check custom header or generate one
        $token = 'guest_' . wp_generate_uuid4();
        // Set cookie if headers not yet sent
        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, $token, time() + (365 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
        }
        return $token;
    }

    /**
     * Get user usage record
     */
    public static function get_user_record($visitor_id) {
        $option_key = 'niyati_usr_' . md5($visitor_id);
        $record = get_transient($option_key);

        if (!$record || !is_array($record)) {
            // Check long-term option fallback
            $all_records = get_option('niyati_user_quotas', []);
            if (isset($all_records[$visitor_id])) {
                $record = $all_records[$visitor_id];
            } else {
                // Completely new visitor
                $record = [
                    'visitor_id'       => $visitor_id,
                    'is_new_user'      => true,
                    'first_seen'       => time(),
                    'last_seen'        => time(),
                    'total_asked'      => 0,
                    'asked_today'      => 0,
                    'today_date'       => current_time('Y-m-d'),
                ];
            }
        }

        // Check if day changed for daily resets
        $today = current_time('Y-m-d');
        if (isset($record['today_date']) && $record['today_date'] !== $today) {
            $record['asked_today'] = 0;
            $record['today_date'] = $today;
            // If they were new on a previous day and have asked questions, they are now a regular/old user
            if ($record['total_asked'] > 0) {
                $record['is_new_user'] = false;
            }
        }

        return $record;
    }

    /**
     * Save user usage record
     */
    public static function save_user_record($visitor_id, $record) {
        $option_key = 'niyati_usr_' . md5($visitor_id);
        set_transient($option_key, $record, 30 * DAY_IN_SECONDS);

        // Also save persistently
        $all_records = get_option('niyati_user_quotas', []);
        $all_records[$visitor_id] = $record;
        // Keep only recent 5000 users to prevent unbounded wp_options growth
        if (count($all_records) > 5000) {
            $all_records = array_slice($all_records, -4000, null, true);
        }
        update_option('niyati_user_quotas', $all_records, false);
    }

    /**
     * Check if user is allowed to ask a question
     * 
     * @param string $visitor_id
     * @return array [allowed => bool, remaining => int, limit => int, user_type => string, message => string]
     */
    public static function check_quota($visitor_id) {
        $settings = get_option('niyati_chat_settings', []);
        $new_limit = isset($settings['free_limit_new_user']) ? intval($settings['free_limit_new_user']) : 3;
        $old_limit = isset($settings['free_limit_old_user']) ? intval($settings['free_limit_old_user']) : 1;
        $reset_period = $settings['limit_reset_period'] ?? 'daily'; // 'daily' or 'lifetime'

        $record = self::get_user_record($visitor_id);
        $is_new = !empty($record['is_new_user']);
        $user_limit = $is_new ? $new_limit : $old_limit;

        $questions_used = ($reset_period === 'daily') ? ($record['asked_today'] ?? 0) : ($record['total_asked'] ?? 0);
        $remaining = max(0, $user_limit - $questions_used);
        $allowed = ($remaining > 0);

        $limit_msg_en = !empty($settings['limit_exceeded_msg_en']) 
            ? $settings['limit_exceeded_msg_en'] 
            : 'You have reached your free question limit. Please subscribe or check back tomorrow for more astrological guidance from Niyati.';
        $limit_msg_hi = !empty($settings['limit_exceeded_msg_hi']) 
            ? $settings['limit_exceeded_msg_hi'] 
            : 'आपकी निःशुल्क प्रश्न सीमा समाप्त हो चुकी है। अधिक प्रश्नों के लिए कृपया सदस्यता लें अथवा कल पुनः पधारें।';

        return [
            'allowed'            => $allowed,
            'user_type'          => $is_new ? 'new_user' : 'regular_user',
            'remaining'          => $remaining,
            'limit'              => $user_limit,
            'used'               => $questions_used,
            'reset_period'       => $reset_period,
            'limit_exceeded_msg' => [
                'en' => $limit_msg_en,
                'hi' => $limit_msg_hi
            ]
        ];
    }

    /**
     * Deduct one question upon successful response
     */
    public static function increment_question_count($visitor_id) {
        $record = self::get_user_record($visitor_id);
        $record['total_asked'] = ($record['total_asked'] ?? 0) + 1;
        $record['asked_today'] = ($record['asked_today'] ?? 0) + 1;
        $record['last_seen'] = time();

        // If new user uses up their first quota, they transition to regular user
        $settings = get_option('niyati_chat_settings', []);
        $new_limit = isset($settings['free_limit_new_user']) ? intval($settings['free_limit_new_user']) : 3;
        if ($record['total_asked'] >= $new_limit) {
            $record['is_new_user'] = false;
        }

        self::save_user_record($visitor_id, $record);

        return self::check_quota($visitor_id);
    }
}
