<?php
/**
 * The options a formatter takes.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.3.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;

/**
 * Class Options
 *
 * There was a method per combination -- format(), format_with_code(),
 * format_localized(), decimal(), input_value() -- and adding subscriptions
 * doubled it, because every one of them wanted a recurring twin.
 *
 * An enum was the first attempt and was wrong: these are not one axis. Whether
 * to show the symbol, whether to name the currency, whether to use the
 * locale's own layout and whether to include thousands separators are four
 * independent questions, and an enum forces them into one list where
 * "localized" sits beside "code" as though you could not want both.
 *
 * So: an array, keyed, with the keys declared. The usual objection to an array
 * is that a typo does nothing and says nothing -- which is why an unrecognised
 * key raises _doing_it_wrong() under WP_DEBUG, the same way wp-field-kit
 * handles its own configuration.
 *
 * @since 1.3.0
 */
final class Options {

	/**
	 * Every key a formatter reads.
	 *
	 * @var array<string, mixed>
	 */
	private const DEFAULTS = array(
		// Show the currency's symbol: `$49.99`.
		'symbol'     => true,

		// Name the currency after the amount: `49.99 USD`. With `symbol` as
		// well, `$49.99 USD` -- which an invoice sometimes wants and a shop
		// front never does.
		'code'       => false,

		// Group the thousands: `1,999.00` rather than `1999.00`. Off for a
		// text input, so a value round-trips through a form unchanged.
		'separators' => true,

		// Lay the amount out the way a locale does -- `49,99 €` -- through
		// PHP's intl extension. Falls back silently when intl is absent,
		// because a missing extension should not stop a price rendering.
		'locale'     => '',
	);

	/**
	 * Fill in what was not given, and complain about what is not read.
	 *
	 * @since 1.3.0
	 *
	 * @param array<string, mixed> $options What the caller passed.
	 *
	 * @return array<string, mixed>
	 */
	public static function parse( array $options = array() ): array {
		self::warn_about_unknown_keys( $options );

		return array_merge( self::DEFAULTS, array_intersect_key( $options, self::DEFAULTS ) );
	}

	/**
	 * The keys a formatter reads.
	 *
	 * @since 1.3.0
	 *
	 * @return string[]
	 */
	public static function keys(): array {
		return array_keys( self::DEFAULTS );
	}

	/**
	 * The keys given that nothing reads.
	 *
	 * @since 1.3.0
	 *
	 * @param array<string, mixed> $options What the caller passed.
	 *
	 * @return string[]
	 */
	public static function unknown_keys( array $options ): array {
		return array_values( array_diff( array_keys( $options ), self::keys() ) );
	}

	/**
	 * Say so, while somebody is looking.
	 *
	 * The answer to the usual objection to configuration arrays: a
	 * misspelled key is not an error in PHP, the option silently does
	 * nothing, and the only symptom is a price that does not look the way it
	 * was asked to.
	 *
	 * @since 1.3.0
	 *
	 * @param array<string, mixed> $options What the caller passed.
	 *
	 * @return void
	 */
	private static function warn_about_unknown_keys( array $options ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! function_exists( '_doing_it_wrong' ) ) {
			return;
		}

		$unknown = self::unknown_keys( $options );

		if ( array() === $unknown ) {
			return;
		}

		_doing_it_wrong(
			__METHOD__,
			esc_html(
				sprintf(
					/* translators: 1: comma-separated list of option keys, 2: comma-separated list of keys that are read. */
					__( 'Money was given options nothing reads: %1$s. The options are: %2$s.', 'arraypress' ),
					implode( ', ', $unknown ),
					implode( ', ', self::keys() )
				)
			),
			'1.3.0'
		);
	}
}
