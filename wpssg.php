<?php

/**
 * wpssg.php
 *
 * @package           WPSSG
 * @author            Leon Stafford <me@ljs.dev>
 * @license           The Unlicense
 * @link              https://unlicense.org
 */

declare(strict_types=1);

// TODO: temp changed for dev path
define('ABSPATH', __DIR__ . '/../');

//wp-config.php:
// TODO: temp Lokl creds
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', 'banana');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY', '');
define('SECURE_AUTH_KEY', '');
define('LOGGED_IN_KEY', '');
define('NONCE_KEY', '');
define('AUTH_SALT', '');
define('SECURE_AUTH_SALT', '');
define('LOGGED_IN_SALT', '');
define('NONCE_SALT', '');

// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
$table_prefix = 'wp_';

//wp-settings.php:
define('WPINC', 'wp-includes');
// rm the tinymce, required php, mysql checks
// ie https://github.com/WordPress/WordPress/search?q=required_php_version not used anywhere of relevance to us
// wp_version is used in a few places, ie Etag header, but really, shouldn't need it for generating site frontend,
// I'd remove it
// global $wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version,
// ... $wp_local_package;
// so we can ignore this: require ABSPATH . WPINC . '/version.php';
global $wp_version;

$wp_version = '5.8';

// make a decision on language support and load the DB. a lot of this file is unneeded utility code.

// review load.php further, then continue with wp-settings.php review from line 34
// wpincludes/load.php

// force 1.0
function wp_get_server_protocol() : string {
    return 'HTTP/1.0';
}

$_SERVER['SERVER_SOFTWARE'] = 'leonstafford/wpssg';

// TODO: change this as needed
$_SERVER['REQUEST_URI'] = '/';

// not relevant for Lokl / CLI usage... but may bring back in for protected VPS usage
function wp_populate_basic_auth_from_authorization_header() : void {
}

// not required
function wp_check_php_mysql_versions() : void {
}

function wp_get_environment_type() {
    return 'production';
}

function wp_maintenance() : void {
    return;
}

function wp_is_maintenance_mode() : bool {
    return false;
}

function timer_float() : float {
    return microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'];
}

// wonder why the useless return value...
function timer_start() : bool {
    global $timestart;
    $timestart = microtime( true );
    return true;
}

function timer_stop( int $display = 0, int $precision = 3 ) : string {
    global $timestart, $timeend;
    $timeend   = microtime( true );
    $timetotal = $timeend - $timestart;
    $r         = ( function_exists( 'number_format_i18n' ) ) ? number_format_i18n( $timetotal, $precision ) : number_format( $timetotal, $precision );
    if ( $display ) {
        echo $r;
    }
    return $r;
}

// no debug mode
function wp_debug_mode() : void {
}

// can probably hardcode these, skip the legacy one
/**
 * Set the location of the language directory.
 *
 * To set directory manually, define the `WP_LANG_DIR` constant
 * in wp-config.php.
 *
 * If the language directory exists within `WP_CONTENT_DIR`, it
 * is used. Otherwise the language directory is assumed to live
 * in `WPINC`.
 *
 * @since 3.0.0
 * @access private
 */
function wp_set_lang_dir() {
    if ( ! defined( 'WP_LANG_DIR' ) ) {
        if ( file_exists( WP_CONTENT_DIR . '/languages' ) && @is_dir( WP_CONTENT_DIR . '/languages' ) || ! @is_dir( ABSPATH . WPINC . '/languages' ) ) {
            /**
             * Server path of the language directory.
             *
             * No leading slash, no trailing slash, full path, not relative to ABSPATH
             *
             * @since 2.1.0
             */
            define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
            if ( ! defined( 'LANGDIR' ) ) {
                // Old static relative path maintained for limited backward compatibility - won't work in some cases.
                define( 'LANGDIR', 'wp-content/languages' );
            }
        } else {
            /**
             * Server path of the language directory.
             *
             * No leading slash, no trailing slash, full path, not relative to `ABSPATH`.
             *
             * @since 2.1.0
             */
            define( 'WP_LANG_DIR', ABSPATH . WPINC . '/languages' );
            if ( ! defined( 'LANGDIR' ) ) {
                // Old relative path maintained for backward compatibility.
                define( 'LANGDIR', WPINC . '/languages' );
            }
        }
    }
}

function require_wp_db() : void {
    global $wpdb;

    require_once ABSPATH . WPINC . '/wp-db.php';
    if ( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
        require_once WP_CONTENT_DIR . '/db.php';
    }

    if ( isset( $wpdb ) ) {
        return;
    }

    $dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
    $dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
    $dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
    $dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';

    $wpdb = new wpdb( $dbuser, $dbpassword, $dbname, $dbhost );
}

/**
 * Set the database table prefix and the format specifiers for database
 * table columns.
 *
 * Columns not listed here default to `%s`.
 *
 * @since 3.0.0
 * @access private
 *
 * @global wpdb   $wpdb         WordPress database abstraction object.
 * @global string $table_prefix The database table prefix.
 */
