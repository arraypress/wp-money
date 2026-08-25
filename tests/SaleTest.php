<?php
/**
 * Sale price and gateway rule tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Money;
use ArrayPress\Money\Rate;
use ArrayPress\Money\Render;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Reduced prices, and the gateway rules that decide whether an amount is one
 * the processor will actually take.
 *
 * Both are places where being wrong is expensive rather than untidy: a
 * strikethrough on the wrong number misprices the product, and an amount the
 * API refuses fails at checkout with the customer watching.
 */
final class SaleTest extends TestCase {

	/**
	 * A reduction strikes through what it cost before.
	 *
	 * `<del>` and `<ins>`, not spans with classes -- that is what the
	 * elements mean, and it is the only version a screen reader can make
	 * sense of.
	 */
	public function test_a_reduction_strikes_through_the_old_price(): void {
		$html = Render::sale( 1999, 2999, 'USD' );

		$this->assertStringContainsString( '<del>', $html );
		$this->assertStringContainsString( '$29.99', $html );
		$this->assertStringContainsString( '<ins>', $html );
		$this->assertStringContainsString( '$19.99', $html );

		// The struck-through one is the old price, not the new one.
		$this->assertMatchesRegularExpression( '#<del>.*29\.99.*</del>#s', $html );
		$this->assertMatchesRegularExpression( '#<ins>.*19\.99.*</ins>#s', $html );
	}

	/**
	 * Each price says which it is, for anybody not looking at it.
	 *
	 * Without the labels a screen reader reads the two numbers one after the
	 * other with nothing to distinguish them, and a struck-through price
	 * sounds exactly like the price.
	 */
	public function test_each_price_is_labelled_for_a_screen_reader(): void {
		$html = Render::sale( 1999, 2999, 'USD' );

		$this->assertStringContainsString( 'screen-reader-text', $html );
		$this->assertStringContainsString( 'Regular price', $html );
		$this->assertStringContainsString( 'Sale price', $html );
	}

	/**
	 * A compare-at that is not higher renders as an ordinary price.
	 *
	 * Striking through a number that is the same or smaller reads as an
	 * increase, which is the opposite of what a sale badge is for. Equal
	 * prices arrive routinely from a product whose sale has ended.
	 *
	 * @param int $compare_at What it supposedly cost before.
	 */
	#[DataProvider( 'notASaleProvider' )]
	public function test_a_compare_at_that_is_not_higher_is_not_a_sale( int $compare_at ): void {
		$html = Render::sale( 1999, $compare_at, 'USD' );

		$this->assertStringNotContainsString( '<del>', $html );
		$this->assertSame( Render::amount( 1999, 'USD' ), $html );
	}

	/**
	 * @return array<string, array{0: int}>
	 */
	public static function notASaleProvider(): array {
		return array(
			'the same'  => array( 1999 ),
			'lower'     => array( 999 ),
			'nought'    => array( 0 ),
			'negative'  => array( -100 ),
		);
	}

	/**
	 * The saving is a whole percentage, rounded down.
	 *
	 * Down, so the badge never overstates it: 33.9% off reads as 33%, which
	 * is a claim the price supports.
	 *
	 * @param int $amount     What is charged.
	 * @param int $compare_at What it cost before.
	 * @param int $expect     The percentage.
	 */
	#[DataProvider( 'savingProvider' )]
	public function test_the_saving_is_a_whole_percentage( int $amount, int $compare_at, int $expect ): void {
		$this->assertSame( $expect, Money::saving_percentage( $amount, $compare_at ) );
	}

	/**
	 * @return array<string, array{0: int, 1: int, 2: int}>
	 */
	public static function savingProvider(): array {
		return array(
			'a third off'      => array( 1999, 2999, 33 ),
			'half price'       => array( 1000, 2000, 50 ),
			'a tenth'          => array( 900, 1000, 10 ),

			// 33.9% rounds down to 33, not up to 34.
			'just under a third' => array( 661, 1000, 33 ),

			'no reduction'     => array( 1999, 1999, 0 ),
			'an increase'      => array( 2999, 1999, 0 ),
			'nothing before'   => array( 1999, 0, 0 ),
		);
	}

