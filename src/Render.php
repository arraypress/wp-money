<?php
/**
 * Rendering money into a page.
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
 * Kept apart from Money on purpose: formatting is arithmetic and has no
 * opinion about HTML, and the moment the two live together somebody escapes
 * twice or not at all. Everything here escapes exactly once, at the end.
 *
 * @since 1.0.0
 */
final class Render {

	/**
	 * An amount, wrapped and escaped.
	 *
	 * The symbols in the dataset are real glyphs -- `د.إ`, `₼`, `৳` -- so the
	 * escaping has to be esc_html() rather than anything that strips
	 * non-ASCII, and the output has to be UTF-8 all the way down.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 * @param string              $class   Class for the wrapper.
	 * @param array               $options How to write the amount. See Options.
	 *
	 * @return string
	 */
	public static function amount(
		int $amount,
		string $code,
		string $class = 'price',
		array $options = array()
	): string {
		return sprintf(
			'<span class="%s">%s</span>',
			esc_attr( $class ),
			esc_html( Money::format( $amount, $code, $options ) )
		);
	}

	/**
	 * A reduced price, with what it cost before struck through.
	 *
	 * `<del>` and `<ins>` rather than a span with a class, because that is
	 * what the elements mean and it is the only version a screen reader can
	 * make sense of. The visually-hidden labels are there for the same
	 * reason: without them the two numbers are read out one after the other
	 * with nothing to say which is which, and a struck-through price sounds
	 * exactly like the price.
	 *
	 * A compare-at at or below the amount renders as an ordinary price. It is
	 * not a reduction, and striking through a number that is the same or
	 * smaller reads as an increase.
	 *
	 * @since 1.4.0
	 *
	 * @param int                  $amount     What is being charged.
	 * @param int                  $compare_at What it cost before.
	 * @param string               $code       ISO-4217 code.
	 * @param string               $class      Class for the wrapper.
	 * @param array<string, mixed> $options    How to write the amounts. See Options.
	 *
	 * @return string
	 */
	public static function sale(
		int $amount,
		int $compare_at,
		string $code,
		string $class = 'price',
		array $options = array()
	): string {
		if ( $compare_at <= $amount ) {
			return self::amount( $amount, $code, $class, $options );
		}

		return sprintf(
			'<span class="%1$s %1$s--sale"><del>%2$s%3$s</del> <ins>%4$s%5$s</ins></span>',
			esc_attr( $class ),
			self::label( __( 'Regular price', 'arraypress' ) ),
			esc_html( Money::format( $compare_at, $code, $options ) ),
			self::label( __( 'Sale price', 'arraypress' ) ),
			esc_html( Money::format( $amount, $code, $options ) )
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