function wp_set_wpdb_vars() {
    global $wpdb, $table_prefix;
    if ( ! empty( $wpdb->error ) ) {
        dead_db();
    }

    $wpdb->field_types = array(
        'post_author'      => '%d',
        'post_parent'      => '%d',
        'menu_order'       => '%d',
        'term_id'          => '%d',
        'term_group'       => '%d',
        'term_taxonomy_id' => '%d',
        'parent'           => '%d',
        'count'            => '%d',
        'object_id'        => '%d',
        'term_order'       => '%d',
        'ID'               => '%d',
        'comment_ID'       => '%d',
        'comment_post_ID'  => '%d',
        'comment_parent'   => '%d',
        'user_id'          => '%d',
        'link_id'          => '%d',
        'link_owner'       => '%d',
        'link_rating'      => '%d',
        'option_id'        => '%d',
        'blog_id'          => '%d',
        'meta_id'          => '%d',
        'post_id'          => '%d',
        'user_status'      => '%d',
        'umeta_id'         => '%d',
        'comment_karma'    => '%d',
        'comment_count'    => '%d',
        // Multisite:
        'active'           => '%d',
        'cat_id'           => '%d',
        'deleted'          => '%d',
        'lang_id'          => '%d',
        'mature'           => '%d',
        'public'           => '%d',
        'site_id'          => '%d',
        'spam'             => '%d',
    );

    $prefix = $wpdb->set_prefix( $table_prefix );

    if ( is_wp_error( $prefix ) ) {
        wp_load_translations_early();
        wp_die(
            sprintf(
                /* translators: 1: $table_prefix, 2: wp-config.php */
                __( '<strong>Error</strong>: %1$s in %2$s can only contain numbers, letters, and underscores.' ),
                '<code>$table_prefix</code>',
                '<code>wp-config.php</code>'
            )
        );
    }
}

/**
 * Toggle `$_wp_using_ext_object_cache` on and off without directly
 * touching global.
 *
 * @since 3.7.0
 *
 * @global bool $_wp_using_ext_object_cache
 *
 * @param bool $using Whether external object cache is being used.
 * @return bool The current 'using' setting.
 */
function wp_using_ext_object_cache( $using = null ) {
    global $_wp_using_ext_object_cache;
    $current_using = $_wp_using_ext_object_cache;
    if ( null !== $using ) {
        $_wp_using_ext_object_cache = $using;
    }
    return $current_using;
}

/**
 * Start the WordPress object cache.
 *
 * If an object-cache.php file exists in the wp-content directory,
 * it uses that drop-in as an external object cache.
 *
 * @since 3.0.0
 * @access private
 *
 * @global array $wp_filter Stores all of the filters.
 */
function wp_start_object_cache() {
    global $wp_filter;
    static $first_init = true;

    // Only perform the following checks once.

    /**
     * Filters whether to enable loading of the object-cache.php drop-in.
     *
     * This filter runs before it can be used by plugins. It is designed for non-web
     * run-times. If false is returned, object-cache.php will never be loaded.
     *
     * @since 5.8.0
     *
     * @param bool $enable_object_cache Whether to enable loading object-cache.php (if present).
     *                                  Default true.
     */
    if ( $first_init && apply_filters( 'enable_loading_object_cache_dropin', true ) ) {
        if ( ! function_exists( 'wp_cache_init' ) ) {
            /*
             * This is the normal situation. First-run of this function. No
             * caching backend has been loaded.
             *
             * We try to load a custom caching backend, and then, if it
             * results in a wp_cache_init() function existing, we note
             * that an external object cache is being used.
             */
            if ( file_exists( WP_CONTENT_DIR . '/object-cache.php' ) ) {
                require_once WP_CONTENT_DIR . '/object-cache.php';
                if ( function_exists( 'wp_cache_init' ) ) {
                    wp_using_ext_object_cache( true );
                }

                // Re-initialize any hooks added manually by object-cache.php.
                if ( $wp_filter ) {
                    $wp_filter = WP_Hook::build_preinitialized_hooks( $wp_filter );
                }
            }
        } elseif ( ! wp_using_ext_object_cache() && file_exists( WP_CONTENT_DIR . '/object-cache.php' ) ) {
            /*
             * Sometimes advanced-cache.php can load object-cache.php before
             * this function is run. This breaks the function_exists() check
             * above and can result in wp_using_ext_object_cache() returning
             * false when actually an external cache is in use.
             */
            wp_using_ext_object_cache( true );
        }
    }

    if ( ! wp_using_ext_object_cache() ) {
        require_once ABSPATH . WPINC . '/cache.php';
    }

    require_once ABSPATH . WPINC . '/cache-compat.php';

    /*
     * If cache supports reset, reset instead of init if already
     * initialized. Reset signals to the cache that global IDs
     * have changed and it may need to update keys and cleanup caches.
     */
    if ( ! $first_init && function_exists( 'wp_cache_switch_to_blog' ) ) {
        wp_cache_switch_to_blog( get_current_blog_id() );
    } elseif ( function_exists( 'wp_cache_init' ) ) {
        wp_cache_init();
    }

    if ( function_exists( 'wp_cache_add_global_groups' ) ) {
        wp_cache_add_global_groups( array( 'users', 'userlogins', 'usermeta', 'user_meta', 'useremail', 'userslugs', 'site-transient', 'site-options', 'blog-lookup', 'blog-details', 'site-details', 'rss', 'global-posts', 'blog-id-cache', 'networks', 'sites', 'blog_meta' ) );
        wp_cache_add_non_persistent_groups( array( 'counts', 'plugins' ) );
    }

    $first_init = false;
}

