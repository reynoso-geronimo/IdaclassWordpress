<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u969791460_SNpaj' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'u8VxswYsBEqg' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'wv53MX2Ev4GNmFl8F5VBy(-:m0CV%?BmL%A:d;,n2?f`(*{A|kUc5e]x}a;8U7YX' );
define( 'SECURE_AUTH_KEY',   '}LbR^Kp+1T4{{CE3F|/uRZx}p|?&:;b_pD5BXS!BqP*DMG(}=D &e`N%Qi):*#H*' );
define( 'LOGGED_IN_KEY',     'Hk%-/j*V#oYmc#Or{[vc(mpal(YG`V^f)hC-z5*z1/_Zn-E%#+;8l,ge9zoK?c0}' );
define( 'NONCE_KEY',         '7O_%VH%o#b$_,0{o;wG])82CguE_;AxAF%{PA@ie3P/j[@Twe`*^YBt.Smc~lZM%' );
define( 'AUTH_SALT',         '?~<6+6).V TCh?mQC!=(9Ds@lxAs*hr>$EQ?fuCRL}AKD%N(uUeHig~r6nZ)#<*L' );
define( 'SECURE_AUTH_SALT',  'krC3c>RF8X{k+;EL7>&{tMW}5B^12}Gw4C 9q?XC?$!=+B(}@&=6DaPg$6WH=R.j' );
define( 'LOGGED_IN_SALT',    '&8|[DOWI;4#TFdx,q4%3Ipd0fqF`9,JrmbA#!lWm5^l3F*TB- ud8OTk3<$/*4,_' );
define( 'NONCE_SALT',        'j42t;d6@G6b:,BxgAHyP6I|^SYsE@y1e Y%~lz0!m;#RhWc.qpdD+m[9^G3AGD1;' );
define( 'WP_CACHE_KEY_SALT', 'X DB>J*X/V5Wr>hToc&9;blAx:HWFsPjqfxFVi-|FC,W&K`{ZPmw/~[,9.HInSw3' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
