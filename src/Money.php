<?php
/**
 * Integer-minor-unit money formatting and parsing.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;

/**
 * Class Money
 *
 * Formats and parses amounts held as integers in the smallest currency
 * unit — the representation Stripe, PayPal, Adyen and every sane
 * accounting system use.
 *
 * **Money is never a float.** `0.1 + 0.2` is not `0.3` in binary
 * floating point, and a rounding error in a total is a discrepancy
 * somebody eventually has to reconcile by hand. Amounts stay integer
 * from the database to the payment processor; this class exists only to
 * render them for humans and read them back.
 *
 * **Dividing by 100 is wrong for twenty currencies.** The exponent is 0
 * for fifteen (JPY, KRW, VND, the CFA francs) and 3 for five (BHD, JOD,
 * KWD, OMR, TND). A ¥1,000 order divided by 100 displays as ¥10.00, and
 * BHD 1.000 displays as BD 1000 — the first undercharges by two orders
 * of magnitude and the second overcharges by three. Everything here
 * consults {@see Currencies::decimals()} instead.
 *
 * Typical use:
 *
 *   Money::format( 4999, 'USD' );        // '$49.99'
 *   Money::format( 1000, 'JPY' );        // '¥1,000'
 *   Money::parse( '49.99', 'USD' );      // 4999
 *
 * @since 1.0.0
 */