// irrelevant
function wp_not_installed() : void {
}

/**
 * Retrieve an array of must-use plugin files.
 *
 * The default directory is wp-content/mu-plugins. To change the default
 * directory manually, define `WPMU_PLUGIN_DIR` and `WPMU_PLUGIN_URL`
 * in wp-config.php.
 *
 * @since 3.0.0
 * @access private
 *
 * @return string[] Array of absolute paths of files to include.
 */
function wp_get_mu_plugins() {
    $mu_plugins = array();
    if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
        return $mu_plugins;
    }
    $dh = opendir( WPMU_PLUGIN_DIR );
    if ( ! $dh ) {
        return $mu_plugins;
    }
    while ( ( $plugin = readdir( $dh ) ) !== false ) {
        if ( '.php' === substr( $plugin, -4 ) ) {
            $mu_plugins[] = WPMU_PLUGIN_DIR . '/' . $plugin;
        }
    }
    closedir( $dh );
    sort( $mu_plugins );

    return $mu_plugins;
}

// needed for asset detection
function wp_get_active_and_valid_plugins() : array {
    $plugins        = array();
    $active_plugins = (array) get_option( 'active_plugins', array() );

    // Check for hacks file if the option is enabled.
    if ( get_option( 'hack_file' ) && file_exists( ABSPATH . 'my-hacks.php' ) ) {
        _deprecated_file( 'my-hacks.php', '1.5.0' );
        array_unshift( $plugins, ABSPATH . 'my-hacks.php' );
    }

    if ( empty( $active_plugins ) || wp_installing() ) {
        return $plugins;
    }

    $network_plugins = is_multisite() ? wp_get_active_network_plugins() : false;

    foreach ( $active_plugins as $plugin ) {
        if ( ! validate_file( $plugin )                     // $plugin must validate as file.
            && '.php' === substr( $plugin, -4 )             // $plugin must end with '.php'.
            && file_exists( WP_PLUGIN_DIR . '/' . $plugin ) // $plugin must exist.
            // Not already included as a network plugin.
            && ( ! $network_plugins || ! in_array( WP_PLUGIN_DIR . '/' . $plugin, $network_plugins, true ) )
            ) {
            $plugins[] = WP_PLUGIN_DIR . '/' . $plugin;
        }
    }

    /*
     * Remove plugins from the list of active plugins when we're on an endpoint
     * that should be protected against WSODs and the plugin is paused.
     */
    if ( wp_is_recovery_mode() ) {
        $plugins = wp_skip_paused_plugins( $plugins );
    }

    return $plugins;
}

/**
 * Filters a given list of plugins, removing any paused plugins from it.
 *
 * @since 5.2.0
 *
 * @param string[] $plugins Array of absolute plugin main file paths.
 * @return string[] Filtered array of plugins, without any paused plugins.
 */
function wp_skip_paused_plugins( array $plugins ) {
    $paused_plugins = wp_paused_plugins()->get_all();

    if ( empty( $paused_plugins ) ) {
        return $plugins;
    }

    foreach ( $plugins as $index => $plugin ) {
        list( $plugin ) = explode( '/', plugin_basename( $plugin ) );

        if ( array_key_exists( $plugin, $paused_plugins ) ) {
            unset( $plugins[ $index ] );

            // Store list of paused plugins for displaying an admin notice.
            $GLOBALS['_paused_plugins'][ $plugin ] = $paused_plugins[ $plugin ];
        }
    }

    return $plugins;
}

// may be needed for asset detection
function wp_get_active_and_valid_themes() : array {
    global $pagenow;

    $themes = array();

    if ( wp_installing() && 'wp-activate.php' !== $pagenow ) {
        return $themes;
    }

    if ( TEMPLATEPATH !== STYLESHEETPATH ) {
        $themes[] = STYLESHEETPATH;
    }

    $themes[] = TEMPLATEPATH;

    /*
     * Remove themes from the list of active themes when we're on an endpoint
     * that should be protected against WSODs and the theme is paused.
     */
    if ( wp_is_recovery_mode() ) {
        $themes = wp_skip_paused_themes( $themes );

        // If no active and valid themes exist, skip loading themes.
        if ( empty( $themes ) ) {
            add_filter( 'wp_using_themes', '__return_false' );
        }
    }

    return $themes;
}

