# WordPress Money

Money as integer minor units, formatted correctly for 136 currencies.

## What it does

Storing money as a float is how a total ends up at £9.999999999. Storing it as
an integer number of pence is correct, but then every currency needs its own
rules: yen has no decimal places, Kuwaiti dinar has three, and Hungarian forint
takes a payout only in whole units.

This holds those rules. Amounts go in and out as integers; formatting, parsing
and validation know what each currency expects.

## Features

- Format an integer amount for any of 136 currencies, with the right decimals
- Parse a typed amount back to minor units, whatever separators were used
- Check an amount is chargeable, and say why it is not
- Render a sale price with the old price struck through and a screen-reader label
- Work out a saving percentage, or apply a flat or percentage discount
- Split an amount between parts without losing a penny to rounding
- Describe a subscription interval — "/month", "every 3 weeks"
- Global helpers for the common cases: `format_money()`, `sanitize_money()`

## Installation

```bash
composer require arraypress/wp-money
```

## Quick start

```php
use ArrayPress\Money\Money;

// 4999 pence, in the store's currency.
echo Money::format( 4999 );                      // £49.99
echo Money::format( 4999, [ 'currency' => 'JPY' ] );  // ¥4,999  (no decimals)

// What the customer typed, back to minor units.
$amount = Money::parse( '1,299.50', 'GBP' );     // 129950

// Would the gateway take this?
if ( ! Money::is_chargeable( $amount, 'GBP' ) ) {
    $why = Money::why_not_chargeable( $amount, 'GBP' );
}

// A sale price, marked up for a listing.
echo render_price( 3999, [ 'compare_at' => 4999 ] );
```

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
