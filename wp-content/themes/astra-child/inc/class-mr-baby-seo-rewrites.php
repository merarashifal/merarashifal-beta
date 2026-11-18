<?php
/**
 * MeraRashifal Baby Name Engine: Clean URL Taxonomy, SEO Canonicals & Sitemaps
 * Class: MR_Baby_SEO_Rewrites
 *
 * Implements clean religion hubs (/baby-names/hindu/, /baby-names/muslim/, etc.),
 * canonicalization of ?religion= parameters with noindex, follow directives,
 * and XML sitemap registration.
 *
 * @package MeraRashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Baby_SEO_Rewrites
{
    private static $instance = null;
    private $base_slug = 'baby-names';
    private $religions = ['hindu', 'muslim', 'christian', 'sikh'];

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register_religion_rewrites'], 5);
        add_action('init', [$this, 'register_sitemap_rewrites'], 10);
        add_filter('query_vars', [$this, 'register_query_vars']);
        add_filter('redirect_canonical', [$this, 'filter_sitemap_canonical'], 10, 1);
        add_action('template_redirect', [$this, 'handle_sitemap_request'], 5);

        // SEO Hooks for Yoast & WordPress Core
        add_filter('wpseo_canonical', [$this, 'filter_canonical'], 99);
        add_filter('wpseo_robots', [$this, 'filter_robots'], 99);
        add_filter('wpseo_title', [$this, 'filter_seo_title'], 99);
        add_filter('wpseo_metadesc', [$this, 'filter_seo_desc'], 99);

        // Sitemap integration filter
        add_filter('mr_baby_sitemap_urls', [$this, 'inject_religion_sitemap_urls']);

        // Dynamic Permalinks Flush to activate new clean routes safely on init
        add_action('init', function() {
            if (!get_option('mr_baby_religion_rewrite_flushed_v2')) {
                flush_rewrite_rules(false);
                update_option('mr_baby_religion_rewrite_flushed_v2', 1);
            }
        }, 99);
    }

    /**
     * Registers clean religion rewrite rules
     */
    public function register_religion_rewrites()
    {
        $rel_regex = '(hindu|muslim|christian|sikh)';

        // 1. Religion Hubs: /baby-names/hindu/
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_home&religion=$matches[1]',
            'top'
        );

        // 2. Religion Gender Home: /baby-names/hindu/(boy|girl)-names/
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/(boy|girl)-names/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_home&religion=$matches[1]&gender=$matches[2]',
            'top'
        );

        // 3. Religion Gender Hub: /baby-names/hindu/boy/
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/(boy|girl)/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_list&religion=$matches[1]&gender=$matches[2]',
            'top'
        );

        // 4. Paginated Religion Gender Hub: /baby-names/hindu/boy/page2
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/(boy|girl)/page([0-9]+)/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_list&religion=$matches[1]&gender=$matches[2]&paged=$matches[3]',
            'top'
        );

        // 5. Religion Alphabet Hub: /baby-names/hindu/t/
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/([a-z])/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=alphabet_hub&religion=$matches[1]&alphabet=$matches[2]',
            'top'
        );

        // 6. Religion Gender Alphabet Hub: /baby-names/hindu/boy/t/ or /baby-names/hindu/t/boy/
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/([a-z])/(boy|girl)/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_list&religion=$matches[1]&alphabet=$matches[2]&gender=$matches[3]',
            'top'
        );
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/(boy|girl)/([a-z])/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_list&religion=$matches[1]&gender=$matches[2]&alphabet=$matches[3]',
            'top'
        );

        // 7. Paginated Religion Alphabet Gender Hub: /baby-names/hindu/t/boy/page2
        add_rewrite_rule(
            '^' . $this->base_slug . '/' . $rel_regex . '/([a-z])/(boy|girl)/page([0-9]+)/?$',
            'index.php?pagename=' . $this->base_slug . '&baby_page=gender_list&religion=$matches[1]&alphabet=$matches[2]&gender=$matches[3]&paged=$matches[4]',
            'top'
        );
    }

    /**
     * Registers query variables
     */
    public function register_query_vars($vars)
    {
        $vars[] = 'baby_page';
        $vars[] = 'alphabet';
        $vars[] = 'gender';
        $vars[] = 'baby_name';
        $vars[] = 'religion';
        $vars[] = 'prefix';
        $vars[] = 'afs_map';
        $vars[] = 'afs_alpha';
        $vars[] = 'afs_gender';
        return $vars;
    }

    /**
     * Enforces Search Console Parameter Policy:
     * Canonicalizes ?religion= queries to clean hubs and detail pages
     */
    public function filter_canonical($canonical)
    {
        if (!$this->is_baby_request()) {
            return $canonical;
        }

        $religion = strtolower(trim((string)get_query_var('religion') ?: ($_GET['religion'] ?? '')));
        $gender   = strtolower(trim((string)get_query_var('gender')));
        $char     = strtolower(trim((string)get_query_var('alphabet')));
        $name     = strtolower(trim((string)get_query_var('baby_name')));

        // Detail Page: /baby-names/{char}/{gender}/{name}/
        if (!empty($name) && !empty($gender) && !empty($char)) {
            return home_url("/baby-names/{$char}/{$gender}/{$name}/");
        }

        // Clean Religion Gender Alphabet Hub: /baby-names/{religion}/{char}/{gender}/
        if (!empty($religion) && in_array($religion, $this->religions)) {
            if (!empty($char) && !empty($gender)) {
                return home_url("/baby-names/{$religion}/{$char}/{$gender}/");
            }
            if (!empty($gender)) {
                return home_url("/baby-names/{$religion}/{$gender}/");
            }
            return home_url("/baby-names/{$religion}/");
        }

        return $canonical;
    }

    /**
     * Enforces Parameter Policy: Marks dirty query parameters as "noindex, follow"
     */
    public function filter_robots($robots)
    {
        if (!$this->is_baby_request()) {
            return $robots;
        }

        // If URL contains dirty query parameters like ?religion=, mark noindex, follow
        if (isset($_GET['religion']) || isset($_GET['q'])) {
            return 'noindex, follow';
        }

        return $robots;
    }

    /**
     * Generates SEO title for Clean Religion Hubs
     */
    public function filter_seo_title($title)
    {
        if (!$this->is_baby_request()) {
            return $title;
        }

        $religion = strtolower(trim((string)get_query_var('religion')));
        $gender   = strtolower(trim((string)get_query_var('gender')));
        $char     = strtoupper(trim((string)get_query_var('alphabet')));

        if (!empty($religion) && in_array($religion, $this->religions)) {
            $rel_uc = ucfirst($religion);
            $gen_uc = !empty($gender) ? ucfirst($gender) : '';

            if (!empty($char) && !empty($gen_uc)) {
                return "{$rel_uc} {$gen_uc} Baby Names Starting with {$char} | Meanings & Astrology - MeraRashifal";
            }
            if (!empty($gen_uc)) {
                return "{$rel_uc} Baby {$gen_uc} Names (A-Z) | Modern & Traditional - MeraRashifal";
            }
            return "{$rel_uc} Baby Names (Boy & Girl) | Vedic, Modern & Classic - MeraRashifal";
        }

        return $title;
    }

    /**
     * Generates Meta Description for Clean Religion Hubs
     */
    public function filter_seo_desc($desc)
    {
        if (!$this->is_baby_request()) {
            return $desc;
        }

        $religion = strtolower(trim((string)get_query_var('religion')));
        $gender   = strtolower(trim((string)get_query_var('gender')));

        if (!empty($religion) && in_array($religion, $this->religions)) {
            $rel_uc = ucfirst($religion);
            $gen_uc = !empty($gender) ? ucfirst($gender) : 'Boy and Girl';
            return "Explore curated collection of {$rel_uc} {$gen_uc} baby names on MeraRashifal.com with authentic meanings, Rashi, Nakshatra, and Vedic numerology insights.";
        }

        return $desc;
    }

    /**
     * Appends clean religion hub URLs to XML sitemaps
     */
    public function inject_religion_sitemap_urls($urls = [])
    {
        if (!is_array($urls)) {
            $urls = [];
        }

        foreach ($this->religions as $rel) {
            $urls[] = "/baby-names/{$rel}/";
            $urls[] = "/baby-names/{$rel}/boy/";
            $urls[] = "/baby-names/{$rel}/girl/";

            // Register key alphabetical hubs for each religion
            foreach (range('a', 'z') as $char) {
                $urls[] = "/baby-names/{$rel}/{$char}/boy/";
                $urls[] = "/baby-names/{$rel}/{$char}/girl/";
            }
        }

        return $urls;
    }

    /**
     * Helper to detect baby names section requests
     */
    private function is_baby_request()
    {
        if (get_query_var('baby_page')) {
            return true;
        }
        if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/baby-names') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Registers XML sitemap rewrite rules for baby names
     */
    public function register_sitemap_rewrites()
    {
        add_rewrite_rule('^sitemaps/baby-names-nav\\.xml(?:\\.gz)?/?$', 'index.php?afs_map=baby-names-nav', 'top');
        add_rewrite_rule('^sitemaps/baby-names-([a-z])-(boy|girl)\\.xml(?:\\.gz)?/?$', 'index.php?afs_map=baby-names-names&afs_alpha=$matches[1]&afs_gender=$matches[2]', 'top');
    }

    /**
     * Prevents WordPress trailing slash canonical redirect on XML sitemaps
     */
    public function filter_sitemap_canonical($redirect_url)
    {
        if (get_query_var('afs_map')) {
            return false;
        }
        return $redirect_url;
    }

    /**
     * Handles sitemap requests if Snippet 59 has not processed them
     */
    public function handle_sitemap_request()
    {
        $map = get_query_var('afs_map');
        if (!$map || !in_array($map, ['baby-names-nav', 'baby-names-names'], true)) {
            return;
        }

        // Allow Snippet 59 to handle if defined
        if (function_exists('afs_baby_names_nav') && $map === 'baby-names-nav') {
            return;
        }
        if (function_exists('afs_baby_names_names') && $map === 'baby-names-names') {
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow');
        }

        if ($map === 'baby-names-nav') {
            echo $this->generate_nav_sitemap_xml();
            exit;
        } elseif ($map === 'baby-names-names') {
            echo $this->generate_names_sitemap_xml();
            exit;
        }
    }

    /**
     * Fallback generator for baby-names-nav.xml
     */
    private function generate_nav_sitemap_xml()
    {
        global $wpdb;
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $lastmod = gmdate('Y-m-d\TH:i:s+00:00');

        $urls = [
            '/baby-names/',
            '/baby-names/boy-names/',
            '/baby-names/girl-names/'
        ];

        foreach ($this->religions as $rel) {
            $urls[] = "/baby-names/{$rel}/";
            $urls[] = "/baby-names/{$rel}/boy/";
            $urls[] = "/baby-names/{$rel}/girl/";
        }

        foreach (range('a', 'z') as $char) {
            $urls[] = "/baby-names/{$char}/";
            $urls[] = "/baby-names/{$char}/boy/";
            $urls[] = "/baby-names/{$char}/girl/";
        }

        foreach ($urls as $u) {
            $priority = ($u === '/baby-names/') ? '1.0' : (($u === '/baby-names/boy-names/' || $u === '/baby-names/girl-names/') ? '0.9' : '0.8');
            $out .= "  <url>\n";
            $out .= "    <loc>" . esc_url(home_url($u)) . "</loc>\n";
            $out .= "    <lastmod>{$lastmod}</lastmod>\n";
            $out .= "    <changefreq>weekly</changefreq>\n";
            $out .= "    <priority>{$priority}</priority>\n";
            $out .= "  </url>\n";
        }

        // Add 2-letter prefixes
        $names_table = $wpdb->prefix . 'baby_names';
        $prefixes = $wpdb->get_results("SELECT DISTINCT SUBSTRING(name, 1, 2) as prefix, gender FROM {$names_table} WHERE LENGTH(name) >= 2");
        if ($prefixes) {
            foreach ($prefixes as $p) {
                $prefix = strtolower($p->prefix);
                $gender = strtolower($p->gender);
                $alpha  = substr($prefix, 0, 1);
                if (preg_match('/^[a-z]{2}$/', $prefix) && in_array($gender, ['boy', 'girl'], true)) {
                    $u = "/baby-names/{$alpha}/{$gender}/{$prefix}/";
                    $out .= "  <url>\n";
                    $out .= "    <loc>" . esc_url(home_url($u)) . "</loc>\n";
                    $out .= "    <lastmod>{$lastmod}</lastmod>\n";
                    $out .= "    <changefreq>monthly</changefreq>\n";
                    $out .= "    <priority>0.7</priority>\n";
                    $out .= "  </url>\n";
                }
            }
        }

        $out .= '</urlset>';
        return $out;
    }

    /**
     * Fallback generator for baby-names-{a-z}-{boy|girl}.xml
     */
    private function generate_names_sitemap_xml()
    {
        global $wpdb;
        $alpha  = sanitize_text_field(get_query_var('afs_alpha'));
        $gender = sanitize_text_field(get_query_var('afs_gender'));
        $gender_uc = ucfirst(strtolower($gender));
        $alpha_upper = strtoupper($alpha);

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $lastmod = gmdate('Y-m-d\TH:i:s+00:00');

        $names_table = $wpdb->prefix . 'baby_names';
        $names = $wpdb->get_results($wpdb->prepare(
            "SELECT name FROM {$names_table} WHERE starting_letter = %s AND gender = %s",
            $alpha_upper,
            $gender_uc
        ));

        if ($names) {
            foreach ($names as $n) {
                $name_lower = strtolower($n->name);
                $u = "/baby-names/{$alpha}/" . strtolower($gender) . "/{$name_lower}/";
                $out .= "  <url>\n";
                $out .= "    <loc>" . esc_url(home_url($u)) . "</loc>\n";
                $out .= "    <lastmod>{$lastmod}</lastmod>\n";
                $out .= "    <changefreq>monthly</changefreq>\n";
                $out .= "    <priority>0.6</priority>\n";
                $out .= "  </url>\n";
            }
        }

        $out .= '</urlset>';
        return $out;
    }

    public function get_supported_religions()
    {
        return $this->religions;
    }
}
