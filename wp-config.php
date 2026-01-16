<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpperformance' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'h?/:WI,hr3t_vU,_jz[~wNa+|@N57MI%!4=/ey_Lin>b6xiwrZh+3bc:N)W{majn');
define('SECURE_AUTH_KEY',  ']p}{)/U,4PT^sCm9DqT^/|.vfT8wpLll|-2p)[Y3JFJF_DCoau} Ku-L^8<Z+tM0');
define('LOGGED_IN_KEY',    'QwvSci.j|H/IuZIZ}F,2lM;iH^TwS #R&&u;XcvTj1EfI]oR=N>|`%AvP6--]r(r');
define('NONCE_KEY',        'NX6SWLMk%Lgds+&kXXc7R|^YRB}PXft8UM-k|+IB-HAQLYvpS3@R9T++U?w7m:Z~');
define('AUTH_SALT',        '>8^N-^ww-G`TD2:/LQ-Yyv{KXD?XKSHo,)}e`o)B r9MKgd:h#(#-/eeOA5ZynI[');
define('SECURE_AUTH_SALT', 'ZpdyH|;U.&w[w>wI#-ndoFAtHBS8Hm5qy`32|;u_1 ?!Qe/*@RJ=Bq}p[5jRq9G|');
define('LOGGED_IN_SALT',   '|Cj-IG^]^NFSZL7=6&)p$w-{bDUS:]By^{![9TT)n(~/7zxc|tjwgl-[3Ymz4H>`');
define('NONCE_SALT',       'U&ocR^E(-q5%K,sWsr<t-+eIj-|3uS92|7? [:n-NQ@h3uoWI>gpBS@Y]cz/Y9s3');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
@ini_set( 'upload_max_size' , '64M' );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
