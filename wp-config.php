<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'stampil1_wp434' );

/** MySQL database username */
define( 'DB_USER', 'stampil1_adrian' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Feb0lebisback' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'zykmc7ijf7c1ryylxqbbifqzczno7mjsupd9bghwgpyf17vqrwitiqdlpavuzttc' );
define( 'SECURE_AUTH_KEY',  '0mrmkj6xteqvq0z3mw6dx4lndwbxesivp1sjvjsl2ymeln7qtrpnqecukyb80z8h' );
define( 'LOGGED_IN_KEY',    'gubuuwwkrzcrhzivxd8ep5v1gyltinbisovpehhy5ffgpftpdpqkxauxxa6nn9vy' );
define( 'NONCE_KEY',        'csnaqhwqziipjxkzq8hnafzqq7dc1gp7qmdommq29eizm58oo4kr4ufuyukkl1g6' );
define( 'AUTH_SALT',        'thrk2iim459qedrgi4bbxfbbcxgcpeqg6gpuk0vvrddyouolnnozzqz651yjj3wr' );
define( 'SECURE_AUTH_SALT', 'emc6trnbl5rxfydqlg5lju8ua42z2vdmw4dfrg28sh5uy27fn4inpbyjlz4qescn' );
define( 'LOGGED_IN_SALT',   'omx2utb5qijxddvvm3mwwhyfxmwo8zurskwsi1rc2najwsfzvglogvcdrunt69wu' );
define( 'NONCE_SALT',       'fz09ohpwzwek88qc4sfqxbdsczm0l8q5qtc4aniyhytyljqdjpqgelv6rt1is4jh' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wper_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define('WP_MEMORY_LIMIT', '1024M');

