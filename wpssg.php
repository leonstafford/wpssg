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

define('ABSPATH', __DIR__ . '/');

//wp-config.php:
define('DB_NAME', 'database_name_here');
define('DB_USER', 'username_here');
define('DB_PASSWORD', 'password_here');
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


// require ABSPATH . WPINC . '/load.php'; <-- much of this can be skipped. We need to set the REQUEST_URI type stuff,
// make a decision on language support and load the DB. a lot of this file is unneeded utility code.

// review load.php further, then continue with wp-settings.php review from line 34
// wpincludes/load.php

// force 1.0
function wp_get_server_protocol() : string {
    return 'HTTP/1.0';
}

// need to do some of this
/**
 * Fix `$_SERVER` variables for various setups.
 *
 * @since 3.0.0
 * @access private
 *
 * @global string $PHP_SELF The filename of the currently executing script,
 *                          relative to the document root.
 */
function wp_fix_server_vars() {
    global $PHP_SELF;

    $default_server_values = array(
        'SERVER_SOFTWARE' => '',
        'REQUEST_URI'     => '',
    );

    $_SERVER = array_merge( $default_server_values, $_SERVER );

    // Fix for IIS when running with PHP ISAPI.
    if ( empty( $_SERVER['REQUEST_URI'] ) || ( 'cgi-fcgi' !== PHP_SAPI && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {

        if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
            // IIS Mod-Rewrite.
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } elseif ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
            // IIS Isapi_Rewrite.
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
        } else {
            // Use ORIG_PATH_INFO if there is no PATH_INFO.
            if ( ! isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) ) {
                $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
            }

            // Some IIS + PHP configurations put the script-name in the path-info (no need to append it twice).
            if ( isset( $_SERVER['PATH_INFO'] ) ) {
                if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] ) {
                    $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
                } else {
                    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
                }
            }

            // Append the query string if it exists and isn't null.
            if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
    }

    // Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests.
    if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) ) {
        $_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
    }

    // Fix for Dreamhost and other PHP as CGI hosts.
    if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false ) {
        unset( $_SERVER['PATH_INFO'] );
    }

    // Fix empty PHP_SELF.
    $PHP_SELF = $_SERVER['PHP_SELF'];
    if ( empty( $PHP_SELF ) ) {
        $_SERVER['PHP_SELF'] = preg_replace( '/(\?.*)?$/', '', $_SERVER['REQUEST_URI'] );
        $PHP_SELF            = $_SERVER['PHP_SELF'];
    }

    wp_populate_basic_auth_from_authorization_header();
}

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

echo PHP_EOL . 'END OF SCRIPT' . PHP_EOL;
