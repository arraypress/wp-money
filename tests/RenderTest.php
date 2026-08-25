<?php
/**
 * Rendering tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Render;
use PHPUnit\Framework\TestCase;

/**
 * The only part of this library that knows what HTML is.
 *
 * Money::format() is arithmetic and has no opinion about markup. Keeping the
 * two apart is what stops the usual mistake, which is escaping twice or not
 * at all -- and the symbols here make double-escaping visible: `د.إ` and `৳`
 * survive esc_html() untouched, so anything mangling them is doing something
 * it should not.
 */
final class RenderTest extends TestCase {

	/**
	 * An amount comes back wrapped and escaped.
	 */
	public function test_an_amount_is_wrapped_and_escaped(): void {
		$this->assertSame( '<span class="price">$49.99</span>', Render::amount( 4999, 'USD' ) );
	}

	/**
	 * The wrapper class is the caller's, and is escaped.
	 */
	public function test_the_class_is_escaped(): void {
		$html = Render::amount( 100, 'USD', 'a" onclick="x' );

		$this->assertStringNotContainsString( 'onclick="x"', $html );
		$this->assertStringContainsString( '&quot;', $html );
	}

	/**
	 * A non-ASCII symbol survives intact.
	 *
	 * esc_html() leaves these alone; a function that escaped twice, or that
	 * reached for something ASCII-only, would not.
	 */
	public function test_a_non_ascii_symbol_survives(): void {
		$this->assertStringContainsString( '৳', Render::amount( 100, 'BDT' ) );
		$this->assertStringContainsString( '₼', Render::amount( 100, 'AZN' ) );

		// And no entity-encoding of the glyph, which is what escaping twice
		// would produce.
		$this->assertStringNotContainsString( '&amp;', Render::amount( 100, 'BDT' ) );
	}

	/**
	 * The code form names the currency, for a page showing more than one.
	 *
	 * `$49.99` is ambiguous across the dollar currencies.
	 */
	public function test_the_code_form_names_the_currency(): void {
		$html = Render::amount_with_code( 4999, 'AUD' );

		$this->assertStringContainsString( 'AUD', $html );
		$this->assertNotSame( Render::amount( 4999, 'AUD' ), $html );
	}

	/**
	 * A zero-decimal currency renders with no decimal part.
	 *
	 * The mistake this library exists to prevent, reaching the page: dividing
	 * by 100 shows a 1,000 yen order as ¥10.00.
	 */
	public function test_a_zero_decimal_currency_renders_whole(): void {
		$html = Render::amount( 1000, 'JPY' );

		$this->assertStringContainsString( '1,000', $html );
		$this->assertStringNotContainsString( '10.00', $html );
	}

	/**
	 * And a three-decimal one keeps all three.
	 */
	public function test_a_three_decimal_currency_keeps_three(): void {
		$this->assertStringContainsString( '1.500', Render::amount( 1500, 'BHD' ) );
	}
}