// might be useful
/**
 * Filters a given list of themes, removing any paused themes from it.
 *
 * @since 5.2.0
 *
 * @param string[] $themes Array of absolute theme directory paths.
 * @return string[] Filtered array of absolute paths to themes, without any paused themes.
 */
function wp_skip_paused_themes( array $themes ) {
    $paused_themes = wp_paused_themes()->get_all();

    if ( empty( $paused_themes ) ) {
        return $themes;
    }

    foreach ( $themes as $index => $theme ) {
        $theme = basename( $theme );

        if ( array_key_exists( $theme, $paused_themes ) ) {
            unset( $themes[ $index ] );

            // Store list of paused themes for displaying an admin notice.
            $GLOBALS['_paused_themes'][ $theme ] = $paused_themes[ $theme ];
        }
    }

    return $themes;
}

function wp_is_recovery_mode() {
    return false;
}

// not relevant
function is_protected_endpoint() {
    return false;
}

// no ajax for SSG
function is_protected_ajax_action() {
        return false;
}

// utf8 all the things
function wp_set_internal_encoding() : void {
    mb_internal_encoding( 'UTF-8' );
}

// add_magic_quotes from inc/functions.php
function wp_magic_quotes() : void {
    $_GET    = add_magic_quotes( $_GET );
    $_POST   = add_magic_quotes( $_POST );
    $_COOKIE = add_magic_quotes( $_COOKIE );
    $_SERVER = add_magic_quotes( $_SERVER );
    $_REQUEST = array_merge( $_GET, $_POST );
}

function shutdown_action_hook() : void {
    do_action( 'shutdown' );

    wp_cache_close();
}

// rm PHP 4!!! compat code
function wp_clone( mixed $object ) : mixed {
    return clone $object ;
}

// never admin UI
function is_admin() {
    return false;
}

// never admin UI
function is_blog_admin() {
    return false;
}

// never multisite
function is_network_admin() : bool{
    return false;
}

// never generating admin views
function is_user_admin() : bool {
    return false;
}

function is_multisite() : bool {
    return false;
}

function get_current_blog_id() : int {
    return 1;
}

// always single site
function get_current_network_id() : int {
    return 1;
}

// let's try and get rid of all/most of this
/**
 * Attempt an early load of translations.
 *
 * Used for errors encountered during the initial loading process, before
 * the locale has been properly detected and loaded.
 *
 * Designed for unusual load sequences (like setup-config.php) or for when
 * the script will then terminate with an error, otherwise there is a risk
 * that a file can be double-included.
 *
 * @since 3.4.0
 * @access private
 *
 * @global WP_Locale $wp_locale WordPress date and time locale object.
 */
function wp_load_translations_early() {
    global $wp_locale;

    static $loaded = false;
    if ( $loaded ) {
        return;
    }
    $loaded = true;

    if ( function_exists( 'did_action' ) && did_action( 'init' ) ) {
        return;
    }

    // We need $wp_local_package.
    require ABSPATH . WPINC . '/version.php';

    // Translation and localization.
    require_once ABSPATH . WPINC . '/pomo/mo.php';
    require_once ABSPATH . WPINC . '/l10n.php';
    require_once ABSPATH . WPINC . '/class-wp-locale.php';
    require_once ABSPATH . WPINC . '/class-wp-locale-switcher.php';

    // General libraries.
    require_once ABSPATH . WPINC . '/plugin.php';

    $locales   = array();
    $locations = array();

    while ( true ) {
        if ( defined( 'WPLANG' ) ) {
            if ( '' === WPLANG ) {
                break;
            }
            $locales[] = WPLANG;
        }

        if ( isset( $wp_local_package ) ) {
            $locales[] = $wp_local_package;
        }

        if ( ! $locales ) {
            break;
        }

        if ( defined( 'WP_LANG_DIR' ) && @is_dir( WP_LANG_DIR ) ) {
            $locations[] = WP_LANG_DIR;
        }

        if ( defined( 'WP_CONTENT_DIR' ) && @is_dir( WP_CONTENT_DIR . '/languages' ) ) {
            $locations[] = WP_CONTENT_DIR . '/languages';
        }

        if ( @is_dir( ABSPATH . 'wp-content/languages' ) ) {
            $locations[] = ABSPATH . 'wp-content/languages';
        }

        if ( @is_dir( ABSPATH . WPINC . '/languages' ) ) {
            $locations[] = ABSPATH . WPINC . '/languages';
        }

        if ( ! $locations ) {
            break;
        }

        $locations = array_unique( $locations );

        foreach ( $locales as $locale ) {
            foreach ( $locations as $location ) {
                if ( file_exists( $location . '/' . $locale . '.mo' ) ) {
                    load_textdomain( 'default', $location . '/' . $locale . '.mo' );
                    if ( defined( 'WP_SETUP_CONFIG' ) && file_exists( $location . '/admin-' . $locale . '.mo' ) ) {
                        load_textdomain( 'default', $location . '/admin-' . $locale . '.mo' );
                    }
                    break 2;
                }
            }
        }

        break;
    }

    $wp_locale = new WP_Locale();
}

