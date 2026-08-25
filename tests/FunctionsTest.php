<?php
/**
 * Global helper and recurring tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Recurring;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The surface a plugin actually calls.
 *
 * Two things are being tested rather than one. The helpers forward, and a
 * wrapper pointing at the wrong method still returns a plausible price. And
 * they fall back to the store's currency, which is the whole reason they exist
 * -- a single-currency shop should not repeat 'USD' on every line.
 */
final class FunctionsTest extends TestCase {

	/**
	 * Forget any filter a test registered.
	 */
	protected function setUp(): void {
		mo_reset_filters();
	}

	/**
	 * And again.
	 */
	protected function tearDown(): void {
		mo_reset_filters();
	}

	/**
	 * The helpers are declared.
	 */
	public function test_the_helpers_are_declared(): void {
		foreach (
			array(
				'money_currency',
				'format_money',
				'format_money_with_code',
				'format_money_recurring',
				'render_money',
				'sanitize_money',
				'money_to_float',
				'money_input_value',
				'currency_symbol',
				'currency_decimals',
				'is_supported_currency',
				'currency_options',
				'format_rate',
			) as $function
		) {
			$this->assertTrue( function_exists( $function ), sprintf( '%s() was never declared.', $function ) );
		}
	}

	/**
	 * With no currency given, the store's is used.
	 */
	public function test_the_store_currency_is_the_fallback(): void {
		$this->assertSame( 'USD', money_currency() );
		$this->assertSame( '$49.99', format_money( 4999 ) );

		add_filter( 'money_currency', static fn() => 'GBP' );

		$this->assertSame( 'GBP', money_currency() );
		$this->assertSame( '£49.99', format_money( 4999 ) );
	}

	/**
	 * A currency given explicitly beats the store's.
	 */
	public function test_an_explicit_currency_wins(): void {
		add_filter( 'money_currency', static fn() => 'GBP' );

		$this->assertSame( '¥1,000', format_money( 1000, 'JPY' ) );
	}

	/**
	 * A filter returning nonsense does not break every price on the site.
	 *
	 * The failure it guards is silent: an unsupported code formats with no
	 * symbol and two decimals, so a yen price would read "1,000.00" and
	 * nothing would report it.
	 */
	public function test_an_unsupported_store_currency_falls_back(): void {
		add_filter( 'money_currency', static fn() => 'ZZZ' );

		$this->assertSame( 'USD', money_currency() );
		$this->assertSame( '$49.99', format_money( 4999 ) );
	}

	/**
	 * Each helper forwards to the method it names.
	 */
	public function test_each_helper_forwards_correctly(): void {
		// The code replaces the symbol rather than joining it: "49.99 USD" is
		// how an amount is written when the currency has to be stated, and
		// "$49.99 USD" says it twice.
		$this->assertSame( '49.99 USD', format_money_with_code( 4999 ) );
		$this->assertSame( '$', currency_symbol() );
		$this->assertSame( 2, currency_decimals() );
		$this->assertSame( 0, currency_decimals( 'JPY' ) );
		$this->assertTrue( is_supported_currency( 'gbp' ) );
		$this->assertFalse( is_supported_currency( 'ZZZ' ) );
		// Keyed by upper-case code, which is what a select control stores.
		$this->assertArrayHasKey( 'USD', currency_options() );
		$this->assertSame( 49.99, money_to_float( 4999 ) );
		$this->assertStringContainsString( '49.99', render_money( 4999 ) );
	}

	/**
	 * What a person types comes back as minor units.
	 *
	 * @param mixed $typed  What arrived.
	 * @param int   $expect The amount it means.
	 */
	#[DataProvider( 'typedProvider' )]
	public function test_what_a_person_types_is_read( mixed $typed, int $expect ): void {
		$this->assertSame( $expect, sanitize_money( $typed ) );
	}

	/**
	 * @return array<string, array{0: mixed, 1: int}>
	 */
	public static function typedProvider(): array {
		return array(
			'a plain amount'   => array( '19.99', 1999 ),
			'with a symbol'    => array( '$19.99', 1999 ),
			'with separators'  => array( '1,999.00', 199900 ),
			'already an int'   => array( 1999, 1999 ),
			'nothing'          => array( '', 0 ),
			'nonsense'         => array( 'abc', 0 ),
			'an array'         => array( array( 1 ), 0 ),
			'null'             => array( null, 0 ),
		);
	}

