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
		$this->assertSame( '$1,999.00', Money::format( 199900, 'USD' ) );
		$this->assertSame( '$1,999.00', Money::format( 199900, 'USD', array() ) );
	}

	/**
	 * Each option changes one thing.
	 *
	 * @param array  $options What was asked for.
	 * @param string $expect  What it should read.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'optionProvider' )]
	public function test_each_option_changes_one_thing( array $options, string $expect ): void {
		$this->assertSame( $expect, Money::format( 199900, 'USD', $options ) );
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
			Money::format(
				199900,
				'USD',
				array(
					'symbol'     => true,
					'code'       => true,
					'separators' => false,
				)
			)
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
		$this->assertStringContainsString( '1000', str_replace( ',', '', Money::format( 1000, 'JPY', $options ) ) );
		$this->assertStringNotContainsString( '10.00', Money::format( 1000, 'JPY', $options ) );

		$this->assertStringContainsString( '1.500', Money::format( 1500, 'BHD', $options ) );
	}

	/**
	 * A locale lays the amount out its own way.
	 *
	 * Skipped without intl, because the fallback is the point: a missing
	 * extension must not stop a price rendering.
	 */
	public function test_a_locale_lays_the_amount_out_its_own_way(): void {
		if ( ! class_exists( \NumberFormatter::class ) ) {
			$this->assertSame(
				Money::format( 123456, 'EUR' ),
				Money::format( 123456, 'EUR', array( 'locale' => 'fr_FR' ) ),
				'Without intl a locale should fall back rather than fail.'
			);

			$this->markTestSkipped( 'intl is not installed.' );
		}

		$this->assertNotSame(
			Money::format( 123456, 'EUR', array( 'locale' => 'en_US' ) ),
			Money::format( 123456, 'EUR', array( 'locale' => 'fr_FR' ) )
		);
	}

	/**
	 * Only the declared keys are read.
	 */
	public function test_only_the_declared_keys_are_read(): void {
		$this->assertSame( array( 'symbol', 'code', 'separators', 'locale' ), Options::keys() );

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
			Money::format( 4999, 'USD' ),
			@Money::format( 4999, 'USD', array( 'symbal' => false ) )
		);
	}
}