// never installing
function wp_installing( $is_installing = null ) : bool {
    return false;
}

// SSG doesn't need TLS
function is_ssl() {
    return false;
}

// check if used outside of ini settings, which we don't need
function wp_convert_hr_to_bytes( string $value ) : int {
    $value = strtolower( trim( $value ) );
    $bytes = (int) $value;

    if ( false !== strpos( $value, 'g' ) ) {
        $bytes *= GB_IN_BYTES;
    } elseif ( false !== strpos( $value, 'm' ) ) {
        $bytes *= MB_IN_BYTES;
    } elseif ( false !== strpos( $value, 'k' ) ) {
        $bytes *= KB_IN_BYTES;
    }

    // Deal with large (float) values which run into the maximum integer size.
    return min( $bytes, PHP_INT_MAX );
}

function wp_is_ini_value_changeable( string $setting ) {
    return false;
}

function wp_doing_ajax() : bool {
    return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
}

function wp_using_themes() : bool {
    return apply_filters( 'wp_using_themes', defined( 'WP_USE_THEMES' ) && WP_USE_THEMES );
}

function wp_doing_cron() : bool {
    return apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
}

function is_wp_error( mixed $thing ) : bool {
    $is_wp_error = ( $thing instanceof WP_Error );

    if ( $is_wp_error ) {
        do_action( 'is_wp_error_instance', $thing );
    }

    return $is_wp_error;
}

function wp_is_file_mod_allowed( string $context ) : bool {
    return apply_filters( 'file_mod_allowed', false, '' );
}

function wp_start_scraping_edited_file_errors() : void {
    return;
}

// ?
function wp_finalize_scraping_edited_file_errors( $scrape_key ) : void {
}

// let's not deal with any generated JSON
function wp_is_json_request() : bool {
    return false;
}

function wp_is_jsonp_request() : bool {
    return false;
}

function wp_is_json_media_type( string $media_type ) : bool {
    return false;
}

// may use this for feeds, sitemap, but probably not...
function wp_is_xml_request() {
    $accepted = array(
        'text/xml',
        'application/rss+xml',
        'application/atom+xml',
        'application/rdf+xml',
        'text/xml+oembed',
        'application/xml+oembed',
    );

    if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {
        foreach ( $accepted as $type ) {
            if ( false !== strpos( $_SERVER['HTTP_ACCEPT'], $type ) ) {
                return true;
            }
        }
    }

    if ( isset( $_SERVER['CONTENT_TYPE'] ) && in_array( $_SERVER['CONTENT_TYPE'], $accepted, true ) ) {
        return true;
    }

    return false;
}

function wp_is_site_protected_by_basic_auth( $context = '' ) : mixed {
    return apply_filters( 'wp_is_site_protected_by_basic_auth', false, 'front' );
}
// end load.php, continue settings.php

// TODO: override these
// Include files required for initialization.
require ABSPATH . WPINC . '/class-wp-paused-extensions-storage.php';
require ABSPATH . WPINC . '/class-wp-fatal-error-handler.php';
require ABSPATH . WPINC . '/class-wp-recovery-mode-cookie-service.php';
require ABSPATH . WPINC . '/class-wp-recovery-mode-key-service.php';
require ABSPATH . WPINC . '/class-wp-recovery-mode-link-service.php';
require ABSPATH . WPINC . '/class-wp-recovery-mode-email-service.php';
require ABSPATH . WPINC . '/class-wp-recovery-mode.php';
require ABSPATH . WPINC . '/error-protection.php';
require ABSPATH . WPINC . '/default-constants.php';
require_once ABSPATH . WPINC . '/plugin.php';

global $blog_id;

wp_initial_constants();

wp_register_fatal_error_handler();

// WordPress calculates offsets from UTC.
// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
date_default_timezone_set( 'UTC' );

/**
 * Filters whether to enable loading of the advanced-cache.php drop-in.
 *
 * This filter runs before it can be used by plugins. It is designed for non-web
 * run-times. If false is returned, advanced-cache.php will never be loaded.
 *
 * @since 4.6.0
 *
 * @param bool $enable_advanced_cache Whether to enable loading advanced-cache.php (if present).
 *                                    Default true.
 */
if ( WP_CACHE && apply_filters( 'enable_loading_advanced_cache_dropin', true ) && file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
    // For an advanced caching plugin to use. Uses a static drop-in because you would only want one.
    include WP_CONTENT_DIR . '/advanced-cache.php';

    // Re-initialize any hooks added manually by advanced-cache.php.
    if ( $wp_filter ) {
        $wp_filter = WP_Hook::build_preinitialized_hooks( $wp_filter );
    }
}

// Define WP_LANG_DIR if not set.
wp_set_lang_dir();

// Load early WordPress files.
require ABSPATH . WPINC . '/compat.php';
require ABSPATH . WPINC . '/class-wp-list-util.php';
require ABSPATH . WPINC . '/formatting.php';
require ABSPATH . WPINC . '/meta.php';
require ABSPATH . WPINC . '/functions.php';
require ABSPATH . WPINC . '/class-wp-meta-query.php';
require ABSPATH . WPINC . '/class-wp-matchesmapregex.php';
require ABSPATH . WPINC . '/class-wp.php';
require ABSPATH . WPINC . '/class-wp-error.php';
require ABSPATH . WPINC . '/pomo/mo.php';

