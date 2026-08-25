<?php
/**
 * Prices that repeat.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.2.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;


/**
 * Class Recurring
 *
 * A subscription price is an amount and a period, and the period changes what
 * the amount means. £9.99 and £9.99 a month are different products.
 *
 * Ported from the JavaScript this set already shipped for the same job, so a
 * price rendered in a checkout by the browser and in an order table by PHP
 * reads the same. That was the point: the two disagreeing is how a customer
 * ends up querying their invoice.
 *
 * @since 1.2.0
 */
final class Recurring {

	/**
	 * The intervals a subscription can bill on.
	 *
	 * Stripe's set. Anything else is passed through as given rather than
	 * refused, because a gateway may add one before this library hears of it.
	 *
	 * @var string[]
	 */
	public const INTERVALS = array( 'day', 'week', 'month', 'year' );

	/**
	 * A recurring price, short form.
	 *
	 * `£9.99/mo`, which is what fits in a table cell and on a button.
	 *
	 * @since 1.2.0
	 *
	 * @param int                     $amount   Amount in the smallest currency unit.
	 * @param string                  $code     ISO-4217 code.
	 * @param string                  $interval 'day', 'week', 'month' or 'year'.
	 * @param int                     $count    How many intervals between charges.
	 * @param array                   $options  How to write the amount. See Options.
	 *
	 * @return string
	 */
	public static function format(
		int $amount,
		string $code,
		string $interval,
		int $count = 1,
		array $options = array()
	): string {
		return Money::format( $amount, $code, $options ) . self::suffix( $interval, $count );
	}

	/**
	 * What follows the amount.
	 *
	 * Two shapes, because one does not cover both cases. Every month is
	 * `/mo`, which is short enough to sit beside a price. Every three months
	 * is "every 3 months", because `/3mo` is not something anybody reads
	 * correctly on the first attempt.
	 *
	 * @since 1.2.0
	 *
	 * @param string $interval 'day', 'week', 'month' or 'year'.
	 * @param int    $count    How many intervals between charges.
	 *
	 * @return string Empty when there is no interval.
	 */
	public static function suffix( string $interval, int $count = 1 ): string {
		$interval = strtolower( trim( $interval ) );

		if ( '' === $interval ) {
			return '';
		}

		// A count of nought or less is not a billing period. Treating it as
		// one would render "every 0 months", which is worse than a plain
		// price.
		$count = max( 1, $count );

		if ( 1 === $count ) {
			return self::short( $interval );
		}

		return ' ' . sprintf(
			/* translators: 1: how many intervals, 2: the interval, pluralised. */
			__( 'every %1$d %2$s', 'arraypress' ),
			$count,
			self::plural( $interval, $count )
		);
	}

	/**
	 * The short form: `/mo`, `/yr`.
	 *
	 * @since 1.2.0
	 *
	 * @param string $interval The interval.
	 *
	 * @return string
	 */
	private static function short( string $interval ): string {
		return match ( $interval ) {
			'day'   => '/' . __( 'day', 'arraypress' ),
			'week'  => '/' . __( 'wk', 'arraypress' ),
			'month' => '/' . __( 'mo', 'arraypress' ),
			'year'  => '/' . __( 'yr', 'arraypress' ),
			default => '/' . $interval,
		};
	}

	/**
	 * The interval, pluralised.
	 *
	 * Through _n() rather than by appending an "s", because the count decides
	 * the form and not every language agrees that two is plural.
	 *
	 * @since 1.2.0
	 *
	 * @param string $interval The interval.
	 * @param int    $count    How many.
	 *
	 * @return string
	 */
	private static function plural( string $interval, int $count ): string {
		return match ( $interval ) {
			'day'   => _n( 'day', 'days', $count, 'arraypress' ),
			'week'  => _n( 'week', 'weeks', $count, 'arraypress' ),
			'month' => _n( 'month', 'months', $count, 'arraypress' ),
			'year'  => _n( 'year', 'years', $count, 'arraypress' ),
			default => $interval . 's',
		};
	}

	/**
	 * The intervals, for a settings dropdown.
	 *
	 * @since 1.2.0
	 *
	 * @return array<string, string>
	 */
	public static function options(): array {
		return array(
			'day'   => __( 'Daily', 'arraypress' ),
			'week'  => __( 'Weekly', 'arraypress' ),
			'month' => __( 'Monthly', 'arraypress' ),
			'year'  => __( 'Yearly', 'arraypress' ),
		);
	}
}
