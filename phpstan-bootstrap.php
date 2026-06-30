<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines runtime constants that WordPress sets in wp-config.php so PHPStan
 * can resolve them during static analysis.
 */

if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', '' );
}
