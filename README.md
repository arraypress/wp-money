# WP Money

Money formatting and parsing for integer minor units. 136 currencies with correct decimal exponents — including the twenty where dividing by 100 is wrong. Zero dependencies.

## Why

Store money as an integer number of minor units, always. Floats cannot represent `0.10`, and the error compounds through every total, tax line and refund until a report is out by a penny nobody can find.

That leaves one question the integer does not answer: how many minor units are in a major one? Almost every implementation assumes 100. It is 1 for yen, and 1000 for Bahraini dinar — so ¥1000 sent as `100000` charges a hundred times too much, and BD 1.500 sent as `150` charges a tenth.

Twenty of the 136 currencies here are not two-decimal. Getting that wrong is not a rounding bug, it is a two-orders-of-magnitude billing error.

## Features

- 💰 **Integer arithmetic throughout** — no float ever touches an amount.
- 🔢 **Correct exponents** — 0 for JPY and the CFA francs, 3 for BHD, KWD, OMR, TND.
- 🏦 **Stripe's special cases** — ISK, HUF, TWD and UGX, where the API's expectation differs from the ISO exponent.
- 📉 **Minimum and maximum charge amounts** — per currency, so you can reject an uncharageable total before the gateway does.
- 🧮 **Fair allocation** — split an amount into parts with the remainder distributed, never lost.
- 🖊️ **Forgiving parsing** — `€1.234,56` and `$1,234.56` both come back as `123456`.
- 🌐 **Locale formatting** — via ext-intl when present, with a sensible fallback when not.


## Rates

A tax rate, a commission, a discount. Each is a number that means one of two
entirely different things, and the number cannot say which — `20` is twenty
percent or twenty pounds, and on a £10 order that is the difference between £8
and £0.

So a rate is the pair, and nothing here accepts a bare number and guesses:

```php
use ArrayPress\\Money\\Rate;

Rate::format( 20, 'percent' );              // '20%'
Rate::format( 20, 'flat', 'USD' );          // '$0.20'

Rate::applied_to( 1000, 20, 'percent' );    // 200
Rate::applied_to( 1000, 20, 'flat' );       // 20
```

An unrecognised kind reads as money, deliberately: showing `20` where `£0.20`
belongs is a display bug, showing `20%` where `£0.20` belongs is a pricing one.

Deductions are bounded by the amount and never negative, and percentages round
rather than truncate — 8.875% of £19.99 truncated loses a penny on every line.



## Using it

Two functions cover almost everything, and both take the same options array:

```php
format_money( 4999 );                                  // '$49.99'
format_money( 1000, [ 'currency' => 'JPY' ] );         // '¥1,000'
format_money( 4999, [ 'code' => true ] );              // '$49.99 USD'
format_money( 999,  [ 'interval' => 'month' ] );       // '$9.99/mo'
format_money( 999,  [ 'interval' => 'month', 'interval_count' => 3 ] );
                                                       // '$9.99 every 3 months'

render_price( 4999 );                                  // <span class="price">$49.99</span>
render_price( 1999, [ 'compare_at' => 2999 ] );        // struck through, see below
```

| Key | Default | What it does |
|-----|---------|--------------|
| `currency` | store's | ISO-4217 code |
| `symbol` | `true` | Show the currency's symbol |
| `code` | `false` | Name the currency after the amount |
| `separators` | `true` | Group the thousands |
| `interval` | `''` | `day`, `week`, `month` or `year` |
| `interval_count` | `1` | How many intervals between charges |
| `compare_at` | `null` | *(render only)* what it cost before |
| `class` | `'price'` | *(render only)* wrapper class |

They combine, which is why this is an array and not an enum — an enum cannot
express "symbol and code", which an invoice wants.

An option nothing reads raises `_doing_it_wrong()` under `WP_DEBUG`. That is
the usual objection to configuration arrays answered: `compare_at` passed to
`format_money()` rather than `render_price()` is a plausible thing to write, it
would otherwise do nothing, and the only symptom would be a sale price
rendering as an ordinary one.

The other three globals:

```php
money_currency();                    // the store's code, filterable
sanitize_money( '$1,999.00' );       // 199900
money_input_value( 199900 );         // '1999.00' — round-trips through a form
```

Everything rarer is on the classes: `Currencies::symbol()`, `Money::to_float()`,
`Money::allocate()`, `Rate::applied_to()`, `Recurring::options()`.