/**
 * @global wpdb $wpdb WordPress database abstraction object.
 * @since 0.71
 */
global $wpdb;
// Include the wpdb class and, if present, a db.php database drop-in.
require_wp_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
wp_set_wpdb_vars();

// Start the WordPress object cache, or an external object cache if the drop-in is present.
wp_start_object_cache();

// Attach the default filters.
require ABSPATH . WPINC . '/default-filters.php';

// Initialize multisite if enabled.
if ( is_multisite() ) {
    require ABSPATH . WPINC . '/class-wp-site-query.php';
    require ABSPATH . WPINC . '/class-wp-network-query.php';
    require ABSPATH . WPINC . '/ms-blogs.php';
    require ABSPATH . WPINC . '/ms-settings.php';
} elseif ( ! defined( 'MULTISITE' ) ) {
    define( 'MULTISITE', false );
}

register_shutdown_function( 'shutdown_action_hook' );

// Stop most of WordPress from being loaded if we just want the basics.
if ( SHORTINIT ) {
    return false;
}

// Load the L10n library.
require_once ABSPATH . WPINC . '/l10n.php';
require_once ABSPATH . WPINC . '/class-wp-locale.php';
require_once ABSPATH . WPINC . '/class-wp-locale-switcher.php';

// Run the installer if WordPress is not installed.
wp_not_installed();

// Load most of WordPress.
require ABSPATH . WPINC . '/class-wp-walker.php';
require ABSPATH . WPINC . '/class-wp-ajax-response.php';
require ABSPATH . WPINC . '/capabilities.php';
require ABSPATH . WPINC . '/class-wp-roles.php';
require ABSPATH . WPINC . '/class-wp-role.php';
require ABSPATH . WPINC . '/class-wp-user.php';
require ABSPATH . WPINC . '/class-wp-query.php';
require ABSPATH . WPINC . '/query.php';
require ABSPATH . WPINC . '/class-wp-date-query.php';
require ABSPATH . WPINC . '/theme.php';
require ABSPATH . WPINC . '/class-wp-theme.php';
require ABSPATH . WPINC . '/class-wp-theme-json.php';
require ABSPATH . WPINC . '/class-wp-theme-json-resolver.php';
require ABSPATH . WPINC . '/class-wp-block-template.php';
require ABSPATH . WPINC . '/block-template-utils.php';
require ABSPATH . WPINC . '/block-template.php';
require ABSPATH . WPINC . '/theme-templates.php';
require ABSPATH . WPINC . '/template.php';
require ABSPATH . WPINC . '/https-detection.php';
require ABSPATH . WPINC . '/https-migration.php';
require ABSPATH . WPINC . '/class-wp-user-request.php';
require ABSPATH . WPINC . '/user.php';
require ABSPATH . WPINC . '/class-wp-user-query.php';
require ABSPATH . WPINC . '/class-wp-session-tokens.php';
require ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php';
require ABSPATH . WPINC . '/class-wp-metadata-lazyloader.php';
require ABSPATH . WPINC . '/general-template.php';
require ABSPATH . WPINC . '/link-template.php';
require ABSPATH . WPINC . '/author-template.php';
require ABSPATH . WPINC . '/robots-template.php';
require ABSPATH . WPINC . '/post.php';
require ABSPATH . WPINC . '/class-walker-page.php';
require ABSPATH . WPINC . '/class-walker-page-dropdown.php';
require ABSPATH . WPINC . '/class-wp-post-type.php';
require ABSPATH . WPINC . '/class-wp-post.php';
require ABSPATH . WPINC . '/post-template.php';
require ABSPATH . WPINC . '/revision.php';
require ABSPATH . WPINC . '/post-formats.php';
require ABSPATH . WPINC . '/post-thumbnail-template.php';
require ABSPATH . WPINC . '/category.php';
require ABSPATH . WPINC . '/class-walker-category.php';
require ABSPATH . WPINC . '/class-walker-category-dropdown.php';
require ABSPATH . WPINC . '/category-template.php';
require ABSPATH . WPINC . '/comment.php';
require ABSPATH . WPINC . '/class-wp-comment.php';
require ABSPATH . WPINC . '/class-wp-comment-query.php';
require ABSPATH . WPINC . '/class-walker-comment.php';
require ABSPATH . WPINC . '/comment-template.php';
require ABSPATH . WPINC . '/rewrite.php';
require ABSPATH . WPINC . '/class-wp-rewrite.php';
require ABSPATH . WPINC . '/feed.php';
require ABSPATH . WPINC . '/bookmark.php';
require ABSPATH . WPINC . '/bookmark-template.php';
require ABSPATH . WPINC . '/kses.php';
require ABSPATH . WPINC . '/cron.php';
require ABSPATH . WPINC . '/deprecated.php';
require ABSPATH . WPINC . '/script-loader.php';
require ABSPATH . WPINC . '/taxonomy.php';
require ABSPATH . WPINC . '/class-wp-taxonomy.php';
require ABSPATH . WPINC . '/class-wp-term.php';
require ABSPATH . WPINC . '/class-wp-term-query.php';
require ABSPATH . WPINC . '/class-wp-tax-query.php';
require ABSPATH . WPINC . '/update.php';
require ABSPATH . WPINC . '/canonical.php';
require ABSPATH . WPINC . '/shortcodes.php';
require ABSPATH . WPINC . '/embed.php';
require ABSPATH . WPINC . '/class-wp-embed.php';
require ABSPATH . WPINC . '/class-wp-oembed.php';
require ABSPATH . WPINC . '/class-wp-oembed-controller.php';
require ABSPATH . WPINC . '/media.php';
require ABSPATH . WPINC . '/http.php';
require ABSPATH . WPINC . '/class-http.php';
require ABSPATH . WPINC . '/class-wp-http-streams.php';
require ABSPATH . WPINC . '/class-wp-http-curl.php';
require ABSPATH . WPINC . '/class-wp-http-proxy.php';
require ABSPATH . WPINC . '/class-wp-http-cookie.php';
require ABSPATH . WPINC . '/class-wp-http-encoding.php';
require ABSPATH . WPINC . '/class-wp-http-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-hooks.php';
require ABSPATH . WPINC . '/widgets.php';
require ABSPATH . WPINC . '/class-wp-widget.php';
require ABSPATH . WPINC . '/class-wp-widget-factory.php';
require ABSPATH . WPINC . '/nav-menu.php';
require ABSPATH . WPINC . '/nav-menu-template.php';
require ABSPATH . WPINC . '/admin-bar.php';
require ABSPATH . WPINC . '/class-wp-application-passwords.php';