	/**
	 * A discount produces the price to charge.
	 *
	 * Both kinds, through the same call, because the number cannot say which
	 * it is.
	 */
	public function test_a_discount_produces_the_price_to_charge(): void {
		$regular = 2999;

		$this->assertSame( 2399, $regular - Rate::applied_to( $regular, 20, 'percent' ) );
		$this->assertSame( 2499, $regular - Rate::applied_to( $regular, 500, 'flat' ) );

		// And it renders as a sale against the original.
		$html = Render::sale( $regular - Rate::applied_to( $regular, 20, 'percent' ), $regular, 'USD' );

		$this->assertStringContainsString( '$23.99', $html );
		$this->assertStringContainsString( '$29.99', $html );
	}

	/**
	 * A sale price obeys the currency's decimals like any other.
	 */
	public function test_a_sale_price_obeys_the_currency(): void {
		$html = Render::sale( 1000, 2000, 'JPY' );

		$this->assertStringContainsString( '¥1,000', $html );
		$this->assertStringContainsString( '¥2,000', $html );
		$this->assertStringNotContainsString( '10.00', $html );
	}

	/**
	 * Only ISK and UGX are restricted at charge time.
	 *
	 * Stripe: HUF and TWD are "zero-decimal for payouts, even though you can
	 * charge two-decimal amounts". Treating all four the same refuses a HUF
	 * 10.45 order the API would have taken -- a checkout failure caused
	 * entirely by this library.
	 *
	 * @param string $code       The currency.
	 * @param int    $amount     The amount.
	 * @param bool   $chargeable Whether Stripe would take it.
	 */
	#[DataProvider( 'gatewayProvider' )]
	public function test_the_charge_rules_match_the_gateway( string $code, int $amount, bool $chargeable ): void {
		$this->assertSame( $chargeable, Money::is_valid_amount( $amount, $code ) );
	}

	/**
	 * @return array<string, array{0: string, 1: int, 2: bool}>
	 */
	public static function gatewayProvider(): array {
		return array(
			'ISK in fractions'   => array( 'ISK', 545, false ),
			'ISK whole'          => array( 'ISK', 500, true ),
			'UGX in fractions'   => array( 'UGX', 545, false ),
			'UGX whole'          => array( 'UGX', 500, true ),

			// Chargeable, unlike ISK and UGX.
			'HUF in fractions'   => array( 'HUF', 1045, true ),
			'TWD in fractions'   => array( 'TWD', 80045, true ),

			// Three-decimal currencies settle in hundredths.
			'BHD to three places' => array( 'BHD', 1234, false ),
			'BHD to two'          => array( 'BHD', 1230, true ),

			'an ordinary amount'  => array( 'USD', 1999, true ),
		);
	}

	/**
	 * Payouts are the stricter question, and only for two currencies.
	 */
	public function test_payouts_are_stricter_for_huf_and_twd(): void {
		$this->assertFalse( Money::is_valid_payout( 1045, 'HUF' ) );
		$this->assertTrue( Money::is_valid_payout( 1000, 'HUF' ) );

		$this->assertFalse( Money::is_valid_payout( 80045, 'TWD' ) );
		$this->assertTrue( Money::is_valid_payout( 80000, 'TWD' ) );

		// Everything else answers the same as a charge.
		$this->assertTrue( Money::is_valid_payout( 1999, 'USD' ) );
		$this->assertFalse( Money::is_valid_payout( 545, 'ISK' ) );
	}

	/**
	 * The minimum charges are the gateway's, to the penny.
	 *
	 * Checked against Stripe's published table. The minimum is the rule that
	 * bites in practice: a 0.30 USD charge looks perfectly reasonable and is
	 * refused, because Stripe will not let its own fee exceed the payment.
	 */
	public function test_the_minimums_are_the_gateways(): void {
		$this->assertFalse( Money::is_chargeable( 30, 'USD' ) );
		$this->assertTrue( Money::is_chargeable( 50, 'USD' ) );

		$this->assertFalse( Money::is_chargeable( 29, 'GBP' ) );
		$this->assertTrue( Money::is_chargeable( 30, 'GBP' ) );

		// The ones people get wrong, because the minor unit is the whole unit.
		$this->assertFalse( Money::is_chargeable( 49, 'JPY' ) );
		$this->assertTrue( Money::is_chargeable( 50, 'JPY' ) );

		$this->assertFalse( Money::is_chargeable( 17499, 'HUF' ) );
		$this->assertTrue( Money::is_chargeable( 17500, 'HUF' ) );
	}
}
