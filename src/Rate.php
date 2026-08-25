<?php
/**
 * A rate: a percentage, or an amount of money.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.1.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;

/**
 * Class Rate
 *
 * A tax rate, a commission, a discount. Each is a number that means one of two
 * entirely different things, and the number cannot say which: `20` is twenty
 * percent or twenty pounds, and getting it backwards on a discount is the
 * difference between £8 and £0 on a £10 order.
 *
 * So a rate is the pair -- a value and a kind -- and every method here takes
 * both. There is no method that accepts a bare number and guesses. Guessing
 * from the range ("under a hundred, so probably a percentage") is what the
 * library this replaced did, and it is wrong for every flat rate under a
 * pound and every percentage over par.
 *
 * A percentage is held as a float, because 8.875% is a real sales tax rate.
 * A flat amount is held as an integer in the smallest currency unit, like
 * every other amount in this library.
 *
 * @since 1.1.0
 */
final class Rate {

	/**
	 * Ways of writing "this is a percentage".
	 *
	 * Every one of these turns up as a `type` column somewhere.
	 *
	 * @var string[]
	 */
	private const PERCENTAGE_KINDS = array( 'percent', 'percentage', 'pct', '%' );

	/**
	 * Whether a kind names a percentage.
	 *
	 * Anything else is taken as a flat amount, deliberately. A rate whose kind
	 * is missing or unrecognised is safer read as money: showing "20" where
	 * "£0.20" belongs is a display bug, and showing "20%" where "£0.20"
	 * belongs is a pricing one.
	 *
	 * @since 1.1.0
	 *
	 * @param string|null $kind The kind, from wherever it is stored.
	 *
	 * @return bool
	 */
	public static function is_percentage( ?string $kind ): bool {
		return in_array( strtolower( trim( (string) $kind ) ), self::PERCENTAGE_KINDS, true );
	}

	/**
	 * Format a rate.
	 *
	 * @since 1.1.0
	 *
	 * @param int|float   $value    A percentage, or an amount in the smallest
	 *                              currency unit.
	 * @param string|null $kind     'percent' or anything else for a flat amount.
	 * @param string      $currency ISO-4217 code, for the flat case.
	 * @param int         $decimals Decimal places, for the percentage case.
	 *
	 * @return string
	 */
	public static function format( int|float $value, ?string $kind, string $currency = 'USD', int $decimals = 0 ): string {
		return self::is_percentage( $kind )
			? self::percentage( (float) $value, $decimals )
			: Money::format( (int) $value, array( 'currency' => $currency ) );
	}

	/**
	 * A percentage.
	 *
	 * Trailing zeroes are dropped when no precision was asked for, so a table
	 * of rates reads "20%" and "8.875%" rather than "20.000%".
	 *
	 * @since 1.1.0
	 *
	 * @param float    $value    The percentage.
	 * @param int|null $decimals Decimal places, or null to keep what is needed.
	 *
	 * @return string
	 */
	public static function percentage( float $value, ?int $decimals = null ): string {
		if ( null === $decimals ) {
			// Keep what the number actually carries, up to four places --
			// enough for a sales tax rate, short of floating-point noise.
			$decimals = 0;

			for ( $places = 4; $places > 0; $places-- ) {
				if ( round( $value, $places ) !== round( $value, $places - 1 ) ) {
					$decimals = $places;
					break;
				}
			}
		}

		return self::number( $value, $decimals ) . '%';
	}

	/**
	 * What a rate takes off an amount.
	 *
	 * The reason a rate is worth modelling at all: applying it is where the
	 * two kinds diverge, and where guessing costs money.
	 *
	 * @since 1.1.0
	 *
	 * @param int         $amount Amount in the smallest currency unit.
	 * @param int|float   $value  The rate.
	 * @param string|null $kind   'percent' or anything else for a flat amount.
	 *
	 * @return int The deduction, never more than the amount and never negative.
	 */
	public static function applied_to( int $amount, int|float $value, ?string $kind ): int {
		$deduction = self::is_percentage( $kind )
			? (int) round( $amount * ( (float) $value / 100 ) )
			: (int) $value;

		return max( 0, min( $amount, $deduction ) );
	}

	/**
	 * A rate brought into range.
	 *
	 * A percentage is clamped to nought and a hundred; a flat amount only to
	 * nought, because there is no upper bound on money.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed       $value The value, from a form or a column.
	 * @param string|null $kind  'percent' or anything else for a flat amount.
	 *
	 * @return int|float
	 */
	public static function sanitize( mixed $value, ?string $kind ): int|float {
		if ( ! is_numeric( $value ) ) {
			return self::is_percentage( $kind ) ? 0.0 : 0;
		}

		if ( self::is_percentage( $kind ) ) {
			return max( 0.0, min( 100.0, round( (float) $value, 4 ) ) );
		}

		return max( 0, (int) $value );
	}

	/**
	 * Whether a value is usable as a rate of this kind.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed       $value The value.
	 * @param string|null $kind  'percent' or anything else for a flat amount.
	 *
	 * @return bool
	 */
	public static function is_valid( mixed $value, ?string $kind ): bool {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		if ( self::is_percentage( $kind ) ) {
			return (float) $value >= 0.0 && (float) $value <= 100.0;
		}

		return (float) $value >= 0.0 && (float) $value === floor( (float) $value );
	}

	/**
	 * A number, localised where WordPress is present.
	 *
	 * @param float $value    The number.
	 * @param int   $decimals Decimal places.
	 *
	 * @return string
	 */
	private static function number( float $value, int $decimals ): string {
		return function_exists( 'number_format_i18n' )
			? (string) number_format_i18n( $value, $decimals )
			: number_format( $value, $decimals );
	}
}