// override some REST functions being called
function rest_default_additional_properties_to_false() : array {
    return [];
}

function rest_cookie_collect_status() : void {
}

function get_rest_url( ?int $blog_id = null, string $path = '/', string $scheme = 'rest' ) : string {
    return 'resturl';
}

function rest_api_init() : void {
}

require ABSPATH . WPINC . '/sitemaps.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-index.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-provider.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-registry.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-renderer.php';
require ABSPATH . WPINC . '/sitemaps/class-wp-sitemaps-stylesheet.php';
require ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-posts.php';
require ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-taxonomies.php';
require ABSPATH . WPINC . '/sitemaps/providers/class-wp-sitemaps-users.php';
require ABSPATH . WPINC . '/class-wp-block-editor-context.php';
require ABSPATH . WPINC . '/class-wp-block-type.php';
require ABSPATH . WPINC . '/class-wp-block-pattern-categories-registry.php';
require ABSPATH . WPINC . '/class-wp-block-patterns-registry.php';
require ABSPATH . WPINC . '/class-wp-block-styles-registry.php';
require ABSPATH . WPINC . '/class-wp-block-type-registry.php';
require ABSPATH . WPINC . '/class-wp-block.php';
require ABSPATH . WPINC . '/class-wp-block-list.php';
require ABSPATH . WPINC . '/class-wp-block-parser.php';
require ABSPATH . WPINC . '/blocks.php';
require ABSPATH . WPINC . '/blocks/index.php';
require ABSPATH . WPINC . '/block-editor.php';
require ABSPATH . WPINC . '/block-patterns.php';
require ABSPATH . WPINC . '/class-wp-block-supports.php';
require ABSPATH . WPINC . '/block-supports/align.php';
require ABSPATH . WPINC . '/block-supports/border.php';
require ABSPATH . WPINC . '/block-supports/colors.php';
require ABSPATH . WPINC . '/block-supports/custom-classname.php';
require ABSPATH . WPINC . '/block-supports/duotone.php';
require ABSPATH . WPINC . '/block-supports/elements.php';
require ABSPATH . WPINC . '/block-supports/generated-classname.php';
require ABSPATH . WPINC . '/block-supports/layout.php';
require ABSPATH . WPINC . '/block-supports/spacing.php';
require ABSPATH . WPINC . '/block-supports/typography.php';

$GLOBALS['wp_embed'] = new WP_Embed();

