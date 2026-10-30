<?php
/**
 * Niyati Chat Nudge & Festive Scheduler Manager
 * Handles periodic proactive speech bubbles, custom messages, and date-based festive wishes.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Nudge {

    /**
     * Get General Nudge Settings
     */
    public static function get_settings() {
        $defaults = [
            'enabled'          => 1,
            'interval_minutes' => 30,
            'display_seconds'  => 30,
            'default_message'  => 'Ask Niyati is active to answer all your questions',
        ];
        $saved = get_option('niyati_nudge_settings', []);
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Get All Scheduled Campaigns
     */
    public static function get_campaigns() {
        $campaigns = get_option('niyati_nudge_campaigns', []);
        return is_array($campaigns) ? $campaigns : [];
    }

    /**
     * Determine the currently active nudge message based on real-time schedule
     */
    public static function get_active_nudge() {
        $settings = self::get_settings();
        if (empty($settings['enabled'])) {
            return [
                'enabled' => false,
            ];
        }

        $now = current_time('timestamp');
        $active_message = $settings['default_message'];
        $campaign_name  = 'default';
        $is_scheduled   = false;

        $campaigns = self::get_campaigns();
        if (!empty($campaigns)) {
            foreach ($campaigns as $camp) {
                if (empty($camp['status']) || $camp['status'] !== 'active') {
                    continue;
                }

                $start = !empty($camp['start_date']) ? strtotime($camp['start_date']) : 0;
                $end   = !empty($camp['end_date']) ? strtotime($camp['end_date']) : PHP_INT_MAX;

                if ($now >= $start && $now <= $end) {
                    $active_message = $camp['message'];
                    $campaign_name  = $camp['name'] ?? 'scheduled';
                    $is_scheduled   = true;
                    break; // Priority to the first active matching campaign
                }
            }
        }

        return [
            'enabled'          => true,
            'message'          => $active_message,
            'interval_minutes' => max(1, intval($settings['interval_minutes'] ?? 30)),
            'display_seconds'  => max(3, intval($settings['display_seconds'] ?? 30)),
            'campaign'         => $campaign_name,
            'is_scheduled'     => $is_scheduled,
        ];
    }

    /**
     * Save General Nudge Settings
     */
    public static function save_settings($data) {
        $settings = [
            'enabled'          => !empty($data['enabled']) ? 1 : 0,
            'interval_minutes' => max(1, intval($data['interval_minutes'] ?? 30)),
            'display_seconds'  => max(0, intval($data['display_seconds'] ?? 30)),
            'default_message'  => sanitize_text_field(trim($data['default_message'] ?? 'Ask Niyati is active to answer all your questions')),
        ];
        update_option('niyati_nudge_settings', $settings);
        return $settings;
    }

    /**
     * Save or Add a Scheduled Campaign
     */
    public static function save_campaign($data) {
        $campaigns = self::get_campaigns();
        $id = !empty($data['id']) ? sanitize_key($data['id']) : 'camp_' . time() . '_' . wp_rand(100, 999);

        $name       = sanitize_text_field(trim($data['name'] ?? 'Scheduled Wish'));
        $message    = sanitize_text_field(trim($data['message'] ?? ''));
        $start_date = sanitize_text_field(trim($data['start_date'] ?? ''));
        $end_date   = sanitize_text_field(trim($data['end_date'] ?? ''));
        $status     = (!empty($data['status']) && $data['status'] === 'paused') ? 'paused' : 'active';

        if (empty($message)) {
            return false;
        }

        $campaigns[$id] = [
            'id'         => $id,
            'name'       => $name,
            'message'    => $message,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'status'     => $status,
            'updated_at' => current_time('mysql'),
        ];

        update_option('niyati_nudge_campaigns', $campaigns);
        return $id;
    }

    /**
     * Delete a Campaign
     */
    public static function delete_campaign($id) {
        $campaigns = self::get_campaigns();
        if (isset($campaigns[$id])) {
            unset($campaigns[$id]);
            update_option('niyati_nudge_campaigns', $campaigns);
            return true;
        }
        return false;
    }

    /**
     * Toggle Campaign Active/Paused Status
     */
    public static function toggle_campaign($id) {
        $campaigns = self::get_campaigns();
        if (isset($campaigns[$id])) {
            $campaigns[$id]['status'] = ($campaigns[$id]['status'] === 'active') ? 'paused' : 'active';
            update_option('niyati_nudge_campaigns', $campaigns);
            return $campaigns[$id]['status'];
        }
        return false;
    }
}
