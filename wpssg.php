<?php



define( 'ABSPATH', __DIR__ . '/' );
// toggle error reporting with a DEBUG mode for this script. We have developed WP site, mindful of errors, so this should just be catching issues with our generator

define('THISSCRIPT_DEBUG', false);

if(THISSCRIPT_DEBUG === true)
{
   error_reporting(E_ALL);
   display_errors(true);
   log_errors(true);
}
else
{
   error_reporting(0);
   display_errors(false);
   log_errors(false);
}

//wp-config.php:
define( 'DB_NAME', 'database_name_here' );
define( 'DB_USER', 'username_here' );
define( 'DB_PASSWORD', 'password_here' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         '' );
define( 'SECURE_AUTH_KEY',  '' );
define( 'LOGGED_IN_KEY',    '' );
define( 'NONCE_KEY',        '' );
define( 'AUTH_SALT',        '' );
define( 'SECURE_AUTH_SALT', '' );
define( 'LOGGED_IN_SALT',   '' );
define( 'NONCE_SALT',       '' );

$table_prefix = 'wp_';

//wp-settings.php:
define( 'WPINC', 'wp-includes' );
// rm the tinymce, required php, mysql checks
// ie https://github.com/WordPress/WordPress/search?q=required_php_version not used anywhere of relevance to us
// wp_version is used in a few places, ie Etag header, but really, shouldn't need it for generating site frontend, I'd remove it
// global $wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $wp_local_package;
// so we can ignore this: require ABSPATH . WPINC . '/version.php';


// require ABSPATH . WPINC . '/load.php'; <-- much of this can be skipped. We need to set the REQUEST_URI type stuff, make a decision on language support and load the DB. a lot of this file is unneeded utility code.

// review load.php further, then continue with wp-settings.php review from line 34
