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
        add_filter('query_vars', [$this, 'register_query_vars']);

        // SEO Hooks for Yoast & WordPress Core
        add_filter('wpseo_canonical', [$this, 'filter_canonical'], 99);
        add_filter('wpseo_robots', [$this, 'filter_robots'], 99);
        add_filter('wpseo_title', [$this, 'filter_seo_title'], 99);
        add_filter('wpseo_metadesc', [$this, 'filter_seo_desc'], 99);

        // Sitemap integration filter
        add_filter('mr_baby_sitemap_urls', [$this, 'inject_religion_sitemap_urls']);

        // Dynamic Permalinks Flush to activate new clean routes
        if (!get_transient('mr_baby_religion_rewrite_flushed_v1')) {
            flush_rewrite_rules(false);
            set_transient('mr_baby_religion_rewrite_flushed_v1', 1, DAY_IN_SECONDS);
        }
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

    public function get_supported_religions()
    {
        return $this->religions;
    }
}
