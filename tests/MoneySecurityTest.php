<?php
/**
 * Untrusted input reaching the money helpers.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ArrayPress\Money\Currencies;
use ArrayPress\Money\Money;

/**
 * A currency code routinely arrives from a query string — `?currency=`
 * on a headless checkout — and an amount arrives from a form field.
 * Neither may reach the rendered page or a fatal error.
 */
final class MoneySecurityTest extends TestCase {

	/* ─── Codes ─────────────────────────────────────────────────────── */

	#[DataProvider( 'hostile_codes' )]
	public function test_an_unrecognised_code_is_never_echoed( string $code ): void {
		$this->assertSame( '', Currencies::sanitize_code( $code ) );
		$this->assertSame( '', Currencies::symbol( $code ) );
		$this->assertSame( '', Currencies::name( $code ) );

		foreach ( array( Money::format( 100, $code ), Money::format_with_code( 100, $code ) ) as $rendered ) {
			$this->assertStringNotContainsString( '<', $rendered );
			$this->assertStringNotContainsString( '"', $rendered );
			$this->assertSame( '1.00', trim( $rendered ) );
		}
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function hostile_codes(): array {
		return array(
			'script tag'   => array( '<script>alert(1)</script>' ),
			'attribute'    => array( '" onload="alert(1)' ),
			'too short'    => array( 'US' ),
			'too long'     => array( 'USDD' ),
			'symbol'       => array( '€' ),
			'digits'       => array( '123' ),
			'empty'        => array( '' ),
			'null byte'    => array( "US\x00D" ),
			'entity'       => array( '&lt;b&gt;' ),
		);
	}

	/**
	 * An unknown but well-formed code is a data problem, not an attack,
	 * so it is shown rather than swallowed.
	 */
	public function test_a_plausible_unknown_code_is_kept(): void {
		$this->assertSame( 'XYZ', Currencies::sanitize_code( 'xyz' ) );
		$this->assertSame( 'XYZ', Currencies::symbol( 'XYZ' ) );
		$this->assertSame( '1.00 XYZ', Money::format_with_code( 100, 'xyz' ) );
	}

	public function test_known_codes_are_unaffected(): void {
		$this->assertSame( '$1.00', Money::format( 100, 'usd' ) );
		$this->assertSame( '1.00 USD', Money::format_with_code( 100, 'USD' ) );
		$this->assertSame( '¥1,000', Money::format( 1000, 'JPY' ) );
	}

	/* ─── Amounts ───────────────────────────────────────────────────── */

	/**
	 * Twenty digits in a price field overflow to a float, and PHP raises
	 * a `TypeError` at the return boundary — which reaches the user as a
	 * fatal rather than a validation message.
	 */
	#[DataProvider( 'oversized_amounts' )]
	public function test_an_unrepresentable_amount_is_refused( string $input ): void {
		$this->expectException( InvalidArgumentException::class );

		Money::parse( $input, 'USD' );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function oversized_amounts(): array {
		return array(
			'21 digits'  => array( '999999999999999999999' ),
			'int max'    => array( '9223372036854775807' ),
			'negative'   => array( '-999999999999999999999' ),
			'separators' => array( '999,999,999,999,999,999,999.99' ),
		);
	}

	#[DataProvider( 'ordinary_amounts' )]
	public function test_ordinary_amounts_still_parse( string $input, int $expected ): void {
		$this->assertSame( $expected, Money::parse( $input, 'USD' ) );
	}

	/**
	 * @return array<string, array{0: string, 1: int}>
	 */
	public static function ordinary_amounts(): array {
		return array(
			'plain'        => array( '49.99', 4999 ),
			'separators'   => array( '$1,234.56', 123456 ),
			'european'     => array( '€1.234,56', 123456 ),
			'negative'     => array( '-5.00', -500 ),
			'no fraction'  => array( '10', 1000 ),
			'nothing'      => array( 'abc', 0 ),
			'empty'        => array( '', 0 ),
			'large valid'  => array( '90000000000000', 9000000000000000 ),
		);
	}

	public function test_parsing_never_emits_a_type_error(): void {
		foreach ( array( '1e400', str_repeat( '9', 400 ), '.'. str_repeat( '9', 400 ) ) as $input ) {
			try {
				$this->assertIsInt( Money::parse( $input, 'USD' ) );
			} catch ( InvalidArgumentException ) {
				$this->addToAssertionCount( 1 );
			}
		}
	}
}
