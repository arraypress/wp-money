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
	 * @param string $class  Class for the wrapper.
	 *
	 * @return string
	 */
	public static function amount( int $amount, string $code, string $class = 'price' ): string {
		return sprintf(
			'<span class="%s">%s</span>',
			esc_attr( $class ),
			esc_html( Money::format( $amount, $code ) )
		);
	}

	/**
	 * An amount with the code beside it, for a page showing more than one
	 * currency.
	 *
	 * `$49.99` is ambiguous across the dollar currencies; `$49.99 USD` is not.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount Amount in the smallest currency unit.
	 * @param string $code   ISO-4217 code.
	 * @param string $class  Class for the wrapper.
	 *
	 * @return string
	 */
	public static function amount_with_code( int $amount, string $code, string $class = 'price' ): string {
		return sprintf(
			'<span class="%s">%s</span>',
			esc_attr( $class ),
			esc_html( Money::format_with_code( $amount, $code ) )
		);
	}
}
