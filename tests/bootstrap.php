<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

/**
 * Registered filters.
 *
 * @var array<string, array<int, callable>>
 */
$GLOBALS['mo_filters'] = array();

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Register a filter callback.
	 *
	 * @param string   $hook     Hook name.
	 * @param callable $callback Callback.
	 *
	 * @return bool
	 */
	function add_filter( string $hook, callable $callback ): bool {
		$GLOBALS['mo_filters'][ $hook ][] = $callback;

		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Run a value through the callbacks for a hook.
	 *
	 * @param string $hook    Hook name.
	 * @param mixed  $value   Value.
	 * @param mixed  ...$args Further arguments.
	 *
	 * @return mixed
	 */
	function apply_filters( string $hook, $value, ...$args ) {
		foreach ( $GLOBALS['mo_filters'][ $hook ] ?? array() as $callback ) {
			$value = $callback( $value, ...$args );
		}

		return $value;
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Translation stub.
	 *
	 * @param string $text   Text.
	 * @param string $domain Text domain.
	 *
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string {
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	/**
	 * Plural stub.
	 *
	 * @param string $single Singular.
	 * @param string $plural Plural.
	 * @param int    $number How many.
	 * @param string $domain Text domain.
	 *
	 * @return string
	 */
	function _n( string $single, string $plural, int $number, string $domain = 'default' ): string {
		return 1 === $number ? $single : $plural;
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	/**
	 * Core's localised number formatter.
	 *
	 * @param float $number   Number.
	 * @param int   $decimals Decimal places.
	 *
	 * @return string
	 */
	function number_format_i18n( float $number, int $decimals = 0 ): string {
		return number_format( $number, $decimals );
	}
}


if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

/**
 * What _doing_it_wrong() was told.
 *
 * @var array<int, string>
 */
$GLOBALS['mo_wrong'] = array();

if ( ! function_exists( '_doing_it_wrong' ) ) {
	/**
	 * Record the complaint rather than raising it.
	 *
	 * @param string $function The function.
	 * @param string $message  The complaint.
	 * @param string $version  Since when.
	 *
	 * @return void
	 */
	function _doing_it_wrong( string $function, string $message, string $version ): void {
		$GLOBALS['mo_wrong'][] = $message;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Core's HTML escaper.
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

/**
 * Forget what _doing_it_wrong() was told.
 *
 * @return void
 */
function mo_reset_wrong(): void {
	$GLOBALS['mo_wrong'] = array();
}

/**
 * Forget any filter a test registered.
 *
 * @return void
 */
function mo_reset_filters(): void {
	$GLOBALS['mo_filters'] = array();
}


if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Core's HTML escaper.
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Core's attribute escaper.
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	function esc_attr( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/*
 * And src/Functions.php again: it is a Composer `files` entry, so it already
 * ran when PHPUnit loaded the autoloader -- before ABSPATH was defined, so it
 * returned without declaring anything. `require`, not `require_once`.
 */
require dirname( __DIR__ ) . '/src/Functions.php';
