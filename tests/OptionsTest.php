<?php
/**
 * Formatter option tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Money;
use ArrayPress\Money\Options;
use ArrayPress\Money\Render;
use PHPUnit\Framework\TestCase;

/**
 * One formatter with options, rather than a method per combination.
 *
 * There used to be five -- format(), format_with_code(), format_localized(),
 * decimal(), input_value() -- and adding subscriptions doubled it, because
 * every one wanted a recurring twin.
 *
 * An enum was tried first and was wrong. These are not one axis: whether to
 * show the symbol, whether to name the currency, whether to group the
 * thousands and which locale's layout to use are four independent questions,
 * and an enum forces them into one list where "localized" sits beside "code"
 * as though you could not want both.
 */
final class OptionsTest extends TestCase {

	/**
	 * The default is a symbol and grouped thousands.
	 *
	 * What a shop front shows, and what every caller passing nothing gets.
	 */
	public function test_the_default_is_a_plain_price(): void {
		$this->assertSame( '$1,999.00', Money::format( 199900, array( 'currency' => 'USD' ) ) );
		$this->assertSame( '$1,999.00', Money::format( 199900, array( 'currency' => 'USD' ) ) );
	}

	/**
	 * Each option changes one thing.
	 *
	 * @param array  $options What was asked for.
	 * @param string $expect  What it should read.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'optionProvider' )]
	public function test_each_option_changes_one_thing( array $options, string $expect ): void {
		$this->assertSame( $expect, Money::format( 199900, array( 'currency' => 'USD' ) + $options ) );
	}

	/**
	 * @return array<string, array{0: array<string, mixed>, 1: string}>
	 */
	public static function optionProvider(): array {
		return array(
			'nothing'            => array( array(), '$1,999.00' ),
			'no symbol'          => array( array( 'symbol' => false ), '1,999.00' ),
			'no separators'      => array( array( 'separators' => false ), '$1999.00' ),
			'the code as well'   => array( array( 'code' => true ), '$1,999.00 USD' ),
			'the code instead'   => array( array( 'symbol' => false, 'code' => true ), '1,999.00 USD' ),

			// The combination an input field wants: nothing but the number,
			// so it round-trips through a form unchanged.
			'ready for an input' => array( array( 'symbol' => false, 'separators' => false ), '1999.00' ),
		);
	}

	/**
	 * The options combine, which is the whole reason they are not an enum.
	 *
	 * "Symbol and code" is a real thing an invoice wants and no single-axis
	 * list can express.
	 */
	public function test_the_options_combine(): void {
		$this->assertSame(
			'$1999.00 USD',
			Money::format( 199900, array( 'currency' => 'USD', 'symbol'     => true,
					'code'       => true,
					'separators' => false ) )
		);
	}

	/**
	 * A currency's own decimals survive every option.
	 *
	 * The thing this library exists for: no combination of presentation
	 * choices may turn a 1,000 yen order into ¥10.00.
	 *
	 * @param array $options What was asked for.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'optionProvider' )]
	public function test_the_currency_decimals_survive_every_option( array $options, string $ignored ): void {
		$this->assertStringContainsString( '1000', str_replace( ',', '', Money::format( 1000, array( 'currency' => 'JPY' ) + $options ) ) );
		$this->assertStringNotContainsString( '10.00', Money::format( 1000, array( 'currency' => 'JPY' ) + $options ) );

		$this->assertStringContainsString( '1.500', Money::format( 1500, array( 'currency' => 'BHD' ) + $options ) );
	}


	/**
	 * Only the declared keys are read.
	 */
	public function test_only_the_declared_keys_are_read(): void {
		$this->assertSame( array( 'currency', 'symbol', 'code', 'separators', 'interval', 'interval_count' ), Options::keys() );

		// A renderer reads two more.
		$this->assertContains( 'compare_at', Options::keys( true ) );
		$this->assertContains( 'class', Options::keys( true ) );

		$parsed = Options::parse( array( 'symbol' => false, 'nonsense' => true ) );

		$this->assertArrayNotHasKey( 'nonsense', $parsed );
		$this->assertFalse( $parsed['symbol'] );
		$this->assertTrue( $parsed['separators'], 'An unset option lost its default.' );
	}

	/**
	 * An option nothing reads is reported, not ignored.
	 *
	 * The standing objection to configuration arrays, answered: a misspelled
	 * key is not an error in PHP, so the option silently does nothing and the
	 * only symptom is a price that does not look the way it was asked to.
	 */
	public function test_an_unknown_option_is_named(): void {
		$this->assertSame( array( 'dispaly' ), Options::unknown_keys( array( 'dispaly' => 'code' ) ) );
		$this->assertSame( array(), Options::unknown_keys( array( 'symbol' => false, 'code' => true ) ) );
	}

	/**
	 * A misspelled key does not quietly change the price either.
	 */
	public function test_a_misspelled_option_leaves_the_defaults(): void {
		$this->assertSame(
			Money::format( 4999, array( 'currency' => 'USD' ) ),
			@Money::format( 4999, array( 'currency' => 'USD', 'symbal' => false ) )
		);
	}

	/**
	 * A formatter refuses the rendering keys, loudly.
	 *
	 * `compare_at` passed to format_money() rather than render_price() is the
	 * mistake this catches most: it is a plausible thing to write, it does
	 * nothing, and the only symptom is a sale price that renders as an
	 * ordinary one.
	 */
	public function test_a_formatter_refuses_the_rendering_keys(): void {
		mo_reset_wrong();

		Money::format( 1999, array( 'compare_at' => 2999 ) );

		$this->assertCount( 1, $GLOBALS['mo_wrong'] );
		$this->assertStringContainsString( 'compare_at', $GLOBALS['mo_wrong'][0] );

		// And a renderer accepts them without complaint.
		mo_reset_wrong();

		Render::price( 1999, array( 'compare_at' => 2999 ) );

		$this->assertSame( array(), $GLOBALS['mo_wrong'] );
	}

	/**
	 * The renderer does not pass its own keys down to the formatter.
	 *
	 * They are not format keys, so the formatter would complain about them --
	 * on every rendered price, in every debug log.
	 */
	public function test_the_renderer_keeps_its_own_keys(): void {
		mo_reset_wrong();

		Render::price( 1999, array( 'compare_at' => 2999, 'class' => 'cost' ) );

		$this->assertSame( array(), $GLOBALS['mo_wrong'] );
	}

	/**
	 * A misspelled key is named, and does not change the price.
	 */
	public function test_a_misspelled_key_is_named(): void {
		mo_reset_wrong();

		$rendered = Money::format( 4999, array( 'currency' => 'USD', 'symbal' => false ) );

		$this->assertSame( Money::format( 4999, array( 'currency' => 'USD' ) ), $rendered );
		$this->assertCount( 1, $GLOBALS['mo_wrong'] );
		$this->assertStringContainsString( 'symbal', $GLOBALS['mo_wrong'][0] );
	}
}