// Load multisite-specific files.
if ( is_multisite() ) {
    require ABSPATH . WPINC . '/ms-functions.php';
    require ABSPATH . WPINC . '/ms-default-filters.php';
    require ABSPATH . WPINC . '/ms-deprecated.php';
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
wp_plugin_directory_constants();

$GLOBALS['wp_plugin_paths'] = array();

// Load must-use plugins.
foreach ( wp_get_mu_plugins() as $mu_plugin ) {
    include_once $mu_plugin;

    /**
     * Fires once a single must-use plugin has loaded.
     *
     * @since 5.1.0
     *
     * @param string $mu_plugin Full path to the plugin's main file.
     */
    do_action( 'mu_plugin_loaded', $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
    foreach ( wp_get_active_network_plugins() as $network_plugin ) {
        wp_register_plugin_realpath( $network_plugin );
        include_once $network_plugin;

        /**
         * Fires once a single network-activated plugin has loaded.
         *
         * @since 5.1.0
         *
         * @param string $network_plugin Full path to the plugin's main file.
         */
        do_action( 'network_plugin_loaded', $network_plugin );
    }
    unset( $network_plugin );
}

/**
 * Fires once all must-use and network-activated plugins have loaded.
 *
 * @since 2.8.0
 */
do_action( 'muplugins_loaded' );

if ( is_multisite() ) {
    ms_cookie_constants();
}

// Define constants after multisite is loaded.
wp_cookie_constants();

// Define and enforce our SSL constants.
wp_ssl_constants();

// Create common globals.
require ABSPATH . WPINC . '/vars.php';

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

wp_start_scraping_edited_file_errors();

// Register the default theme directory root.
register_theme_directory( get_theme_root() );

if ( ! is_multisite() ) {
    // Handle users requesting a recovery mode link and initiating recovery mode.
    wp_recovery_mode()->initialize();
}

// Load active plugins.
foreach ( wp_get_active_and_valid_plugins() as $plugin ) {
    wp_register_plugin_realpath( $plugin );
    include_once $plugin;

    /**
     * Fires once a single activated plugin has loaded.
     *
     * @since 5.1.0
     *
     * @param string $plugin Full path to the plugin's main file.
     */
    do_action( 'plugin_loaded', $plugin );
}
unset( $plugin );

// Load pluggable functions.
require ABSPATH . WPINC . '/pluggable.php';
require ABSPATH . WPINC . '/pluggable-deprecated.php';

// Set internal encoding.
wp_set_internal_encoding();

// Run wp_cache_postload() if object cache is enabled and the function exists.
if ( WP_CACHE && function_exists( 'wp_cache_postload' ) ) {
    wp_cache_postload();
}

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
wp_functionality_constants();

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST ).
wp_magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * WordPress Query object
 *
 * @global WP_Query $wp_the_query WordPress Query object.
 * @since 2.0.0
 */
$GLOBALS['wp_the_query'] = new WP_Query();

/**
 * Holds the reference to @see $wp_the_query
 * Use this global for WordPress queries
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @since 1.5.0
 */
$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];

/**
 * Holds the WordPress Rewrite object for creating pretty URLs
 *
 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
 * @since 1.5.0
 */
$GLOBALS['wp_rewrite'] = new WP_Rewrite();

/**
 * WordPress Object
 *
 * @global WP $wp Current WordPress environment instance.
 * @since 2.0.0
 */
$GLOBALS['wp'] = new WP();

/**
 * WordPress Widget Factory Object
 *
 * @global WP_Widget_Factory $wp_widget_factory
 * @since 2.8.0
 */
$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();

/**
 * WordPress User Roles
 *
 * @global WP_Roles $wp_roles WordPress role management object.
 * @since 2.0.0
 */
$GLOBALS['wp_roles'] = new WP_Roles();

/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_theme' );

// Define the template related constants.
wp_templating_constants();

// Load the default text localization domain.
load_default_textdomain();

$locale      = get_locale();
$locale_file = WP_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) ) {
    require $locale_file;
}
unset( $locale_file );

/**
 * WordPress Locale object for loading locale domain date and various strings.
 *
 * @global WP_Locale $wp_locale WordPress date and time locale object.
 * @since 2.1.0
 */
$GLOBALS['wp_locale'] = new WP_Locale();

/**
 * WordPress Locale Switcher object for switching locales.
 *
 * @since 4.7.0
 *
 * @global WP_Locale_Switcher $wp_locale_switcher WordPress locale switcher object.
 */
$GLOBALS['wp_locale_switcher'] = new WP_Locale_Switcher();
$GLOBALS['wp_locale_switcher']->init();

// Load the functions for the active theme, for both parent and child theme if applicable.
foreach ( wp_get_active_and_valid_themes() as $theme ) {
    if ( file_exists( $theme . '/functions.php' ) ) {
        include $theme . '/functions.php';
    }
}
unset( $theme );

/**
 * Fires after the theme is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_theme' );

// Create an instance of WP_Site_Health so that Cron events may fire.
if ( ! class_exists( 'WP_Site_Health' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
}
WP_Site_Health::get_instance();

// Set up current user.
$GLOBALS['wp']->init();

/**
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * Most of WP is loaded at this stage, and the user is authenticated. WP continues
 * to load on the {@see 'init'} hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once WP is loaded, use the {@see 'wp_loaded'} hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status.
if ( is_multisite() ) {
    $file = ms_site_check();
    if ( true !== $file ) {
        require $file;
        die();
    }
    unset( $file );
}

/**
 * This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.
 *
 * Ajax requests should use wp-admin/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link https://codex.wordpress.org/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action( 'wp_loaded' );



echo PHP_EOL . 'END OF SCRIPT' . PHP_EOL;
