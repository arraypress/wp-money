<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

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
