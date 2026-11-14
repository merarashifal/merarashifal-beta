<?php
/**
 * MeraRashifal Privacy & Cookie Acceptance Engine
 *
 * Implements bottom-left floating privacy banner, granular cookie settings modal,
 * first-party consent persistence, and auto-generates DPDP Act 2023 compliant Cookie Policy.
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Cookie_Consent {

    public static function init() {
        // Enqueue frontend scripts & styles
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets'], 20);

        // Render banner & settings modal in footer
        add_action('wp_footer', [__CLASS__, 'render_consent_banner_and_modal'], 99);

        // Register shortcode for the Cookie Policy page
        add_shortcode('mr_cookie_policy', [__CLASS__, 'render_cookie_policy_content']);

        // Auto-create essential legal pages if missing
        add_action('init', [__CLASS__, 'ensure_legal_pages_exist'], 5);
    }

    /**
     * Ensure /cookie-policy/ and /refund-policy/ pages exist in WordPress
     */
    public static function ensure_legal_pages_exist() {
        // Only run check once per admin or if pages are missing
        if (!get_option('mr_legal_pages_provisioned_v1')) {
            // 1. Cookie Policy Page
            $cookie_page = get_page_by_path('cookie-policy');
            if (!$cookie_page) {
                wp_insert_post([
                    'post_title'     => 'Cookie Policy',
                    'post_name'      => 'cookie-policy',
                    'post_content'   => '[mr_cookie_policy]',
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ]);
            }

            // 2. Cancellation and Refund Policy Page
            $refund_page = get_page_by_path('refund-policy');
            if (!$refund_page) {
                wp_insert_post([
                    'post_title'     => 'Cancellation and Refund Policy',
                    'post_name'      => 'refund-policy',
                    'post_content'   => self::get_default_refund_policy_html(),
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ]);
            }

            update_option('mr_legal_pages_provisioned_v1', 1);
        }
    }

    /**
     * Enqueue lightweight CSS and JavaScript for the cookie consent UI
     */
    public static function enqueue_assets() {
        if (is_admin()) {
            return;
        }

        wp_enqueue_style(
            'mr-cookie-consent-css',
            MR_PLUGIN_URL . 'assets/css/mr-cookie-consent.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'mr-cookie-consent-js',
            MR_PLUGIN_URL . 'assets/js/mr-cookie-consent.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('mr-cookie-consent-js', 'mr_cookie_consent_vars', [
            'cookie_policy_url'  => home_url('/cookie-policy/'),
            'privacy_policy_url' => home_url('/privacy-policy/'),
            'terms_url'          => home_url('/terms-and-conditions/'),
            'cookie_name'        => 'mr_cookie_consent',
            'cookie_expiry_days' => 365,
        ]);
    }

    /**
     * Render the Floating Privacy Acceptance Card, Settings Modal, and Reopen Trigger
     */
    public static function render_consent_banner_and_modal() {
        if (is_admin()) {
            return;
        }
        $cookie_policy_url = home_url('/cookie-policy/');
        ?>
        <!-- ============================================================== -->
        <!-- MERARASHIFAL PRIVACY & COOKIE CONSENT BANNER (Bottom-Left Card)-->
        <!-- Shown ONCE until visitor chooses preference; never reappears   -->
        <!-- ============================================================== -->
        <div id="mr-cookie-banner" class="mr-cookie-banner" role="region" aria-label="Privacy and Cookie Consent" style="display:none;">
            <div class="mr-cookie-banner-inner">
                <div class="mr-cookie-header">
                    <h3 class="mr-cookie-title">We care about your privacy</h3>
                </div>
                <div class="mr-cookie-body">
                    <p class="mr-cookie-desc">
                        This website uses cookies that are needed for the site to work properly and to get data on how you interact with it, as well as for marketing purposes. By accepting, you agree to store cookies on your device for ad targeting, personalization, and analytics as described in our <a href="<?php echo esc_url($cookie_policy_url); ?>" class="mr-cookie-policy-link" target="_blank" rel="noopener">Cookie policy</a>.
                    </p>
                </div>
                <div class="mr-cookie-actions">
                    <button type="button" class="mr-cookie-btn mr-cookie-btn-accept" id="mr-cookie-btn-accept-all">
                        Accept all
                    </button>
                    <button type="button" class="mr-cookie-btn mr-cookie-btn-reject" id="mr-cookie-btn-reject-all">
                        Reject all
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the comprehensive Cookie Policy content for the /cookie-policy/ page
     */
    public static function render_cookie_policy_content() {
        ob_start();
        ?>
        <div class="mr-legal-policy-wrapper" style="max-width:920px; margin:20px auto 60px auto; padding:0 16px; font-family:'Noto Sans', sans-serif; color:#1E293B; line-height:1.75;">
            <div style="background:#FFFDF9; border:1px solid #F1E5D1; border-radius:14px; padding:32px 36px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                
                <div style="border-bottom:2px solid #F6EDE3; padding-bottom:18px; margin-bottom:26px;">
                    <span style="display:inline-block; font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; color:#F58220; background:#FFF4E6; padding:4px 12px; border-radius:20px; border:1px solid #FED7AA; margin-bottom:10px;">
                        Statutory Compliance &bull; DPDP Act 2023
                    </span>
                    <h1 style="margin:0 0 8px 0; font-size:28px; font-weight:800; color:#7A1F3D;">Cookie Policy &bull; MeraRashifal.com</h1>
                    <p style="margin:0; font-size:13.5px; color:#64748B;">
                        <strong>Effective Date:</strong> November 2025 &bull; <strong>Last Updated:</strong> November 2026<br>
                        <strong>Data Fiduciary:</strong> Merarashifal Technologies (Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, U.P., 201014)
                    </p>
                </div>

                <div class="mr-legal-section" style="margin-bottom:24px;">
                    <h2 style="font-size:18px; font-weight:700; color:#101820; margin-bottom:10px;">1. What Are Cookies?</h2>
                    <p style="font-size:14px; color:#475569;">
                        Cookies are small text files placed on your computer, mobile phone, or tablet by websites that you visit. They are widely utilized to make web platforms function efficiently, provide a smoother user experience, remember customer preferences, and furnish analytical information to website operators.
                    </p>
                </div>

                <div class="mr-legal-section" style="margin-bottom:24px;">
                    <h2 style="font-size:18px; font-weight:700; color:#101820; margin-bottom:10px;">2. How MeraRashifal Uses Cookies</h2>
                    <p style="font-size:14px; color:#475569;">
                        MeraRashifal Technologies uses first-party and trusted third-party cookies strictly for lawful, explicit purposes under the Digital Personal Data Protection Act, 2023 (DPDP Act) and the Information Technology (SPDI) Rules, 2011:
                    </p>
                    
                    <div style="margin:16px 0; display:flex; flex-direction:column; gap:14px;">
                        <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px 20px;">
                            <h3 style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0F172A;">A. Strictly Necessary &amp; Security Cookies (Mandatory)</h3>
                            <p style="margin:0; font-size:13.5px; color:#475569;">
                                Required for core technical functionality: maintaining secure authentication sessions (via Google OAuth 2.0), protecting checkout forms against CSRF and malware attacks, remembering your shopping cart items, and generating official retail tax invoices. These cannot be switched off.
                            </p>
                        </div>

                        <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px 20px;">
                            <h3 style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0F172A;">B. Personalization &amp; Astrological Memory Cookies</h3>
                            <p style="margin:0; font-size:13.5px; color:#475569;">
                                Retains your preferred birth chart format (North Indian Diamond Kundali vs South Indian Square Chart), Vedic Sidereal Lahiri Ayanamsha calculations, and Niyati AI Astrologer session memory across visits.
                            </p>
                        </div>

                        <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px 20px;">
                            <h3 style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0F172A;">C. Performance &amp; Analytics Cookies</h3>
                            <p style="margin:0; font-size:13.5px; color:#475569;">
                                Furnishes aggregated, anonymous usage statistics via Google Site Kit and Google Analytics to help our engineering team optimize planetary calculation speeds and eliminate runtime bottlenecks.
                            </p>
                        </div>

                        <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px 20px;">
                            <h3 style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0F172A;">D. Marketing &amp; Advertising Cookies</h3>
                            <p style="margin:0; font-size:13.5px; color:#475569;">
                                Delivered through authorized advertising networks (such as Google AdSense) to show contextual, non-intrusive advertisements. <em>In strict compliance with Section 9 of the DPDP Act 2023, behavioral profiling or targeted advertising directed at minors under 18 years of age is strictly prohibited on MeraRashifal.</em>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mr-legal-section" style="margin-bottom:24px;">
                    <h2 style="font-size:18px; font-weight:700; color:#101820; margin-bottom:10px;">3. Managing Your Cookie Preferences</h2>
                    <p style="font-size:14px; color:#475569;">
                        You have continuous statutory rights to customize or withdraw consent for non-essential cookies at any time:
                    </p>
                    <ul style="font-size:14px; color:#475569; padding-left:22px; margin:8px 0 16px 0;">
                        <li><strong>In Your Account:</strong> Logged-in members can review, update, or revoke cookie categories anytime from <a href="<?php echo esc_url(home_url('/my-account/?tab=settings#mr-cookie-settings-section')); ?>" style="color:#F58220; text-decoration:underline; font-weight:700;">My Account &rarr; Settings</a>.</li>
                        <li><strong>Through Your Web Browser:</strong> You can configure your browser (Google Chrome, Safari, Mozilla Firefox, Microsoft Edge) to refuse or delete cookies. Note that disabling necessary cookies may prevent logging in or completing report purchases.</li>
                    </ul>
                </div>

                <div class="mr-legal-section" style="margin-bottom:24px;">
                    <h2 style="font-size:18px; font-weight:700; color:#101820; margin-bottom:10px;">4. Grievance Redressal &amp; Data Protection Officer</h2>
                    <p style="font-size:14px; color:#475569;">
                        For questions regarding this Cookie Policy or our data protection practices, please contact our designated Grievance Officer:
                    </p>
                    <div style="background:#F8FAFC; border-left:4px solid #F58220; padding:12px 18px; border-radius:0 8px 8px 0; font-size:13.5px; color:#334155;">
                        <strong>Entity:</strong> Merarashifal Technologies<br>
                        <strong>Officer:</strong> Grievance &amp; Data Protection Officer<br>
                        <strong>Address:</strong> Rishabh Cloud 9, Ahinsa Khand 2, Indirapuram, Ghaziabad, Uttar Pradesh, 201014<br>
                        <strong>Email:</strong> <a href="mailto:support@merarashifal.com" style="color:#2563EB;">support@merarashifal.com</a> &bull; <strong>SLA:</strong> 48 hours acknowledgment
                    </div>
                </div>

            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Default HTML for the Cancellation and Refund Policy
     */
    public static function get_default_refund_policy_html() {
        return '<div class="mr-legal-policy-wrapper" style="max-width:920px; margin:20px auto 60px auto; padding:0 16px; font-family:\'Noto Sans\', sans-serif; color:#1E293B; line-height:1.75;">
            <div style="background:#FFFDF9; border:1px solid #F1E5D1; border-radius:14px; padding:32px 36px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                <div style="border-bottom:2px solid #F6EDE3; padding-bottom:18px; margin-bottom:26px;">
                    <span style="display:inline-block; font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; color:#F58220; background:#FFF4E6; padding:4px 12px; border-radius:20px; border:1px solid #FED7AA; margin-bottom:10px;">
                        Official Policy &bull; Consumer Protection
                    </span>
                    <h1 style="margin:0 0 8px 0; font-size:28px; font-weight:800; color:#7A1F3D;">Cancellation &amp; Refund Policy</h1>
                    <p style="margin:0; font-size:13.5px; color:#64748B;">
                        <strong>Effective Date:</strong> November 2025 &bull; <strong>Last Updated:</strong> November 2026<br>
                        <strong>Operated by:</strong> Merarashifal Technologies, Indirapuram, Ghaziabad, U.P., 201014
                    </p>
                </div>

                <div style="font-size:14px; color:#475569; margin-bottom:20px;">
                    <h2 style="font-size:17px; font-weight:700; color:#0F172A;">1. Digital &amp; Personalized Astrological Services</h2>
                    <p>MeraRashifal provides customized Vedic astrology reports, live AI consultations (Niyati AI), birth chart calculations, and digital subscriptions. Because astrological reports and AI credits are tailored mathematically to your exact personal birth coordinates and delivered electronically within 60 seconds of order confirmation, orders cannot be cancelled once generation has commenced.</p>
                </div>

                <div style="font-size:14px; color:#475569; margin-bottom:20px;">
                    <h2 style="font-size:17px; font-weight:700; color:#0F172A;">2. Duplicate or Failed Transactions</h2>
                    <p>In the event of a technical disruption where your account is debited multiple times for a single order, or payment succeeds but an internal server error prevents your digital report tokens or credits from being unlocked, a full 100% refund will be issued promptly.</p>
                </div>

                <div style="font-size:14px; color:#475569; margin-bottom:20px;">
                    <h2 style="font-size:17px; font-weight:700; color:#0F172A;">3. Refund Processing Timeline</h2>
                    <p>Approved refunds are processed via our authorized banking gateway (Razorpay) back to the original source method (UPI, Net Banking, Credit/Debit Card) within <strong>5 to 7 business days</strong> in compliance with RBI guidelines.</p>
                </div>

                <div style="font-size:14px; color:#475569; margin-bottom:20px;">
                    <h2 style="font-size:17px; font-weight:700; color:#0F172A;">4. Contact Helpdesk for Refund Requests</h2>
                    <p>To request assistance with an order, please email <a href="mailto:support@merarashifal.com" style="color:#2563EB;">support@merarashifal.com</a> with your Order ID, registered email address, and transaction receipt. Our support desk investigates and acknowledges all inquiries within 48 hours.</p>
                </div>
            </div>
        </div>';
    }
}
