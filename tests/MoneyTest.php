<?php
/**
 * Money and Currencies test suite.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use ArrayPress\Money\Currencies;
use ArrayPress\Money\Money;

final class MoneyTest extends TestCase {

	/* ─── Dataset ───────────────────────────────────────────────────── */

	public function test_the_dataset_is_complete(): void {
		$this->assertCount( 136, Currencies::all() );
		$this->assertCount( 136, Currencies::codes() );
	}

	public function test_every_currency_has_a_usable_row(): void {
		foreach ( Currencies::all() as $code => $meta ) {
			$this->assertNotSame( '', $meta['name'], $code );
			$this->assertNotSame( '', $meta['symbol'], $code );
			$this->assertContains( $meta['decimals'], array( 0, 2, 3 ), $code );
			$this->assertNotSame( '', $meta['locale'], $code );
		}
	}

	public function test_lookups_are_case_insensitive(): void {
		$this->assertSame( Currencies::get( 'usd' ), Currencies::get( 'USD' ) );
		$this->assertSame( 'US Dollar', Currencies::name( ' usd ' ) );
	}

	public function test_unknown_currencies_degrade_sensibly(): void {
		$this->assertNull( Currencies::get( 'ZZZ' ) );
		$this->assertFalse( Currencies::supports( 'ZZZ' ) );
		$this->assertSame( 2, Currencies::decimals( 'ZZZ' ) );
		$this->assertSame( 'ZZZ', Currencies::symbol( 'ZZZ' ) );
	}

	/* ─── Exponents ─────────────────────────────────────────────────── */

	#[DataProvider( 'exponents' )]
	public function test_decimal_exponents( string $code, int $expected ): void {
		$this->assertSame( $expected, Currencies::decimals( $code ) );
	}

	/** @return array<string, array{0: string, 1: int}> */
	public static function exponents(): array {
		return array(
			'USD' => array( 'USD', 2 ),
			'EUR' => array( 'EUR', 2 ),
			'GBP' => array( 'GBP', 2 ),
			'JPY' => array( 'JPY', 0 ),
			'KRW' => array( 'KRW', 0 ),
			'VND' => array( 'VND', 0 ),
			'XAF' => array( 'XAF', 0 ),
			'BHD' => array( 'BHD', 3 ),
			'KWD' => array( 'KWD', 3 ),
			'TND' => array( 'TND', 3 ),
		);
	}

	public function test_the_zero_and_three_decimal_sets_are_the_expected_size(): void {
		$zero  = array_filter( Currencies::codes(), static fn( string $c ): bool => Currencies::is_zero_decimal( $c ) );
		$three = array_filter( Currencies::codes(), static fn( string $c ): bool => Currencies::is_three_decimal( $c ) );

		$this->assertCount( 15, $zero );
		$this->assertCount( 5, $three );
	}

	/* ─── Formatting ────────────────────────────────────────────────── */

	#[DataProvider( 'formatting' )]
	public function test_formatting( int $amount, string $code, string $expected ): void {
		$this->assertSame( $expected, Money::format( $amount, $code ) );
	}

	/** @return array<string, array{0: int, 1: string, 2: string}> */
	public static function formatting(): array {
		return array(
			'usd'              => array( 4999, 'USD', '$49.99' ),
			'usd whole'        => array( 5000, 'USD', '$50.00' ),
			'usd zero'         => array( 0, 'USD', '$0.00' ),
			'usd sub-unit'     => array( 5, 'USD', '$0.05' ),
			'usd thousands'    => array( 123456789, 'USD', '$1,234,567.89' ),
			'usd negative'     => array( -4999, 'USD', '-$49.99' ),
			// Dividing by 100 would render these as ¥10.00 and ₩12.34.
			'jpy'              => array( 1000, 'JPY', '¥1,000' ),
			'krw'              => array( 1234, 'KRW', '₩1,234' ),
			// And this one as BD 1500 — three orders of magnitude out.
			'bhd'              => array( 1500, 'BHD', 'BD 1.500' ),
			'kwd'              => array( 1234, 'KWD', 'KD 1.234' ),
		);
	}

	/**
	 * The bug the exponent table exists to prevent, stated directly.
	 */
	public function test_zero_decimal_currencies_are_not_divided(): void {
		$this->assertSame( '¥1,000', Money::format( 1000, 'JPY' ) );
		$this->assertNotSame( '¥10.00', Money::format( 1000, 'JPY' ) );
	}

	public function test_three_decimal_currencies_use_three_places(): void {
		$this->assertSame( '1.500', Money::decimal( 1500, 'BHD' ) );
		$this->assertNotSame( '15.00', Money::decimal( 1500, 'BHD' ) );
	}

	public function test_multi_character_symbols_are_spaced(): void {
		$this->assertStringContainsString( ' ', Money::format( 1500, 'BHD' ) );
		$this->assertStringNotContainsString( ' ', Money::format( 4999, 'USD' ) );
	}

	public function test_formatting_with_a_code(): void {
		$this->assertSame( '49.99 USD', Money::format( 4999, 'USD', array( 'symbol' => false, 'code' => true ) ) );
		$this->assertSame( '1,000 JPY', Money::format( 1000, 'JPY', array( 'symbol' => false, 'code' => true ) ) );
	}

	public function test_input_values_have_no_separators(): void {
		$this->assertSame( '1234567.89', Money::format( 123456789, 'USD', array( 'symbol' => false, 'separators' => false ) ) );
		$this->assertSame( '1000', Money::format( 1000, 'JPY', array( 'symbol' => false, 'separators' => false ) ) );
	}

	#[RequiresPhpExtension( 'intl' )]
	public function test_localized_formatting_follows_the_locale(): void {
		$english = Money::format( 123456, 'EUR', array( 'locale' => 'en_US' ) );
		$french  = Money::format( 123456, 'EUR', array( 'locale' => 'fr_FR' ) );

		$this->assertNotSame( $english, $french );
		$this->assertStringContainsString( '234', $english );
		$this->assertStringContainsString( '234', $french );
	}

	/* ─── Parsing ───────────────────────────────────────────────────── */

	#[DataProvider( 'parsing' )]
	public function test_parsing( string $input, string $code, int $expected ): void {
		$this->assertSame( $expected, Money::parse( $input, $code ) );
	}

	/** @return array<string, array{0: string, 1: string, 2: int}> */
	public static function parsing(): array {
		return array(
			'plain'             => array( '49.99', 'USD', 4999 ),
			'with symbol'       => array( '$49.99', 'USD', 4999 ),
			'with spaces'       => array( '  49.99  ', 'USD', 4999 ),
			'thousands'         => array( '1,234.56', 'USD', 123456 ),
			'european'          => array( '1.234,56', 'USD', 123456 ),
			'comma decimal'     => array( '49,99', 'USD', 4999 ),
			'whole number'      => array( '50', 'USD', 5000 ),
			'one decimal'       => array( '49.9', 'USD', 4990 ),
			'excess decimals'   => array( '49.999', 'USD', 4999 ),
			'negative'          => array( '-49.99', 'USD', -4999 ),
			'zero decimal'      => array( '1000', 'JPY', 1000 ),
			'zero decimal sep'  => array( '1,000', 'JPY', 1000 ),
			'three decimal'     => array( '1.500', 'BHD', 1500 ),
			'empty'             => array( '', 'USD', 0 ),
			'no digits'         => array( 'abc', 'USD', 0 ),
		);
	}

	public function test_formatting_and_parsing_round_trip(): void {
		foreach ( array( 'USD', 'EUR', 'GBP', 'JPY', 'KRW', 'BHD', 'KWD' ) as $code ) {
			foreach ( array( 0, 1, 999, 4999, 100000, 123456789 ) as $amount ) {
				$this->assertSame(
					$amount,
					Money::parse( Money::format( $amount, $code, array( 'symbol' => false, 'separators' => false ) ), $code ),
					$code . ' ' . $amount
				);
			}
		}
	}

	/* ─── Amount validity ───────────────────────────────────────────── */

	/**
	 * Stripe settles three-decimal currencies in hundredths despite the
	 * three-digit exponent, so the amount must be a multiple of ten.
	 */
	public function test_three_decimal_amounts_must_be_multiples_of_ten(): void {
		$this->assertFalse( Money::is_valid_amount( 1234, 'BHD' ) );
		$this->assertTrue( Money::is_valid_amount( 1230, 'BHD' ) );
		$this->assertSame( 1230, Money::round_to_valid( 1234, 'BHD' ) );
		$this->assertSame( 1240, Money::round_to_valid( 1236, 'BHD' ) );
	}

	/**
	 * ISK and UGX moved to zero decimals but Stripe still expects them
	 * with 00 in the minor position; HUF and TWD are zero-decimal for
	 * payouts. All four reject anything that is not a whole major unit,
	 * and none of that is implied by their decimal exponent.
	 */
	#[DataProvider( 'hundred_multiple_currencies' )]
	public function test_whole_major_unit_currencies( string $code ): void {
		$this->assertSame( 100, Money::increment( $code ), $code );

		$this->assertFalse( Money::is_valid_amount( 555, $code ) );
		$this->assertFalse( Money::is_valid_amount( 510, $code ) );
		$this->assertTrue( Money::is_valid_amount( 500, $code ) );

		$this->assertSame( 600, Money::round_to_valid( 555, $code ) );
		$this->assertSame( 500, Money::round_to_valid( 510, $code ) );
	}

	/** @return array<string, array{0: string}> */
	public static function hundred_multiple_currencies(): array {
		return array(
			'ISK' => array( 'ISK' ),
			'UGX' => array( 'UGX' ),
			'HUF' => array( 'HUF' ),
			'TWD' => array( 'TWD' ),
			'lowercase' => array( 'isk' ),
		);
	}

	public function test_increments_by_currency_class(): void {
		$this->assertSame( 1, Money::increment( 'USD' ) );
		$this->assertSame( 1, Money::increment( 'JPY' ) );
		$this->assertSame( 10, Money::increment( 'BHD' ) );
		$this->assertSame( 100, Money::increment( 'ISK' ) );
	}

	public function test_other_currencies_accept_any_amount(): void {
		$this->assertTrue( Money::is_valid_amount( 1234, 'USD' ) );
		$this->assertSame( 1234, Money::round_to_valid( 1234, 'USD' ) );
	}

	/* ─── Chargeability ─────────────────────────────────────────────── */

	/**
	 * The rejection that surprises people: a perfectly sensible-looking
	 * 30-cent charge is refused because Stripe will not let its fee
	 * exceed the payment.
	 */
	public function test_amounts_below_the_minimum_are_refused(): void {
		$this->assertFalse( Money::is_chargeable( 30, 'USD' ) );
		$this->assertTrue( Money::is_chargeable( 50, 'USD' ) );

		// And the minimums are not proportional between currencies.
		$this->assertTrue( Money::is_chargeable( 30, 'GBP' ) );
		$this->assertFalse( Money::is_chargeable( 1400, 'CZK' ) );
		$this->assertTrue( Money::is_chargeable( 1500, 'CZK' ) );
	}

	public function test_zero_is_chargeable_for_trials_and_full_coupons(): void {
		$this->assertTrue( Money::is_chargeable( 0, 'USD' ) );
	}

	public function test_amounts_above_the_maximum_are_refused(): void {
		$this->assertTrue( Money::is_chargeable( 99999999, 'USD' ) );
		$this->assertFalse( Money::is_chargeable( 100000000, 'USD' ) );

		// Three currencies allow more digits.
		$this->assertTrue( Money::is_chargeable( 999999999, 'INR' ) );
		$this->assertTrue( Money::is_chargeable( 9999999999, 'COP' ) );
		$this->assertTrue( Money::is_chargeable( 999999999999, 'IDR' ) );
	}

	public function test_negative_and_unsupported_are_refused(): void {
		$this->assertFalse( Money::is_chargeable( -100, 'USD' ) );
		$this->assertFalse( Money::is_chargeable( 5000, 'ZZZ' ) );
	}

	public function test_increment_violations_are_refused(): void {
		$this->assertFalse( Money::is_chargeable( 555, 'ISK' ) );
		$this->assertFalse( Money::is_chargeable( 1234, 'BHD' ) );
	}

	public function test_rejection_reasons_are_actionable(): void {
		$this->assertStringContainsString( 'Minimum charge', (string) Money::why_not_chargeable( 30, 'USD' ) );
		$this->assertStringContainsString( '$0.50', (string) Money::why_not_chargeable( 30, 'USD' ) );
		$this->assertStringContainsString( 'multiple of 100', (string) Money::why_not_chargeable( 555, 'ISK' ) );
		$this->assertNull( Money::why_not_chargeable( 4999, 'USD' ) );
	}

	public function test_published_minimums_are_present(): void {
		$this->assertSame( 50, Currencies::minimum_charge( 'USD' ) );
		$this->assertSame( 30, Currencies::minimum_charge( 'GBP' ) );
		$this->assertSame( 17500, Currencies::minimum_charge( 'HUF' ) );
		$this->assertSame( 50, Currencies::minimum_charge( 'JPY' ) );
		$this->assertSame( 0, Currencies::minimum_charge( 'AFN' ), 'no published minimum' );
	}

	/* ─── Arithmetic ────────────────────────────────────────────────── */

	public function test_percentages_round_consistently(): void {
		$this->assertSame( 1000, Money::percentage( 5000, 20.0 ) );
		$this->assertSame( 833, Money::percentage( 4999, 16.667 ) );
		$this->assertSame( 0, Money::percentage( 0, 20.0 ) );
	}

	/**
	 * Splitting 100 three ways and rounding each share gives 99 — and
	 * the missing unit surfaces in a reconciliation months later.
	 */
	public function test_allocation_never_loses_a_unit(): void {
		foreach ( array( 100, 4999, 1, 7, 123456789 ) as $amount ) {
			foreach ( array( 1, 2, 3, 7, 13 ) as $parts ) {
				$shares = Money::allocate( $amount, $parts );

				$this->assertCount( $parts, $shares );
				$this->assertSame( $amount, array_sum( $shares ), "$amount / $parts" );
			}
		}
	}

	public function test_allocation_spreads_the_remainder(): void {
		$this->assertSame( array( 34, 33, 33 ), Money::allocate( 100, 3 ) );
	}

	public function test_allocation_handles_negatives(): void {
		$this->assertSame( -100, array_sum( Money::allocate( -100, 3 ) ) );
	}

	public function test_allocating_into_no_parts_is_refused(): void {
		$this->expectException( \InvalidArgumentException::class );
		Money::allocate( 100, 0 );
	}

	/* ─── Lookups ───────────────────────────────────────────────────── */

	public function test_currencies_resolve_by_country(): void {
		$this->assertContains( 'JPY', Currencies::for_country( 'JP' ) );
		$this->assertContains( 'GBP', Currencies::for_country( 'gb' ) );
		$this->assertSame( array(), Currencies::for_country( 'ZZ' ) );
	}

	public function test_select_options_are_labelled_and_sorted(): void {
		$options = Currencies::options();

		$this->assertCount( 136, $options );
		$this->assertStringContainsString( 'US Dollar', $options['USD'] );
		$this->assertStringContainsString( '$', $options['USD'] );
		$this->assertSame( array_values( $options ), array_values( $options ) );
	}
}
