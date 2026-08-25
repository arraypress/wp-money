<?php
/**
 * Dataset integrity tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Currencies;
use PHPUnit\Framework\TestCase;

/**
 * The dataset is the library. Everything else is arithmetic over it.
 *
 * A currency missing its exponent, or carrying the wrong one, produces no
 * error anywhere: the amount formats, the total adds up, and it is wrong by a
 * factor of a hundred or a thousand. So the table is checked against itself,
 * and the counts the documentation states are checked against the table --
 * because a docblock claiming "136 currencies" beside a table of 140 is how a
 * reader stops trusting either.
 */
final class DatasetTest extends TestCase {

	/**
	 * The counts the README and the docblocks state.
	 *
	 * Not arbitrary: the exponent is 0 for fifteen currencies and 3 for five,
	 * and "twenty currencies where dividing by 100 is wrong" is the sentence
	 * this library exists to make true.
	 */
	public function test_the_stated_counts_are_the_real_ones(): void {
		$this->assertCount( 136, Currencies::all() );

		$zero  = array();
		$three = array();

		foreach ( array_keys( Currencies::all() ) as $code ) {
			$decimals = Currencies::decimals( $code );

			if ( 0 === $decimals ) {
				$zero[] = strtoupper( (string) $code );
			} elseif ( 3 === $decimals ) {
				$three[] = strtoupper( (string) $code );
			}
		}

		$this->assertCount( 15, $zero, 'The zero-decimal count has drifted from what the docs claim.' );
		$this->assertCount( 5, $three, 'The three-decimal count has drifted from what the docs claim.' );
		$this->assertCount( 20, array_merge( $zero, $three ) );
	}

	/**
	 * The currencies that are not two-decimal are the ones they should be.
	 *
	 * Named rather than counted, because a count stays right while the members
	 * change. These are ISO 4217's own exponents and they do not move.
	 */
	public function test_the_non_standard_exponents_are_the_right_currencies(): void {
		$zero = array();

		foreach ( array_keys( Currencies::all() ) as $code ) {
			if ( 0 === Currencies::decimals( $code ) ) {
				$zero[] = strtoupper( (string) $code );
			}
		}

		sort( $zero );

		$this->assertSame(
			array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ),
			$zero
		);

		$three = array();

		foreach ( array_keys( Currencies::all() ) as $code ) {
			if ( 3 === Currencies::decimals( $code ) ) {
				$three[] = strtoupper( (string) $code );
			}
		}

		sort( $three );

		$this->assertSame( array( 'BHD', 'JOD', 'KWD', 'OMR', 'TND' ), $three );
	}

	/**
	 * Every currency carries every field.
	 *
	 * A missing symbol renders an amount with no symbol at all, and a missing
	 * exponent defaults to something -- neither of which errors.
	 */
	public function test_every_currency_is_complete(): void {
		foreach ( array_keys( Currencies::all() ) as $code ) {
			$code = (string) $code;

			$this->assertMatchesRegularExpression( '/^[a-z]{3}$/', $code, 'A code is not three lower-case letters.' );
			$this->assertNotSame( '', trim( Currencies::name( $code ) ), sprintf( '%s has no name.', $code ) );
			$this->assertNotSame( '', trim( Currencies::symbol( $code ) ), sprintf( '%s has no symbol.', $code ) );
			$this->assertContains( Currencies::decimals( $code ), array( 0, 2, 3 ), sprintf( '%s has an odd exponent.', $code ) );
			$this->assertMatchesRegularExpression( '/^[A-Z]{2}$/', Currencies::country( $code ), sprintf( '%s has no issuing country.', $code ) );
		}
	}

	/**
	 * The currencies a store is most likely to take are present.
	 *
	 * A spot check against the failure the cross-checks cannot see: a table
	 * that is internally consistent and missing something.
	 */
	public function test_the_currencies_a_store_needs_are_present(): void {
		foreach ( array( 'usd', 'eur', 'gbp', 'cad', 'aud', 'jpy', 'chf', 'sek', 'nok', 'dkk', 'nzd', 'sgd', 'inr', 'brl', 'mxn', 'zar', 'pln', 'czk' ) as $code ) {
			$this->assertTrue( Currencies::supports( $code ), sprintf( '%s is missing.', strtoupper( $code ) ) );
		}
	}

	/**
	 * No two currencies share a code, and the list is sorted.
	 *
	 * Sorted because it is maintained by hand and a new entry appended to the
	 * end is how a duplicate gets in.
	 */
	public function test_the_list_is_sorted_and_unique(): void {
		$codes = array_map( 'strval', array_keys( Currencies::all() ) );

		$this->assertSame( array_values( array_unique( $codes ) ), $codes, 'A code appears twice.' );

		$sorted = $codes;
		sort( $sorted );

		$this->assertSame( $sorted, $codes, 'The dataset is not in code order.' );
	}
}
