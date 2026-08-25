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
use ArrayPress\Money\Rate;
use ArrayPress\Money\Recurring;
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
	 * Every helper below takes an optional code and falls back to this, so a
	 * single-currency store never repeats itself. Filterable, because a
	 * multi-currency store's answer depends on the request.
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
	 * Format an amount for a person to read.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 *
	 * @return string e.g. `$49.99`, `¥1,000`, `BD 1.500`.
	 */
	function format_money( int $amount, string $code = '' ): string {
		return Money::format( $amount, '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'format_money_with_code' ) ) {
	/**
	 * Format an amount with its currency named.
	 *
	 * For anywhere more than one currency can appear: `$49.99` is ambiguous
	 * across the dollar currencies and `$49.99 USD` is not.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 *
	 * @return string
	 */
	function format_money_with_code( int $amount, string $code = '' ): string {
		return Money::format_with_code( $amount, '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'format_money_recurring' ) ) {
	/**
	 * Format a subscription price.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount   Amount in the smallest currency unit.
	 * @param string $interval 'day', 'week', 'month' or 'year'.
	 * @param int    $count    How many intervals between charges.
	 * @param string $code     ISO-4217 code, or empty for the store's.
	 *
	 * @return string e.g. `$9.99/mo`, `$9.99 every 3 months`.
	 */
	function format_money_recurring( int $amount, string $interval, int $count = 1, string $code = '' ): string {
		return Recurring::format( $amount, '' !== $code ? $code : money_currency(), $interval, $count );
	}
}

if ( ! function_exists( 'render_money' ) ) {
	/**
	 * An amount, wrapped and escaped, for putting in a page.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 * @param string $class  Class for the wrapper.
	 *
	 * @return string
	 */
	function render_money( int $amount, string $code = '', string $class = 'price' ): string {
		return Render::amount( $amount, '' !== $code ? $code : money_currency(), $class );
	}
}

if ( ! function_exists( 'sanitize_money' ) ) {
	/**
	 * Read an amount a person typed, as minor units.
	 *
	 * Takes "19.99", "£19.99", "1,999.00" or "19,99" and gives back the
	 * integer this library works in. Anything unreadable is nought rather
	 * than a fatal, because this is what a form field goes through.
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

		return Money::parse( (string) $value, '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'money_to_float' ) ) {
	/**
	 * An amount as a float, for a gateway that insists on one.
	 *
	 * Not for arithmetic. `0.1 + 0.2` is not `0.3` in binary floating point,
	 * and a rounding error in a total is a discrepancy somebody reconciles by
	 * hand. Keep amounts integer and use this at the boundary only.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 *
	 * @return float
	 */
	function money_to_float( int $amount, string $code = '' ): float {
		return Money::to_float( $amount, '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'money_input_value' ) ) {
	/**
	 * An amount as it should sit in a text input.
	 *
	 * No symbol and no thousands separator, so it round-trips through
	 * sanitize_money() unchanged.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code, or empty for the store's.
	 *
	 * @return string
	 */
	function money_input_value( int $amount, string $code = '' ): string {
		return Money::input_value( $amount, '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'currency_symbol' ) ) {
	/**
	 * A currency's symbol.
	 *
	 * @since 1.2.0
	 *
	 * @param string $code ISO-4217 code, or empty for the store's.
	 *
	 * @return string
	 */
	function currency_symbol( string $code = '' ): string {
		return Currencies::symbol( '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'currency_decimals' ) ) {
	/**
	 * How many decimal places a currency has.
	 *
	 * Nought for fifteen of them and three for five, which is why dividing by
	 * a hundred is wrong for twenty currencies.
	 *
	 * @since 1.2.0
	 *
	 * @param string $code ISO-4217 code, or empty for the store's.
	 *
	 * @return int
	 */
	function currency_decimals( string $code = '' ): int {
		return Currencies::decimals( '' !== $code ? $code : money_currency() );
	}
}

if ( ! function_exists( 'is_supported_currency' ) ) {
	/**
	 * Whether this library knows a currency.
	 *
	 * @since 1.2.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return bool
	 */
	function is_supported_currency( string $code ): bool {
		return Currencies::supports( $code );
	}
}

if ( ! function_exists( 'currency_options' ) ) {
	/**
	 * Every currency, for a settings dropdown.
	 *
	 * @since 1.2.0
	 *
	 * @return array<string, string> Code against name.
	 */
	function currency_options(): array {
		return Currencies::options();
	}
}

if ( ! function_exists( 'format_rate' ) ) {
	/**
	 * Format a rate, which is a percentage or an amount depending on its kind.
	 *
	 * @since 1.2.0
	 *
	 * @param int|float   $value A percentage, or an amount in minor units.
	 * @param string|null $kind  'percent', or anything else for a flat amount.
	 * @param string      $code  ISO-4217 code, or empty for the store's.
	 *
	 * @return string
	 */
	function format_rate( int|float $value, ?string $kind, string $code = '' ): string {
		return Rate::format( $value, $kind, '' !== $code ? $code : money_currency() );
	}
}
