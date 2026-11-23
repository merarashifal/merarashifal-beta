<?php
/**
 * MeraRashifal Silent Authentication & Magic Access Engine
 *
 * Provides silent auto-registration on checkout (guest-first conversion),
 * authenticated session establishment, and 1-click magic login token links.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Auth {

    public static function init() {
        // Intercept 1-click magic login tokens across frontend requests
        add_action('init', [__CLASS__, 'handle_magic_login_listener'], 2);
    }

    /**
     * Silently get existing user by email or auto-register a new WordPress subscriber
     * and automatically establish an authenticated session.
     *
     * @param string $email Customer email address
     * @param string $phone Optional customer phone/WhatsApp number
     * @param string $name Optional customer full name
     * @param array $extra_meta Additional metadata (birth details, preferences, DPDP flags)
     * @return array|WP_Error Array with user_id, is_new, user object, or WP_Error on failure
     */
    public static function get_or_create_user($email, $phone = '', $name = '', $extra_meta = []) {
        $email = sanitize_email(trim($email));
        if (empty($email) || !is_email($email)) {
            return new WP_Error('invalid_email', 'A valid email address is required for order fulfillment and account access.');
        }

        $phone = sanitize_text_field(trim($phone));
        $name  = sanitize_text_field(trim($name));
        $is_new = false;

        $user = get_user_by('email', $email);

        if (!$user) {
            // Generate high-entropy password & unique username
            $random_password = wp_generate_password(16, true, true);
            $base_username   = sanitize_user(strstr($email, '@', true), true);
            if (empty($base_username)) {
                $base_username = 'customer';
            }

            $username = $base_username;
            $counter  = 1;
            while (username_exists($username)) {
                $username = $base_username . '_' . wp_rand(100, 9999);
                $counter++;
                if ($counter > 20) {
                    $username = 'mr_' . wp_rand(100000, 999999);
                    break;
                }
            }

            $user_id = wp_create_user($username, $random_password, $email);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $display_name = !empty($name) ? $name : ucfirst(str_replace(['.', '_', '-'], ' ', $base_username));

            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $display_name,
                'first_name'   => $display_name,
                'role'         => 'subscriber'
            ]);

            update_user_meta($user_id, 'mr_auto_registered', current_time('mysql'));
            update_user_meta($user_id, 'mr_registration_source', 'guest_checkout');
            $is_new = true;
        } else {
            $user_id = $user->ID;
            // If existing user has no display name but name was passed, fill it in
            if (empty($user->display_name) && !empty($name)) {
                wp_update_user([
                    'ID'           => $user_id,
                    'display_name' => $name,
                    'first_name'   => $name
                ]);
            }
        }

        // Save / update phone metadata
        if (!empty($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone);
            update_user_meta($user_id, 'user_phone', $phone);
            update_user_meta($user_id, 'mr_phone', $phone);
        }

        // Save / update extra metadata (birth coordinates, DPDP compliance, preferences)
        if (!empty($extra_meta) && is_array($extra_meta)) {
            foreach ($extra_meta as $meta_key => $meta_val) {
                if (is_scalar($meta_val)) {
                    update_user_meta($user_id, sanitize_key($meta_key), sanitize_text_field((string)$meta_val));
                }
            }
        }

        // Silent Auto-Login: establish session auth cookies immediately
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        return [
            'user_id' => $user_id,
            'is_new'  => $is_new,
            'user'    => get_userdata($user_id)
        ];
    }

    /**
     * Generate a secure, 30-day single-click magic login URL
     *
     * @param int $user_id Target user ID
     * @param string $redirect_to Optional URL path to land upon login
     * @return string Signed magic login URL
     */
    public static function generate_magic_login_url($user_id, $redirect_to = '') {
        $user_id = intval($user_id);
        if ($user_id <= 0) {
            return home_url('/my-account/');
        }

        $raw_token   = wp_generate_password(32, false);
        $token_hash  = hash('sha256', $raw_token);
        $expiry_time = time() + (86400 * 30); // 30 Days

        update_user_meta($user_id, 'mr_magic_token_hash', $token_hash);
        update_user_meta($user_id, 'mr_magic_token_expiry', $expiry_time);

        $target = !empty($redirect_to) ? $redirect_to : home_url('/my-account/');

        return add_query_arg([
            'mr_auth' => $raw_token,
            'uid'     => $user_id,
            'ret'     => urlencode($target)
        ], home_url('/my-account/'));
    }

    /**
     * Validate and consume 1-click magic login token
     */
    public static function validate_and_consume_magic_token($raw_token, $user_id) {
        $user_id = intval($user_id);
        if ($user_id <= 0 || empty($raw_token)) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        $stored_hash   = get_user_meta($user_id, 'mr_magic_token_hash', true);
        $stored_expiry = intval(get_user_meta($user_id, 'mr_magic_token_expiry', true));

        if (empty($stored_hash) || empty($stored_expiry)) {
            return false;
        }

        // Verify expiration
        if (time() > $stored_expiry) {
            delete_user_meta($user_id, 'mr_magic_token_hash');
            delete_user_meta($user_id, 'mr_magic_token_expiry');
            return false;
        }

        // Verify cryptographic hash
        $provided_hash = hash('sha256', $raw_token);
        if (!hash_equals($stored_hash, $provided_hash)) {
            return false;
        }

        // Successfully verified: log user in
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        // Delete used token to prevent replay attacks
        delete_user_meta($user_id, 'mr_magic_token_hash');
        delete_user_meta($user_id, 'mr_magic_token_expiry');

        return true;
    }

    /**
     * Handle incoming magic login links in request
     */
    public static function handle_magic_login_listener() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (!empty($_GET['mr_auth']) && !empty($_GET['uid'])) {
            $raw_token = sanitize_text_field($_GET['mr_auth']);
            $uid       = intval($_GET['uid']);

            if (self::validate_and_consume_magic_token($raw_token, $uid)) {
                $return_url = !empty($_GET['ret']) ? esc_url_raw(urldecode($_GET['ret'])) : home_url('/my-account/');
                // Ensure safe redirect target
                $clean_ret = trim(parse_url($return_url, PHP_URL_PATH) ?? '', '/');
                if (stripos($clean_ret, 'wp-login') !== false || stripos($clean_ret, 'wp-admin') !== false) {
                    $return_url = home_url('/my-account/');
                }
                wp_safe_redirect($return_url);
                exit;
            }
        }
    }
}