	/**
	 * An amount round-trips through the input value and back.
	 *
	 * The pair that has to agree: what goes into a text field must come back
	 * out as the same amount, or editing a price without touching it changes
	 * it.
	 */
	public function test_an_amount_round_trips_through_an_input(): void {
		foreach ( array( 'USD', 'JPY', 'BHD' ) as $code ) {
			foreach ( array( 0, 1, 1999, 100000 ) as $amount ) {
				$this->assertSame(
					$amount,
					sanitize_money( money_input_value( $amount, $code ), $code ),
					sprintf( '%d %s did not survive the round trip.', $amount, $code )
				);
			}
		}
	}

	/**
	 * A subscription price says how often it is charged.
	 *
	 * @param int    $count    How many intervals.
	 * @param string $interval The interval.
	 * @param string $expect   What it should read.
	 */
	#[DataProvider( 'recurringProvider' )]
	public function test_a_subscription_price_names_its_period( int $count, string $interval, string $expect ): void {
		$this->assertSame( $expect, format_money_recurring( 999, $interval, $count ) );
	}

	/**
	 * @return array<string, array{0: int, 1: string, 2: string}>
	 */
	public static function recurringProvider(): array {
		return array(
			'monthly'         => array( 1, 'month', '$9.99/mo' ),
			'yearly'          => array( 1, 'year', '$9.99/yr' ),
			'weekly'          => array( 1, 'week', '$9.99/wk' ),
			'daily'           => array( 1, 'day', '$9.99/day' ),

			// Past one, the short form stops reading correctly: "/3mo" is not
			// something anybody parses on the first attempt.
			'every 3 months'  => array( 3, 'month', '$9.99 every 3 months' ),
			'every 2 years'   => array( 2, 'year', '$9.99 every 2 years' ),
			'every 6 weeks'   => array( 6, 'week', '$9.99 every 6 weeks' ),
		);
	}

	/**
	 * No interval is a plain price, not a broken one.
	 */
	public function test_no_interval_is_a_plain_price(): void {
		$this->assertSame( '$9.99', format_money_recurring( 999, '' ) );
		$this->assertSame( '$9.99', Recurring::format( 999, 'USD', '   ' ) );
	}

	/**
	 * A count of nought is not a billing period.
	 *
	 * "every 0 months" is worse than a plain price, and a nought reaches here
	 * from a gateway that left the field unset.
	 */
	public function test_a_zero_count_reads_as_one(): void {
		$this->assertSame( '$9.99/mo', format_money_recurring( 999, 'month', 0 ) );
		$this->assertSame( '$9.99/mo', format_money_recurring( 999, 'month', -3 ) );
	}

	/**
	 * An interval this library has not heard of is passed through.
	 *
	 * A gateway may add one before this does. Showing "/quarter" is better
	 * than refusing to render the price.
	 */
	public function test_an_unknown_interval_is_passed_through(): void {
		$this->assertSame( '$9.99/quarter', format_money_recurring( 999, 'quarter' ) );
		$this->assertSame( '$9.99 every 2 quarters', format_money_recurring( 999, 'quarter', 2 ) );
	}

	/**
	 * A recurring price obeys the currency's decimals like any other.
	 */
	public function test_a_recurring_price_obeys_the_currency(): void {
		$this->assertSame( '¥1,000/mo', format_money_recurring( 1000, 'month', 1, 'JPY' ) );
	}

	/**
	 * The intervals come back for a dropdown.
	 */
	public function test_the_intervals_are_offered(): void {
		$this->assertSame( array( 'day', 'week', 'month', 'year' ), array_keys( Recurring::options() ) );
		$this->assertSame( Recurring::INTERVALS, array_keys( Recurring::options() ) );
	}

	/**
	 * A rate formats through the helper too.
	 */
	public function test_a_rate_formats_through_the_helper(): void {
		$this->assertSame( '20%', format_rate( 20, 'percent' ) );
		$this->assertSame( '$0.20', format_rate( 20, 'flat' ) );
	}
}
