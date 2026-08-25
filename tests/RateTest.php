<?php
/**
 * Rate tests.
 *
 * @package ArrayPress\Money
 */

declare( strict_types=1 );

namespace ArrayPress\Money\Tests;

use ArrayPress\Money\Rate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A rate is a number that means one of two entirely different things, and the
 * number cannot say which.
 *
 * `20` is twenty percent or twenty pounds. On a £10 order that is the
 * difference between £8 and £0, which is why nothing here accepts a bare
 * number and guesses.
 */
final class RateTest extends TestCase {

	/**
	 * The kinds that mean "percentage", and everything else.
	 *
	 * @param string|null $kind    The kind.
	 * @param bool        $percent Whether it names a percentage.
	 */
	#[DataProvider( 'kindProvider' )]
	public function test_which_kinds_mean_percentage( ?string $kind, bool $percent ): void {
		$this->assertSame( $percent, Rate::is_percentage( $kind ) );
	}

	/**
	 * @return array<string, array{0: string|null, 1: bool}>
	 */
	public static function kindProvider(): array {
		return array(
			'percent'      => array( 'percent', true ),
			'percentage'   => array( 'percentage', true ),
			'pct'          => array( 'pct', true ),
			'the symbol'   => array( '%', true ),
			'cased'        => array( 'Percent', true ),
			'padded'       => array( '  percent ', true ),

			'flat'         => array( 'flat', false ),
			'fixed'        => array( 'fixed', false ),
			'amount'       => array( 'amount', false ),

			// Unrecognised reads as money, deliberately: showing "20" where
			// "£0.20" belongs is a display bug, showing "20%" where "£0.20"
			// belongs is a pricing one.
			'nonsense'     => array( 'nonsense', false ),
			'null'         => array( null, false ),
			'empty'        => array( '', false ),
		);
	}

	/**
	 * Each kind formats its own way.
	 */
	public function test_each_kind_formats_its_own_way(): void {
		$this->assertSame( '20%', Rate::format( 20, 'percent' ) );
		$this->assertSame( '$0.20', Rate::format( 20, 'flat' ) );

		// The same number, two answers. That is the whole point.
		$this->assertNotSame( Rate::format( 20, 'percent' ), Rate::format( 20, 'flat' ) );
	}

	/**
	 * A flat rate is money, so it obeys the currency's own decimals.
	 *
	 * 1000 is ten pounds and a thousand yen.
	 */
	public function test_a_flat_rate_obeys_the_currency(): void {
		$this->assertSame( '$10.00', Rate::format( 1000, 'flat', 'USD' ) );
		$this->assertSame( '¥1,000', Rate::format( 1000, 'flat', 'JPY' ) );
		$this->assertSame( 'BD 1.000', Rate::format( 1000, 'flat', 'BHD' ) );
	}

	/**
	 * A percentage keeps the precision it needs and no more.
	 *
	 * 8.875% is a real sales tax rate, and a table of rates that reads
	 * "20.000%" beside it is harder to scan than one that reads "20%".
	 */
	public function test_a_percentage_keeps_the_precision_it_needs(): void {
		$this->assertSame( '20%', Rate::percentage( 20.0 ) );
		$this->assertSame( '8.875%', Rate::percentage( 8.875 ) );
		$this->assertSame( '7.5%', Rate::percentage( 7.5 ) );

		// And an explicit precision wins.
		$this->assertSame( '20.00%', Rate::percentage( 20.0, 2 ) );
		$this->assertSame( '9%', Rate::percentage( 8.875, 0 ) );
	}

	/**
	 * Applying a rate is where the two kinds diverge.
	 *
	 * The method that earns the class: on a £10 order, twenty percent takes
	 * £2 and twenty pence takes 20p.
	 */
	public function test_applying_a_rate_depends_on_its_kind(): void {
		$this->assertSame( 200, Rate::applied_to( 1000, 20, 'percent' ) );
		$this->assertSame( 20, Rate::applied_to( 1000, 20, 'flat' ) );
	}

	/**
	 * A deduction never exceeds the amount, and never goes negative.
	 *
	 * A flat discount larger than the order would otherwise produce a
	 * negative total, which is a refund nobody authorised.
	 */
	public function test_a_deduction_is_bounded_by_the_amount(): void {
		$this->assertSame( 1000, Rate::applied_to( 1000, 5000, 'flat' ) );
		$this->assertSame( 1000, Rate::applied_to( 1000, 150, 'percent' ) );
		$this->assertSame( 0, Rate::applied_to( 1000, -20, 'flat' ) );
		$this->assertSame( 0, Rate::applied_to( 1000, -20, 'percent' ) );
		$this->assertSame( 0, Rate::applied_to( 0, 20, 'percent' ) );
	}

	/**
	 * A percentage deduction rounds rather than truncates.
	 *
	 * 8.875% of £19.99 is 177.41 minor units. Truncating loses a penny on
	 * every line, which is the kind of discrepancy somebody reconciles by
	 * hand at the end of a quarter.
	 */
	public function test_a_percentage_deduction_rounds(): void {
		$this->assertSame( 177, Rate::applied_to( 1999, 8.875, 'percent' ) );
		$this->assertSame( 334, Rate::applied_to( 1000, 33.35, 'percent' ) );
	}

	/**
	 * Sanitising bounds a percentage at both ends and money at one.
	 *
	 * There is no upper bound on money.
	 */
	public function test_sanitising_bounds_each_kind( ): void {
		$this->assertSame( 100.0, Rate::sanitize( 150, 'percent' ) );
		$this->assertSame( 0.0, Rate::sanitize( -5, 'percent' ) );
		$this->assertSame( 8.875, Rate::sanitize( 8.875, 'percent' ) );

		$this->assertSame( 500000, Rate::sanitize( 500000, 'flat' ) );
		$this->assertSame( 0, Rate::sanitize( -20, 'flat' ) );

		// A flat rate is money, so it comes back an integer.
		$this->assertSame( 20, Rate::sanitize( 20.7, 'flat' ) );
	}

	/**
	 * Nonsense sanitises to nothing of the right type.
	 */
	public function test_nonsense_sanitises_to_zero(): void {
		$this->assertSame( 0.0, Rate::sanitize( 'abc', 'percent' ) );
		$this->assertSame( 0, Rate::sanitize( 'abc', 'flat' ) );
		$this->assertSame( 0, Rate::sanitize( null, 'flat' ) );
		$this->assertSame( 0.0, Rate::sanitize( array(), 'percent' ) );
	}

	/**
	 * Validity is asked of the pair, not the number.
	 */
	public function test_validity_depends_on_the_kind(): void {
		$this->assertTrue( Rate::is_valid( 20, 'percent' ) );
		$this->assertTrue( Rate::is_valid( 100, 'percent' ) );
		$this->assertFalse( Rate::is_valid( 101, 'percent' ) );
		$this->assertFalse( Rate::is_valid( -1, 'percent' ) );

		$this->assertTrue( Rate::is_valid( 500000, 'flat' ) );
		$this->assertFalse( Rate::is_valid( -1, 'flat' ) );

		// Money is integer minor units, so a fractional flat rate is not a
		// rate anyone can charge.
		$this->assertFalse( Rate::is_valid( 20.5, 'flat' ) );

		$this->assertFalse( Rate::is_valid( 'abc', 'percent' ) );
		$this->assertFalse( Rate::is_valid( null, 'flat' ) );
	}
}