final readonly class Money {

	/**
	 * Format an amount with its symbol.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return string e.g. `$49.99`, `¥1,000`, `BD 1.500`.
	 */
	public static function format( int $amount, string $code ): string {
		$symbol   = Currencies::symbol( $code );
		$rendered = self::decimal( $amount, $code );
		$negative = $amount < 0;

		if ( $negative ) {
			$rendered = ltrim( $rendered, '-' );
		}

		// A multi-letter symbol is a code, not a glyph, so it needs a
		// space: "BD 1.500", but "$49.99". An empty symbol means the code
		// was unrecognisable and got dropped — no leading space for it.
		$joined = match ( true ) {
			'' === $symbol            => $rendered,
			1 === mb_strlen( $symbol ) => $symbol . $rendered,
			default                    => $symbol . ' ' . $rendered,
		};

		return $negative ? '-' . $joined : $joined;
	}

	/**
	 * Format an amount with its code rather than its symbol.
	 *
	 * Unambiguous, which matters wherever several currencies appear
	 * together — a report listing `$49.99` twice is not telling you
	 * whether those are the same currency.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return string e.g. `49.99 USD`.
	 */
	public static function format_with_code( int $amount, string $code ): string {
		$suffix = Currencies::sanitize_code( $code );

		return '' === $suffix
			? self::decimal( $amount, $code )
			: self::decimal( $amount, $code ) . ' ' . $suffix;
	}

	/**
	 * Format using the platform's locale rules.
	 *
	 * Places the symbol and separators the way the target locale expects
	 * — `1 234,56 €` in French, `€1,234.56` in English. Requires ext-intl
	 * and falls back to {@see self::format()} without it.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 * @param string $locale Locale, or '' for the currency's default.
	 *
	 * @return string
	 */
	public static function format_localized( int $amount, string $code, string $locale = '' ): string {
		if ( ! class_exists( \NumberFormatter::class ) ) {
			return self::format( $amount, $code );
		}

		$locale    = '' !== $locale ? $locale : Currencies::locale( $code );
		$formatter = new \NumberFormatter( $locale, \NumberFormatter::CURRENCY );
		$result    = $formatter->formatCurrency( self::to_float( $amount, $code ), strtoupper( trim( $code ) ) );

		return false === $result ? self::format( $amount, $code ) : $result;
	}

	/**
	 * The decimal string for an amount, without any symbol.
	 *
	 * Thousands-separated, with exactly as many decimal places as the
	 * currency has.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return string e.g. `49.99`, `1,000`, `1.500`.
	 */
	public static function decimal( int $amount, string $code ): string {
		$decimals = Currencies::decimals( $code );

		if ( 0 === $decimals ) {
			return number_format( $amount );
		}

		$divisor  = 10 ** $decimals;
		$negative = $amount < 0;
		$absolute = abs( $amount );

		// Integer arithmetic throughout — converting to float here is
		// what reintroduces the rounding error this class avoids.
		$major = intdiv( $absolute, $divisor );
		$minor = $absolute % $divisor;

		$rendered = number_format( $major ) . '.' . str_pad( (string) $minor, $decimals, '0', STR_PAD_LEFT );

		return $negative ? '-' . $rendered : $rendered;
	}

	/**
	 * The bare decimal for a form field.
	 *
	 * No thousands separators, since they do not survive a round trip
	 * through a number input.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return string e.g. `1234.56`.
	 */
	public static function input_value( int $amount, string $code ): string {
		return str_replace( ',', '', self::decimal( $amount, $code ) );
	}

	/**
	 * Parse human input back to the smallest currency unit.
	 *
	 * Accepts what people actually type: symbols, spaces, thousands
	 * separators, and either decimal convention — `€1.234,56` and
	 * `$1,234.56` both mean the same amount.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input Raw input.
	 * @param string $code  ISO-4217 code.
	 *
	 * @return int Amount in the smallest currency unit. 0 when nothing
	 *             numeric was present.
	 *
	 * @throws \InvalidArgumentException When the amount is too large to
	 *                                   hold in an integer. This method
	 *                                   reads form input, so callers
	 *                                   should expect it — silently
	 *                                   clamping a money value would be
	 *                                   worse than refusing it.
	 */
	public static function parse( string $input, string $code ): int {
		$decimals = Currencies::decimals( $code );

		// Strip everything but digits, separators and a leading sign.
		$clean    = (string) preg_replace( '/[^\d.,\-]/u', '', trim( $input ) );
		$negative = str_starts_with( $clean, '-' );
		$clean    = str_replace( '-', '', $clean );

		if ( '' === $clean ) {
			return 0;
		}

		$clean = self::normalize_separators( $clean, $decimals );

		if ( 0 === $decimals ) {
			$amount = self::to_int( round( (float) $clean ) );

			return $negative ? -$amount : $amount;
		}

		$parts = explode( '.', $clean, 2 );
		$major = self::to_int( (float) ( '' === $parts[0] ? '0' : $parts[0] ) );

		// Pad or truncate the fraction to the currency's exponent rather
		// than trusting the input to have the right number of digits.
		$fraction = substr( str_pad( $parts[1] ?? '', $decimals, '0' ), 0, $decimals );

		$amount = self::to_int( $major * ( 10 ** $decimals ) + (int) ( '' === $fraction ? '0' : $fraction ) );

		return $negative ? -$amount : $amount;
	}

	/**
	 * Narrow to an integer, refusing anything that would not survive it.
	 *
	 * A twenty-digit figure typed into a price field overflows to a
	 * float, and PHP raises a `TypeError` at the return boundary rather
	 * than let it through — which reaches the user as a fatal error
	 * instead of a validation message. Rejecting it here makes the
	 * failure catchable and specific.
	 *
	 * @since 1.0.0
	 *
	 * @param int|float $value Candidate amount.
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException When outside integer range.
	 */
	private static function to_int( int|float $value ): int {
		if ( is_int( $value ) ) {
			return $value;
		}

		if ( ! is_finite( $value ) || $value >= (float) PHP_INT_MAX || $value <= (float) PHP_INT_MIN ) {
			throw new \InvalidArgumentException( 'That amount is larger than this system can represent.' );
		}

		return (int) $value;
	}

	/**
	 * Convert to a float, for a formatter that demands one.
	 *
	 * Deliberately not public as a general conversion — use it to hand a
	 * value to something like `NumberFormatter`, never to store, compare,
	 * or do arithmetic with.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return float
	 */
	public static function to_float( int $amount, string $code ): float {
		return $amount / ( 10 ** Currencies::decimals( $code ) );
	}

	/**
	 * Currencies that must be sent as whole major units.
	 *
	 * ISK and UGX both moved to zero decimals, but Stripe still requires
	 * them expressed as two-decimal values with `00` in the minor
	 * position — 5 ISK is sent as 500, and fractions are rejected.
	 *
	 * HUF and TWD accept two-decimal charges but are treated as
	 * zero-decimal for payouts, where the amount must divide by 100.
	 *
	 * Either way the safe rule for an amount you will settle is the same:
	 * make it a multiple of 100.
	 */
	private const HUNDRED_MULTIPLE = array( 'isk', 'ugx', 'huf', 'twd' );

	/**
	 * Whether an amount is one the processor will accept.
	 *
	 * Two rules, both of which produce an API rejection rather than a
	 * rounding difference, and neither of which is implied by the decimal
	 * exponent:
	 *
	 * **Three-decimal currencies settle in hundredths.** BHD, JOD, KWD,
	 * OMR and TND have an exponent of 3, but Stripe settles them to two
	 * places, so the amount must be a multiple of ten. BHD 1.234 is
	 * refused.
	 *
	 * **Four currencies must be whole major units.** ISK and UGX moved to
	 * zero decimals but are still expressed with `00` in the minor
	 * position; HUF and TWD are zero-decimal for payout purposes. All
	 * four need a multiple of 100.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return bool
	 */
	public static function is_valid_amount( int $amount, string $code ): bool {
		return 0 === $amount % self::increment( $code );
	}

	/**
	 * Whether an amount can actually be charged.
	 *
	 * Combines the three ways an amount is rejected at the API rather
	 * than by your own validation: below the minimum, above the maximum,
	 * or not a permitted increment.
	 *
	 * The minimum is the one that bites in practice — a 0.30 USD charge
	 * looks perfectly reasonable and is refused, because Stripe will not
	 * let its own fee exceed the payment.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return string|null Null when chargeable, else why not.
	 */
	public static function why_not_chargeable( int $amount, string $code ): ?string {
		$upper = strtoupper( trim( $code ) );

		if ( ! Currencies::supports( $code ) ) {
			return $upper . ' is not a supported currency.';
		}

		if ( $amount < 0 ) {
			return 'Amount cannot be negative.';
		}

		$minimum = Currencies::minimum_charge( $code );

		// Zero is allowed: subscriptions use it for trials and full
		// coupons, and Stripe accepts it.
		if ( 0 !== $amount && $minimum > 0 && $amount < $minimum ) {
			return 'Minimum charge for ' . $upper . ' is ' . self::format( $minimum, $code ) . '.';
		}

		if ( $amount > Currencies::maximum_charge( $code ) ) {
			return 'Maximum charge for ' . $upper . ' is ' . self::format( Currencies::maximum_charge( $code ), $code ) . '.';
		}

		if ( ! self::is_valid_amount( $amount, $code ) ) {
			return $upper . ' amounts must be a multiple of ' . self::increment( $code ) . '.';
		}

		return null;
	}

	/**
	 * Whether an amount can be charged.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return bool
	 */
	public static function is_chargeable( int $amount, string $code ): bool {
		return null === self::why_not_chargeable( $amount, $code );
	}

	/**
	 * Round an amount to something the processor will accept.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 *
	 * @return int
	 */
	public static function round_to_valid( int $amount, string $code ): int {
		$increment = self::increment( $code );

		return 1 === $increment ? $amount : (int) ( round( $amount / $increment ) * $increment );
	}

	/**
	 * The smallest amount step a currency permits.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return int 1, 10, or 100.
	 */
	public static function increment( string $code ): int {
		$lower = strtolower( trim( $code ) );

		if ( in_array( $lower, self::HUNDRED_MULTIPLE, true ) ) {
			return 100;
		}

		return Currencies::is_three_decimal( $code ) ? 10 : 1;
	}

	/**
	 * Apply a percentage, rounding half up.
	 *
	 * For discounts and tax. Kept here so the rounding rule is applied
	 * once rather than reinvented at each call site — inconsistent
	 * rounding is how an order total stops matching the sum of its lines.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $amount  Amount in the smallest currency unit.
	 * @param float $percent Percentage, e.g. 20.0 for 20%.
	 *
	 * @return int
	 */
	public static function percentage( int $amount, float $percent ): int {
		return (int) round( $amount * ( $percent / 100 ) );
	}

	/**
	 * Split an amount into shares that still sum to the original.
	 *
	 * Dividing 100 by 3 and rounding each share gives 33+33+33 = 99, and
	 * the missing penny turns up in a reconciliation months later. The
	 * remainder is distributed one unit at a time across the first
	 * shares, so the total always holds.
	 *
	 * @since 1.0.0
	 *
	 * @param int $amount Amount in the smallest currency unit.
	 * @param int $parts  How many shares.
	 *
	 * @return int[] Shares summing exactly to `$amount`.
	 *
	 * @throws \InvalidArgumentException When asked for fewer than one share.
	 */
	public static function allocate( int $amount, int $parts ): array {
		if ( $parts < 1 ) {
			throw new \InvalidArgumentException( 'Cannot split an amount into fewer than one share.' );
		}

		$base      = intdiv( $amount, $parts );
		$remainder = $amount - ( $base * $parts );
		$shares    = array_fill( 0, $parts, $base );

		for ( $i = 0; $i < abs( $remainder ); $i++ ) {
			$shares[ $i ] += $remainder > 0 ? 1 : -1;
		}

		return $shares;
	}

	/**
	 * Resolve mixed decimal conventions to a single dot.
	 *
	 * `1.234,56` and `1,234.56` are the same amount written for different
	 * audiences, and both arrive from real forms. The rightmost separator
	 * is the decimal one when what follows it is shorter than the
	 * currency's exponent or the two separators differ.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value    Digits and separators only.
	 * @param int    $decimals Currency exponent.
	 *
	 * @return string Digits with at most one dot.
	 */
	private static function normalize_separators( string $value, int $decimals ): string {
		$last_dot   = strrpos( $value, '.' );
		$last_comma = strrpos( $value, ',' );

		// Both present: the rightmost is the decimal separator.
		if ( false !== $last_dot && false !== $last_comma ) {
			return $last_comma > $last_dot
				? str_replace( ',', '.', str_replace( '.', '', $value ) )
				: str_replace( ',', '', $value );
		}

		// Only commas. Grouped thousands ("1,234,567") have three digits
		// after each; a single comma with a short tail is a decimal mark.
		if ( false !== $last_comma ) {
			$tail = strlen( $value ) - $last_comma - 1;

			return ( 3 === $tail && substr_count( $value, ',' ) >= 1 && $decimals !== 3 )
				? str_replace( ',', '', $value )
				: str_replace( ',', '.', $value );
		}

		// Only dots. Several means thousands separators.
		if ( substr_count( $value, '.' ) > 1 ) {
			return str_replace( '.', '', $value );
		}

		return $value;
	}
}