## Sale prices

```php
render_price( 1999, [ 'compare_at' => 2999 ] );
// <span class="price price--sale">
//   <del><span class="screen-reader-text">Regular price</span>$29.99</del>
//   <ins><span class="screen-reader-text">Sale price</span>$19.99</ins>
// </span>

Money::saving_percentage( 1999, 2999 );   // 33 — for the badge
```

`<del>` and `<ins>` rather than spans with classes: that is what the elements
mean, and it is the only version a screen reader can make sense of. Without the
hidden labels the two numbers are read out one after another with nothing to
say which is which, and a struck-through price sounds exactly like the price.

A `compare_at` at or below the amount renders as an ordinary price — striking
through a number that is the same or smaller reads as an increase, and equal
prices arrive routinely from a product whose sale has ended.

The percentage rounds **down**, so a badge never overstates the saving.

For a discount rather than a fixed sale price:

```php
$sale = $regular - Rate::applied_to( $regular, 20, 'percent' );
render_price( $sale, [ 'compare_at' => $regular ] );
```

## Requirements

PHP 8.3+ and WordPress (`ext-intl` optional, for locale-aware formatting)

## Installation

```bash
composer require arraypress/wp-money
```

## Usage

```php
use ArrayPress\Money\Money;

Money::format( 4999, 'USD' );            // '$49.99'
Money::format( 1000, 'JPY' );            // '¥1,000'
Money::format( 1500, 'BHD' );            // 'BD 1.500'
Money::format_with_code( 4999, 'USD' );  // '49.99 USD'
Money::decimal( 4999, 'USD' );           // '49.99'
Money::input_value( 4999, 'USD' );       // '49.99' — no separators, for a form field
```

### Parsing

```php
Money::parse( '$1,234.56', 'USD' );   // 123456
Money::parse( '€1.234,56', 'EUR' );   // 123456
Money::parse( '1000', 'JPY' );        // 1000
Money::parse( 'nonsense', 'USD' );    // 0
```

Throws `InvalidArgumentException` for an amount too large to hold in an integer — a twenty-digit figure typed into a price field. Clamping it silently would be worse.

### Validating an amount

```php
Money::is_valid_amount( 150, 'JPY' );        // false — JPY has no minor unit
Money::round_to_valid( 150, 'JPY' );         // 150 → nearest representable
Money::is_chargeable( 20, 'USD' );           // false — under Stripe's minimum
Money::why_not_chargeable( 20, 'USD' );      // a sentence you can show the buyer
```

### Splitting

```php
Money::percentage( 4999, 20.0 );   // 1000 — VAT, rounded once
Money::allocate( 1000, 3 );        // [334, 333, 333] — remainder distributed, total preserved
```

`allocate()` is the one to reach for when splitting a discount across line items or a payout across parties. Dividing and rounding each part independently loses or invents money.

### Metadata

```php
use ArrayPress\Money\Currencies;

Currencies::decimals( 'BHD' );        // 3
Currencies::symbol( 'GBP' );          // '£'
Currencies::name( 'JPY' );            // 'Japanese Yen'
Currencies::is_zero_decimal( 'KRW' ); // true
Currencies::for_country( 'CH' );      // ['CHF']
Currencies::options();                // code => label, for a select
```

## The currencies that are not two-decimal

| Exponent | Currencies |
|---|---|
| 0 | BIF, CLP, DJF, GNF, JPY, KMF, KRW, MGA, PYG, RWF, UGX, VND, VUV, XAF, XOF, XPF |
| 3 | BHD, IQD, JOD, KWD, LYD, OMR, TND |

Four more are worth knowing about because Stripe treats them differently from ISO 4217: **ISK** and **HUF** are zero-decimal at ISO but must be sent as multiples of 100; **TWD** likewise; **UGX** is zero-decimal but historically was sent otherwise. The tables here follow what the API actually expects.

## Security

Currency codes arrive from query strings more often than people expect — `?currency=` on a headless checkout. An unrecognised code is never echoed back into formatted output; see [SECURITY.md](SECURITY.md).

## Testing

```bash
composer install
composer test
```

93 tests, 873 assertions — every exponent verified against ISO 4217 and Stripe's published tables, plus allocation invariants (the parts always sum to the whole) and the hostile-input cases.

## License

GPL-2.0-or-later
