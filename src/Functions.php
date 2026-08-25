<?php
/**
 * Global money helpers.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.2.0
 */

declare( strict_types=1 );

use ArrayPress\Money\Currencies;
use ArrayPress\Money\Money;
use ArrayPress\Money\Render;

// Exit if accessed directly.
//
// return, not exit. This file is a Composer `files` autoload entry, so it runs
// whenever anything requires the autoloader -- phpunit, phpcs, a composer
// script. Ending the process there kills the tool with status 0 and no output,
// which reads as success: a lint that never looked at a file, or a test suite
// that never ran, both report as passing.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'money_currency' ) ) {
	/**
	 * The currency to use when none is given.
	 *
	 * Filterable, because a multi-currency store's answer depends on the
	 * request.
	 *
	 * @since 1.2.0
	 *
	 * @return string An ISO-4217 code this library supports.
	 */
	function money_currency(): string {
		/**
		 * Filter the store's currency.
		 *
		 * @since 1.2.0
		 *
		 * @param string $code ISO-4217 code.
		 */
		$code = (string) apply_filters( 'money_currency', 'USD' );

		// A filter returning something unsupported would otherwise format
		// every price on the site with no symbol and two decimals, silently.
		return Currencies::supports( $code ) ? strtoupper( $code ) : 'USD';
	}
}

if ( ! function_exists( 'format_money' ) ) {
	/**
	 * An amount as text.
	 *
	 *     format_money( 4999 );                              // '$49.99'
	 *     format_money( 1000, [ 'currency' => 'JPY' ] );      // '¥1,000'
	 *     format_money( 4999, [ 'code' => true ] );           // '$49.99 USD'
	 *     format_money( 999, [ 'interval' => 'month' ] );     // '$9.99/mo'
	 *
	 * @since 1.2.0
	 *
	 * @param int                  $amount  Amount in the smallest currency unit.
	 * @param array<string, mixed> $options How to write it. See Options.
	 *
	 * @return string
	 */
	function format_money( int $amount, array $options = array() ): string {
		return Money::format( $amount, $options );
	}
}

if ( ! function_exists( 'render_price' ) ) {
	/**
	 * An amount as escaped markup.
	 *
	 *     render_price( 4999 );
	 *     render_price( 1999, [ 'compare_at' => 2999 ] );   // struck through
	 *
	 * @since 1.5.0
	 *
	 * @param int                  $amount  Amount in the smallest currency unit.
	 * @param array<string, mixed> $options How to write it. See Options.
	 *
	 * @return string
	 */
	function render_price( int $amount, array $options = array() ): string {
		return Render::price( $amount, $options );
	}
}

if ( ! function_exists( 'sanitize_money' ) ) {
	/**
	 * Read an amount a person typed, as minor units.
	 *
	 * Takes "19.99", "£19.99", "1,999.00" or "19,99". Anything unreadable is
	 * nought rather than a fatal, because this is what a form field goes
	 * through.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed  $value What was typed.
	 * @param string $code  ISO-4217 code, or empty for the store's.
	 *
	 * @return int Amount in the smallest currency unit.
	 */
	function sanitize_money( mixed $value, string $code = '' ): int {
		if ( is_int( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return 0;
		}

		return Money::parse( (string) $value, Money::currency( $code ) );
	}
}

if ( ! function_exists( 'money_input_value' ) ) {
	/**
	 * An amount as it should sit in a text input.
	 *
	 * No symbol and no separators, so it round-trips through sanitize_money()
	 * unchanged. A named helper rather than an options array because getting
	 * it wrong silently changes a price the moment somebody opens the form
	 * and saves it without touching the field.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 *
	 * @return string
	 */
	function money_input_value( int $amount, string $code = '' ): string {
		return Money::format(
			$amount,
			array(
				'currency'   => $code,
				'symbol'     => false,
				'separators' => false,
			)
		);
	}
}
