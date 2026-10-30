<?php
/**
 * astrologer functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package astrologer
 */

if ( ! defined( 'ASTROLOGER_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( 'ASTROLOGER_VERSION', '1.0.9' );
}
if ( ! function_exists( 'astrologer_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function astrologer_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on astrologer, use a find and replace
		 * to change 'astrologer' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'astrologer', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );
          
		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		add_theme_support( "responsive-embeds" );
		add_theme_support( "wp-block-styles" );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
			'astrologer-header-menu' => esc_html__('Header Menu', 'astrologer' ),
			'astrologer-footer-menu' => esc_html__( 'Footer Menu', 'astrologer' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);
        

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'astrologer_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
		add_theme_support( "custom-header", array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			));
		add_theme_support( "custom-background", array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			));	
		/**
		 * Add support for editor styles.
		 */ 
        add_theme_support( 'editor-styles' );
        add_theme_support( 'dark-editor-style' );
        add_theme_support( 'align-wide' );
        add_theme_support( "custom-logo", array()); 
        add_theme_support( 'html5', array( 'search-form' ) );

       
        register_block_pattern(
	              'astrologer/contact-form',
            	array(
            		'title'         => '',
            		'viewportWidth' => '',
            		'categories'    => '',
            		'description'   => '',
            		'keywords'      => '',
            		'content'       => '',
            	)
            );
            
        register_block_style(
            'astrologer/contact-form',
                array(
                    'name'         => 'contact-form',
                    'label'        => __( 'Contact form', 'astrologer' ),
                    'is_default'   => true,
                    'inline_style' => '.wp-block-quote.is-style-blue-quote { color: blue; }',
                )
           );
        
        get_the_tag_list();
         
        /**
         * Enqueue editor styles.
         */
		add_editor_style('assets/css/style-editor.css');
	}
endif;
add_action( 'after_setup_theme', 'astrologer_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function astrologer_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'astrologer_content_width', 640 );
}
add_action( 'after_setup_theme', 'astrologer_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function astrologer_widgets_init() {
	
	register_sidebar(
		array(
			'name'          => esc_html__( 'Right Sidebar', 'astrologer' ),
			'id'            => 'right-sidebar',
			'description'   => esc_html__( 'Add Right widgets here.', 'astrologer' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
    register_sidebar(
		array(
			'name'          => esc_html__('Left Sidebar', 'astrologer' ),
			'id'            => 'left-sidebar',
			'description'   => esc_html__( 'Add Left widgets here.', 'astrologer' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	); 
	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1', 'astrologer' ),
		'id'            => 'footer-1',
		'description'   => esc_html__( 'Add Footer Col One Widgets Here.', 'astrologer' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2', 'astrologer' ),
		'id'            => 'footer-2',
		'description'   => esc_html__( 'Add Footer Col Two Widgets Here.', 'astrologer' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3', 'astrologer' ),
		'id'            => 'footer-3',
		'description'   => esc_html__( 'Add Footer Col Three Widgets Here.', 'astrologer' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4', 'astrologer' ),
		'id'            => 'footer-4',
		'description'   => esc_html__( 'Add Footer Col Four Widgets Here.', 'astrologer' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	
}
add_action( 'widgets_init', 'astrologer_widgets_init' );

/**
 * Enqueue scripts and styles.
 */ 
function astrologer_scripts() {
	$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
$theme_layout = '';
if(!empty($theme_option['theme_layout'])):
  $theme_layout = $theme_option['theme_layout'];
endif;
    // Styles File Enqueue
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
    wp_enqueue_style( 'astrologer-style', get_stylesheet_uri(), array(), ASTROLOGER_VERSION );
    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '1', 'all' );
    wp_enqueue_style( 'nice-select', get_template_directory_uri() . '/assets/css/nice-select.css', array(), '1', 'all' );
    wp_enqueue_style( 'font', get_template_directory_uri() . '/assets/css/fonts.css', array(), '1', 'all' );
    wp_enqueue_style( 'swiper-bundle', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', array(), '1', 'all' );
    wp_enqueue_style('fontawesome', get_template_directory_uri() . '/assets/css/fontawesome.min.css', array(), '1', 'all' );
	wp_enqueue_style('slick', get_template_directory_uri() . '/assets/css/slick.css', array(), '1', 'all' );
	wp_enqueue_style('magnific', get_template_directory_uri() . '/assets/css/magnific.css', array(), '1', 'all' );
	wp_enqueue_style('owl', get_template_directory_uri() . '/assets/css/owl.carousel.css', array(), '1', 'all' );
	wp_enqueue_style('animate', get_template_directory_uri() . '/assets/css/animate.min.css', array(), '1', 'all' );
	wp_enqueue_style('owltheme', get_template_directory_uri() . '/assets/css/owl.theme.default.css', array(), '1', 'all' );
    wp_enqueue_style( 'astrologer-custom-style', get_template_directory_uri() . '/assets/css/astrologer-custom-style.css', array(), '1', 'all');
	if($theme_layout == 'light'):
	wp_enqueue_style( 'astrologer-light-style', get_template_directory_uri() . '/assets/css/astrologer-custom-light-style.css', array(), '1', 'all');
    else:
        wp_dequeue_style( 'wp-block-library-theme' );
    endif;
	// Script File Enqueue   
	wp_enqueue_script( 'bootstrap-bundle', get_template_directory_uri().'/assets/js/bootstrap.bundle.min.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'smooth-scroll', get_template_directory_uri().'/assets/js/smooth-scroll.min.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'slickmin', get_template_directory_uri().'/assets/js/slick.min.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'owl', get_template_directory_uri().'/assets/js/owl.carousel.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'wow', get_template_directory_uri().'/assets/js/wow.min.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'maginific', get_template_directory_uri().'/assets/js/maginific.js',array('jquery'), '20151215', true);
    wp_enqueue_script( 'jquery-nice-select', get_template_directory_uri().'/assets/js/jquery.nice-select.min.js',array('jquery'), '20151215', true);
    wp_enqueue_script( 'swiper-bundle', get_template_directory_uri().'/assets/js/swiper-bundle.min.js',array('jquery'), '20151215', true);
	wp_enqueue_script( 'astrologer-custom-script', get_template_directory_uri().'/assets/js/astrologer-custom-script.js',array('jquery'), '20151215', true);
    wp_enqueue_script( 'astrologer-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), ASTROLOGER_VERSION, true );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'astrologer_scripts' );
 
/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';
 
/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}
/*Site health issue*/
if ( !session_id() ) {
    session_start([
        'read_and_close' => true,
    ]);
}

/**
 * astrologer Custom Functions
 */
require get_template_directory() . '/vendor/astrologer-function.php'; 



