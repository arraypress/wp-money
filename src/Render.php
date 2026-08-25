<?php
/**
 * Putting a price in a page.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;

/**
 * Class Render
 *
 * Money::format() returns text. This puts it in a page.
 *
 * Kept apart on purpose: formatting is arithmetic and has no opinion about
 * markup, and the moment the two live together somebody escapes twice or not
 * at all. Everything here escapes exactly once, at the end.
 *
 * @since 1.0.0
 */
final class Render {

	/**
	 * A price, escaped and wrapped.
	 *
	 * Give it `compare_at` and it renders as a reduction instead, with what
	 * it cost before struck through.
	 *
	 * @since 1.0.0
	 *
	 * @param int                  $amount  Amount in the smallest currency unit.
	 * @param array<string, mixed> $options How to write it. See Options.
	 *
	 * @return string
	 */
	public static function price( int $amount, array $options = array() ): string {
		$options    = Options::parse( $options, true );
		$class      = (string) $options['class'];
		$compare_at = $options['compare_at'];

		$format = array_diff_key( $options, array(
			'compare_at' => null,
			'class' => null,
		) );

		// Not a reduction: the same or a lower "before" price reads as an
		// increase when struck through, and equal prices arrive routinely
		// from a product whose sale has ended.
		if ( ! is_int( $compare_at ) || $compare_at <= $amount ) {
			return sprintf(
				'<span class="%s">%s</span>',
				esc_attr( $class ),
				esc_html( Money::format( $amount, $format ) )
			);
		}

		// <del> and <ins> rather than spans with classes: that is what the
		// elements mean, and it is the only version a screen reader can make
		// sense of. The hidden labels matter for the same reason -- without
		// them the two numbers are read out one after another with nothing to
		// say which is which, and a struck-through price sounds exactly like
		// the price.
		return sprintf(
			'<span class="%1$s %1$s--sale"><del>%2$s%3$s</del> <ins>%4$s%5$s</ins></span>',
			esc_attr( $class ),
			self::label( __( 'Regular price', 'arraypress' ) ),
			esc_html( Money::format( $compare_at, $format ) ),
			self::label( __( 'Sale price', 'arraypress' ) ),
			esc_html( Money::format( $amount, $format ) )
		);
	}

	/**
	 * A label only a screen reader hears.
	 *
	 * `screen-reader-text` is core's class and is styled by every theme that
	 * loads core's stylesheet, which on the front end is most of them.
	 *
	 * @param string $text The label.
	 *
	 * @return string
	 */
	private static function label( string $text ): string {
		return sprintf( '<span class="screen-reader-text">%s</span>', esc_html( $text ) );
	}
}
