<?php
/**
 * Currency dataset.
 *
 * @package   ArrayPress\Money
 * @copyright Copyright (c) 2026, ArrayPress Limited
 * @license   GPL-2.0-or-later
 * @since     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Money;

/**
 * Class Currencies
 *
 * The dataset {@see Money} formats against: 136 currencies, each with a
 * name, symbol, decimal exponent, locale, and issuing country.
 *
 * The exponent is the field that matters and the one people assume. It
 * is 2 for most currencies, 0 for fifteen of them, and 3 for five. Code
 * that divides by 100 is silently wrong for twenty currencies — a
 * ¥1,000 order becomes ¥10.00, and a BHD 1.000 order becomes BD 1000.
 *
 * Kept in step with Stripe's supported-currency list.
 *
 * @since 1.0.0
 */
final readonly class Currencies {

	/**
	 * Keyed by lowercase ISO-4217 code.
	 *
	 * @var array<string, array{name: string, symbol: string, decimals: int, locale: string, country: string, country_name: string}>
	 */
	private const CURRENCIES = array(
		'aed' => array( 'name' => 'United Arab Emirates Dirham', 'symbol' => 'د.إ', 'decimals' => 2, 'locale' => 'ar_AE', 'country' => 'AE', 'country_name' => 'United Arab Emirates' ),
		'afn' => array( 'name' => 'Afghan Afghani', 'symbol' => '؋', 'decimals' => 2, 'locale' => 'fa_AF', 'country' => 'AF', 'country_name' => 'Afghanistan' ),
		'all' => array( 'name' => 'Albanian Lek', 'symbol' => 'L', 'decimals' => 2, 'locale' => 'sq_AL', 'country' => 'AL', 'country_name' => 'Albania' ),
		'amd' => array( 'name' => 'Armenian Dram', 'symbol' => '֏', 'decimals' => 2, 'locale' => 'hy_AM', 'country' => 'AM', 'country_name' => 'Armenia' ),
		'ang' => array( 'name' => 'Netherlands Antillean Guilder', 'symbol' => 'ƒ', 'decimals' => 2, 'locale' => 'nl_CW', 'country' => 'CW', 'country_name' => 'Curaçao' ),
		'aoa' => array( 'name' => 'Angolan Kwanza', 'symbol' => 'Kz', 'decimals' => 2, 'locale' => 'pt_AO', 'country' => 'AO', 'country_name' => 'Angola' ),
		'ars' => array( 'name' => 'Argentine Peso', 'symbol' => '$', 'decimals' => 2, 'locale' => 'es_AR', 'country' => 'AR', 'country_name' => 'Argentina' ),
		'aud' => array( 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimals' => 2, 'locale' => 'en_AU', 'country' => 'AU', 'country_name' => 'Australia' ),
		'awg' => array( 'name' => 'Aruban Florin', 'symbol' => 'ƒ', 'decimals' => 2, 'locale' => 'nl_AW', 'country' => 'AW', 'country_name' => 'Aruba' ),
		'azn' => array( 'name' => 'Azerbaijani Manat', 'symbol' => '₼', 'decimals' => 2, 'locale' => 'az_AZ', 'country' => 'AZ', 'country_name' => 'Azerbaijan' ),
		'bam' => array( 'name' => 'Bosnia-Herzegovina Convertible Mark', 'symbol' => 'KM', 'decimals' => 2, 'locale' => 'bs_BA', 'country' => 'BA', 'country_name' => 'Bosnia and Herzegovina' ),
		'bbd' => array( 'name' => 'Barbadian Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_BB', 'country' => 'BB', 'country_name' => 'Barbados' ),
		'bdt' => array( 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'decimals' => 2, 'locale' => 'bn_BD', 'country' => 'BD', 'country_name' => 'Bangladesh' ),
		'bgn' => array( 'name' => 'Bulgarian Lev', 'symbol' => 'лв', 'decimals' => 2, 'locale' => 'bg_BG', 'country' => 'BG', 'country_name' => 'Bulgaria' ),
		'bhd' => array( 'name' => 'Bahraini Dinar', 'symbol' => 'BD', 'decimals' => 3, 'locale' => 'ar_BH', 'country' => 'BH', 'country_name' => 'Bahrain' ),
		'bif' => array( 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'decimals' => 0, 'locale' => 'rn_BI', 'country' => 'BI', 'country_name' => 'Burundi' ),
		'bmd' => array( 'name' => 'Bermudan Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_BM', 'country' => 'BM', 'country_name' => 'Bermuda' ),
		'bnd' => array( 'name' => 'Brunei Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'ms_BN', 'country' => 'BN', 'country_name' => 'Brunei' ),
		'bob' => array( 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs', 'decimals' => 2, 'locale' => 'es_BO', 'country' => 'BO', 'country_name' => 'Bolivia' ),
		'brl' => array( 'name' => 'Brazilian Real', 'symbol' => 'R$', 'decimals' => 2, 'locale' => 'pt_BR', 'country' => 'BR', 'country_name' => 'Brazil' ),
		'bsd' => array( 'name' => 'Bahamian Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_BS', 'country' => 'BS', 'country_name' => 'Bahamas' ),
		'bwp' => array( 'name' => 'Botswanan Pula', 'symbol' => 'P', 'decimals' => 2, 'locale' => 'en_BW', 'country' => 'BW', 'country_name' => 'Botswana' ),
		'bzd' => array( 'name' => 'Belize Dollar', 'symbol' => 'BZ$', 'decimals' => 2, 'locale' => 'en_BZ', 'country' => 'BZ', 'country_name' => 'Belize' ),
		'cad' => array( 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimals' => 2, 'locale' => 'en_CA', 'country' => 'CA', 'country_name' => 'Canada' ),
		'cdf' => array( 'name' => 'Congolese Franc', 'symbol' => 'FC', 'decimals' => 2, 'locale' => 'fr_CD', 'country' => 'CD', 'country_name' => 'Democratic Republic of the Congo' ),
		'chf' => array( 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimals' => 2, 'locale' => 'de_CH', 'country' => 'CH', 'country_name' => 'Switzerland' ),
		'clp' => array( 'name' => 'Chilean Peso', 'symbol' => '$', 'decimals' => 0, 'locale' => 'es_CL', 'country' => 'CL', 'country_name' => 'Chile' ),
		'cny' => array( 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimals' => 2, 'locale' => 'zh_CN', 'country' => 'CN', 'country_name' => 'China' ),
		'cop' => array( 'name' => 'Colombian Peso', 'symbol' => '$', 'decimals' => 2, 'locale' => 'es_CO', 'country' => 'CO', 'country_name' => 'Colombia' ),
		'crc' => array( 'name' => 'Costa Rican Colón', 'symbol' => '₡', 'decimals' => 2, 'locale' => 'es_CR', 'country' => 'CR', 'country_name' => 'Costa Rica' ),
		'cve' => array( 'name' => 'Cape Verdean Escudo', 'symbol' => '$', 'decimals' => 2, 'locale' => 'pt_CV', 'country' => 'CV', 'country_name' => 'Cape Verde' ),
		'czk' => array( 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'decimals' => 2, 'locale' => 'cs_CZ', 'country' => 'CZ', 'country_name' => 'Czechia' ),
		'djf' => array( 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0, 'locale' => 'fr_DJ', 'country' => 'DJ', 'country_name' => 'Djibouti' ),
		'dkk' => array( 'name' => 'Danish Krone', 'symbol' => 'kr', 'decimals' => 2, 'locale' => 'da_DK', 'country' => 'DK', 'country_name' => 'Denmark' ),
		'dop' => array( 'name' => 'Dominican Peso', 'symbol' => 'RD$', 'decimals' => 2, 'locale' => 'es_DO', 'country' => 'DO', 'country_name' => 'Dominican Republic' ),
		'dzd' => array( 'name' => 'Algerian Dinar', 'symbol' => 'DA', 'decimals' => 2, 'locale' => 'ar_DZ', 'country' => 'DZ', 'country_name' => 'Algeria' ),
		'egp' => array( 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimals' => 2, 'locale' => 'ar_EG', 'country' => 'EG', 'country_name' => 'Egypt' ),
		'etb' => array( 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'decimals' => 2, 'locale' => 'am_ET', 'country' => 'ET', 'country_name' => 'Ethiopia' ),
		'eur' => array( 'name' => 'Euro', 'symbol' => '€', 'decimals' => 2, 'locale' => 'de_DE', 'country' => 'EU', 'country_name' => 'Eurozone' ),
		'fjd' => array( 'name' => 'Fijian Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_FJ', 'country' => 'FJ', 'country_name' => 'Fiji' ),
		'gbp' => array( 'name' => 'British Pound', 'symbol' => '£', 'decimals' => 2, 'locale' => 'en_GB', 'country' => 'GB', 'country_name' => 'United Kingdom' ),
		'gel' => array( 'name' => 'Georgian Lari', 'symbol' => '₾', 'decimals' => 2, 'locale' => 'ka_GE', 'country' => 'GE', 'country_name' => 'Georgia' ),
		'ghs' => array( 'name' => 'Ghanaian Cedi', 'symbol' => '₵', 'decimals' => 2, 'locale' => 'en_GH', 'country' => 'GH', 'country_name' => 'Ghana' ),
		'gip' => array( 'name' => 'Gibraltar Pound', 'symbol' => '£', 'decimals' => 2, 'locale' => 'en_GI', 'country' => 'GI', 'country_name' => 'Gibraltar' ),
		'gmd' => array( 'name' => 'Gambian Dalasi', 'symbol' => 'D', 'decimals' => 2, 'locale' => 'en_GM', 'country' => 'GM', 'country_name' => 'Gambia' ),
		'gnf' => array( 'name' => 'Guinean Franc', 'symbol' => 'FG', 'decimals' => 0, 'locale' => 'fr_GN', 'country' => 'GN', 'country_name' => 'Guinea' ),
		'gtq' => array( 'name' => 'Guatemalan Quetzal', 'symbol' => 'Q', 'decimals' => 2, 'locale' => 'es_GT', 'country' => 'GT', 'country_name' => 'Guatemala' ),
		'gyd' => array( 'name' => 'Guyanaese Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_GY', 'country' => 'GY', 'country_name' => 'Guyana' ),
		'hkd' => array( 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'decimals' => 2, 'locale' => 'zh_HK', 'country' => 'HK', 'country_name' => 'Hong Kong' ),
		'hnl' => array( 'name' => 'Honduran Lempira', 'symbol' => 'L', 'decimals' => 2, 'locale' => 'es_HN', 'country' => 'HN', 'country_name' => 'Honduras' ),
		'htg' => array( 'name' => 'Haitian Gourde', 'symbol' => 'G', 'decimals' => 2, 'locale' => 'fr_HT', 'country' => 'HT', 'country_name' => 'Haiti' ),
		'huf' => array( 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'decimals' => 2, 'locale' => 'hu_HU', 'country' => 'HU', 'country_name' => 'Hungary' ),
		'idr' => array( 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimals' => 2, 'locale' => 'id_ID', 'country' => 'ID', 'country_name' => 'Indonesia' ),
		'ils' => array( 'name' => 'Israeli New Shekel', 'symbol' => '₪', 'decimals' => 2, 'locale' => 'he_IL', 'country' => 'IL', 'country_name' => 'Israel' ),
		'inr' => array( 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimals' => 2, 'locale' => 'en_IN', 'country' => 'IN', 'country_name' => 'India' ),
		'isk' => array( 'name' => 'Icelandic Króna', 'symbol' => 'kr', 'decimals' => 2, 'locale' => 'is_IS', 'country' => 'IS', 'country_name' => 'Iceland' ),
		'jmd' => array( 'name' => 'Jamaican Dollar', 'symbol' => 'J$', 'decimals' => 2, 'locale' => 'en_JM', 'country' => 'JM', 'country_name' => 'Jamaica' ),
		'jod' => array( 'name' => 'Jordanian Dinar', 'symbol' => 'JD', 'decimals' => 3, 'locale' => 'ar_JO', 'country' => 'JO', 'country_name' => 'Jordan' ),
		'jpy' => array( 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimals' => 0, 'locale' => 'ja_JP', 'country' => 'JP', 'country_name' => 'Japan' ),
		'kes' => array( 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'decimals' => 2, 'locale' => 'en_KE', 'country' => 'KE', 'country_name' => 'Kenya' ),
		'kgs' => array( 'name' => 'Kyrgystani Som', 'symbol' => 'лв', 'decimals' => 2, 'locale' => 'ky_KG', 'country' => 'KG', 'country_name' => 'Kyrgyzstan' ),
		'khr' => array( 'name' => 'Cambodian Riel', 'symbol' => '៛', 'decimals' => 2, 'locale' => 'km_KH', 'country' => 'KH', 'country_name' => 'Cambodia' ),
		'kmf' => array( 'name' => 'Comorian Franc', 'symbol' => 'CF', 'decimals' => 0, 'locale' => 'fr_KM', 'country' => 'KM', 'country_name' => 'Comoros' ),
		'krw' => array( 'name' => 'South Korean Won', 'symbol' => '₩', 'decimals' => 0, 'locale' => 'ko_KR', 'country' => 'KR', 'country_name' => 'South Korea' ),
		'kwd' => array( 'name' => 'Kuwaiti Dinar', 'symbol' => 'KD', 'decimals' => 3, 'locale' => 'ar_KW', 'country' => 'KW', 'country_name' => 'Kuwait' ),
		'kyd' => array( 'name' => 'Cayman Islands Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_KY', 'country' => 'KY', 'country_name' => 'Cayman Islands' ),
		'kzt' => array( 'name' => 'Kazakhstani Tenge', 'symbol' => '₸', 'decimals' => 2, 'locale' => 'kk_KZ', 'country' => 'KZ', 'country_name' => 'Kazakhstan' ),
		'lak' => array( 'name' => 'Lao Kip', 'symbol' => '₭', 'decimals' => 2, 'locale' => 'lo_LA', 'country' => 'LA', 'country_name' => 'Laos' ),
		'lbp' => array( 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل', 'decimals' => 2, 'locale' => 'ar_LB', 'country' => 'LB', 'country_name' => 'Lebanon' ),
		'lkr' => array( 'name' => 'Sri Lankan Rupee', 'symbol' => 'Rs', 'decimals' => 2, 'locale' => 'si_LK', 'country' => 'LK', 'country_name' => 'Sri Lanka' ),
		'lrd' => array( 'name' => 'Liberian Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_LR', 'country' => 'LR', 'country_name' => 'Liberia' ),
		'lsl' => array( 'name' => 'Lesotho Loti', 'symbol' => 'L', 'decimals' => 2, 'locale' => 'en_LS', 'country' => 'LS', 'country_name' => 'Lesotho' ),
		'mad' => array( 'name' => 'Moroccan Dirham', 'symbol' => 'MAD', 'decimals' => 2, 'locale' => 'ar_MA', 'country' => 'MA', 'country_name' => 'Morocco' ),
		'mdl' => array( 'name' => 'Moldovan Leu', 'symbol' => 'L', 'decimals' => 2, 'locale' => 'ro_MD', 'country' => 'MD', 'country_name' => 'Moldova' ),
		'mga' => array( 'name' => 'Malagasy Ariary', 'symbol' => 'Ar', 'decimals' => 0, 'locale' => 'mg_MG', 'country' => 'MG', 'country_name' => 'Madagascar' ),
		'mkd' => array( 'name' => 'Macedonian Denar', 'symbol' => 'ден', 'decimals' => 2, 'locale' => 'mk_MK', 'country' => 'MK', 'country_name' => 'North Macedonia' ),
		'mmk' => array( 'name' => 'Myanmar Kyat', 'symbol' => 'K', 'decimals' => 2, 'locale' => 'my_MM', 'country' => 'MM', 'country_name' => 'Myanmar' ),
		'mnt' => array( 'name' => 'Mongolian Tögrög', 'symbol' => '₮', 'decimals' => 2, 'locale' => 'mn_MN', 'country' => 'MN', 'country_name' => 'Mongolia' ),
		'mur' => array( 'name' => 'Mauritian Rupee', 'symbol' => '₨', 'decimals' => 2, 'locale' => 'en_MU', 'country' => 'MU', 'country_name' => 'Mauritius' ),
		'mvr' => array( 'name' => 'Maldivian Rufiyaa', 'symbol' => 'Rf', 'decimals' => 2, 'locale' => 'dv_MV', 'country' => 'MV', 'country_name' => 'Maldives' ),
		'mwk' => array( 'name' => 'Malawian Kwacha', 'symbol' => 'MK', 'decimals' => 2, 'locale' => 'en_MW', 'country' => 'MW', 'country_name' => 'Malawi' ),
		'mxn' => array( 'name' => 'Mexican Peso', 'symbol' => '$', 'decimals' => 2, 'locale' => 'es_MX', 'country' => 'MX', 'country_name' => 'Mexico' ),
		'myr' => array( 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimals' => 2, 'locale' => 'ms_MY', 'country' => 'MY', 'country_name' => 'Malaysia' ),
		'mzn' => array( 'name' => 'Mozambican Metical', 'symbol' => 'MT', 'decimals' => 2, 'locale' => 'pt_MZ', 'country' => 'MZ', 'country_name' => 'Mozambique' ),
		'nad' => array( 'name' => 'Namibian Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_NA', 'country' => 'NA', 'country_name' => 'Namibia' ),
		'ngn' => array( 'name' => 'Nigerian Naira', 'symbol' => '₦', 'decimals' => 2, 'locale' => 'en_NG', 'country' => 'NG', 'country_name' => 'Nigeria' ),
		'nio' => array( 'name' => 'Nicaraguan Córdoba', 'symbol' => 'C$', 'decimals' => 2, 'locale' => 'es_NI', 'country' => 'NI', 'country_name' => 'Nicaragua' ),
		'nok' => array( 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'decimals' => 2, 'locale' => 'nb_NO', 'country' => 'NO', 'country_name' => 'Norway' ),
		'npr' => array( 'name' => 'Nepalese Rupee', 'symbol' => '₨', 'decimals' => 2, 'locale' => 'ne_NP', 'country' => 'NP', 'country_name' => 'Nepal' ),
		'nzd' => array( 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'decimals' => 2, 'locale' => 'en_NZ', 'country' => 'NZ', 'country_name' => 'New Zealand' ),
		'omr' => array( 'name' => 'Omani Rial', 'symbol' => 'ر.ع.', 'decimals' => 3, 'locale' => 'ar_OM', 'country' => 'OM', 'country_name' => 'Oman' ),
		'pab' => array( 'name' => 'Panamanian Balboa', 'symbol' => 'B/', 'decimals' => 2, 'locale' => 'es_PA', 'country' => 'PA', 'country_name' => 'Panama' ),
		'pen' => array( 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'decimals' => 2, 'locale' => 'es_PE', 'country' => 'PE', 'country_name' => 'Peru' ),
		'pgk' => array( 'name' => 'Papua New Guinean Kina', 'symbol' => 'K', 'decimals' => 2, 'locale' => 'en_PG', 'country' => 'PG', 'country_name' => 'Papua New Guinea' ),
		'php' => array( 'name' => 'Philippine Peso', 'symbol' => '₱', 'decimals' => 2, 'locale' => 'en_PH', 'country' => 'PH', 'country_name' => 'Philippines' ),
		'pkr' => array( 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'decimals' => 2, 'locale' => 'ur_PK', 'country' => 'PK', 'country_name' => 'Pakistan' ),
		'pln' => array( 'name' => 'Polish Złoty', 'symbol' => 'zł', 'decimals' => 2, 'locale' => 'pl_PL', 'country' => 'PL', 'country_name' => 'Poland' ),
		'pyg' => array( 'name' => 'Paraguayan Guarani', 'symbol' => '₲', 'decimals' => 0, 'locale' => 'es_PY', 'country' => 'PY', 'country_name' => 'Paraguay' ),
		'qar' => array( 'name' => 'Qatari Riyal', 'symbol' => 'QR', 'decimals' => 2, 'locale' => 'ar_QA', 'country' => 'QA', 'country_name' => 'Qatar' ),
		'ron' => array( 'name' => 'Romanian Leu', 'symbol' => 'lei', 'decimals' => 2, 'locale' => 'ro_RO', 'country' => 'RO', 'country_name' => 'Romania' ),
		'rsd' => array( 'name' => 'Serbian Dinar', 'symbol' => 'din', 'decimals' => 2, 'locale' => 'sr_RS', 'country' => 'RS', 'country_name' => 'Serbia' ),
		'rub' => array( 'name' => 'Russian Ruble', 'symbol' => '₽', 'decimals' => 2, 'locale' => 'ru_RU', 'country' => 'RU', 'country_name' => 'Russia' ),
		'rwf' => array( 'name' => 'Rwandan Franc', 'symbol' => 'FRw', 'decimals' => 0, 'locale' => 'rw_RW', 'country' => 'RW', 'country_name' => 'Rwanda' ),
		'sar' => array( 'name' => 'Saudi Riyal', 'symbol' => 'SR', 'decimals' => 2, 'locale' => 'ar_SA', 'country' => 'SA', 'country_name' => 'Saudi Arabia' ),
		'sbd' => array( 'name' => 'Solomon Islands Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_SB', 'country' => 'SB', 'country_name' => 'Solomon Islands' ),
		'scr' => array( 'name' => 'Seychellois Rupee', 'symbol' => '₨', 'decimals' => 2, 'locale' => 'en_SC', 'country' => 'SC', 'country_name' => 'Seychelles' ),
		'sek' => array( 'name' => 'Swedish Krona', 'symbol' => 'kr', 'decimals' => 2, 'locale' => 'sv_SE', 'country' => 'SE', 'country_name' => 'Sweden' ),
		'sgd' => array( 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'decimals' => 2, 'locale' => 'en_SG', 'country' => 'SG', 'country_name' => 'Singapore' ),
		'sll' => array( 'name' => 'Sierra Leonean Leone', 'symbol' => 'Le', 'decimals' => 2, 'locale' => 'en_SL', 'country' => 'SL', 'country_name' => 'Sierra Leone' ),
		'sos' => array( 'name' => 'Somali Shilling', 'symbol' => 'S', 'decimals' => 2, 'locale' => 'so_SO', 'country' => 'SO', 'country_name' => 'Somalia' ),
		'srd' => array( 'name' => 'Surinamese Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'nl_SR', 'country' => 'SR', 'country_name' => 'Suriname' ),
		'stn' => array( 'name' => 'São Tomé & Príncipe Dobra', 'symbol' => 'Db', 'decimals' => 2, 'locale' => 'pt_ST', 'country' => 'ST', 'country_name' => 'São Tomé and Príncipe' ),
		'szl' => array( 'name' => 'Swazi Lilangeni', 'symbol' => 'L', 'decimals' => 2, 'locale' => 'en_SZ', 'country' => 'SZ', 'country_name' => 'Eswatini' ),
		'thb' => array( 'name' => 'Thai Baht', 'symbol' => '฿', 'decimals' => 2, 'locale' => 'th_TH', 'country' => 'TH', 'country_name' => 'Thailand' ),
		'tjs' => array( 'name' => 'Tajikistani Somoni', 'symbol' => 'SM', 'decimals' => 2, 'locale' => 'tg_TJ', 'country' => 'TJ', 'country_name' => 'Tajikistan' ),
		'tmt' => array( 'name' => 'Turkmenistani Manat', 'symbol' => 'T', 'decimals' => 2, 'locale' => 'tk_TM', 'country' => 'TM', 'country_name' => 'Turkmenistan' ),
		'tnd' => array( 'name' => 'Tunisian Dinar', 'symbol' => 'DT', 'decimals' => 3, 'locale' => 'ar_TN', 'country' => 'TN', 'country_name' => 'Tunisia' ),
		'top' => array( 'name' => 'Tongan Paʻanga', 'symbol' => 'T$', 'decimals' => 2, 'locale' => 'to_TO', 'country' => 'TO', 'country_name' => 'Tonga' ),
		'try' => array( 'name' => 'Turkish Lira', 'symbol' => '₺', 'decimals' => 2, 'locale' => 'tr_TR', 'country' => 'TR', 'country_name' => 'Turkey' ),
		'ttd' => array( 'name' => 'Trinidad & Tobago Dollar', 'symbol' => 'TT$', 'decimals' => 2, 'locale' => 'en_TT', 'country' => 'TT', 'country_name' => 'Trinidad and Tobago' ),
		'twd' => array( 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$', 'decimals' => 2, 'locale' => 'zh_TW', 'country' => 'TW', 'country_name' => 'Taiwan' ),
		'tzs' => array( 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'decimals' => 2, 'locale' => 'en_TZ', 'country' => 'TZ', 'country_name' => 'Tanzania' ),
		'uah' => array( 'name' => 'Ukrainian Hryvnia', 'symbol' => '₴', 'decimals' => 2, 'locale' => 'uk_UA', 'country' => 'UA', 'country_name' => 'Ukraine' ),
		'ugx' => array( 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'decimals' => 2, 'locale' => 'en_UG', 'country' => 'UG', 'country_name' => 'Uganda' ),
		'usd' => array( 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_US', 'country' => 'US', 'country_name' => 'United States' ),
		'uyu' => array( 'name' => 'Uruguayan Peso', 'symbol' => '$U', 'decimals' => 2, 'locale' => 'es_UY', 'country' => 'UY', 'country_name' => 'Uruguay' ),
		'uzs' => array( 'name' => 'Uzbekistani Som', 'symbol' => 'лв', 'decimals' => 2, 'locale' => 'uz_UZ', 'country' => 'UZ', 'country_name' => 'Uzbekistan' ),
		'vnd' => array( 'name' => 'Vietnamese Đồng', 'symbol' => '₫', 'decimals' => 0, 'locale' => 'vi_VN', 'country' => 'VN', 'country_name' => 'Vietnam' ),
		'vuv' => array( 'name' => 'Vanuatu Vatu', 'symbol' => 'VT', 'decimals' => 0, 'locale' => 'en_VU', 'country' => 'VU', 'country_name' => 'Vanuatu' ),
		'wst' => array( 'name' => 'Samoan Tālā', 'symbol' => 'WS$', 'decimals' => 2, 'locale' => 'en_WS', 'country' => 'WS', 'country_name' => 'Samoa' ),
		'xaf' => array( 'name' => 'Central African CFA Franc', 'symbol' => 'FCFA', 'decimals' => 0, 'locale' => 'fr_CM', 'country' => 'CM', 'country_name' => 'Central Africa' ),
		'xcd' => array( 'name' => 'East Caribbean Dollar', 'symbol' => '$', 'decimals' => 2, 'locale' => 'en_AG', 'country' => 'AG', 'country_name' => 'East Caribbean' ),
		'xof' => array( 'name' => 'West African CFA Franc', 'symbol' => 'CFA', 'decimals' => 0, 'locale' => 'fr_SN', 'country' => 'SN', 'country_name' => 'West Africa' ),
		'xpf' => array( 'name' => 'CFP Franc', 'symbol' => '₣', 'decimals' => 0, 'locale' => 'fr_PF', 'country' => 'PF', 'country_name' => 'French Polynesia' ),
		'zar' => array( 'name' => 'South African Rand', 'symbol' => 'R', 'decimals' => 2, 'locale' => 'en_ZA', 'country' => 'ZA', 'country_name' => 'South Africa' ),
		'zmw' => array( 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'decimals' => 2, 'locale' => 'en_ZM', 'country' => 'ZM', 'country_name' => 'Zambia' ),
	);

	/**
	 * Stripe's minimum charge, in the currency's minor unit.
	 *
	 * Stripe refuses a charge below this so its fee cannot exceed the
	 * payment. The amounts are not proportional — 0.30 GBP but 175.00
	 * HUF, 15.00 CZK but 0.50 EUR — so they cannot be derived and have to
	 * be carried.
	 *
	 * A currency absent from this table has no published minimum; the
	 * settlement currency's minimum applies after conversion.
	 */
	private const MINIMUM_CHARGE = array(
		'usd' => 50,    'aed' => 200,   'ars' => 50,    'aud' => 50,
		'brl' => 50,    'cad' => 50,    'chf' => 50,    'cop' => 50,
		'czk' => 1500,  'dkk' => 250,   'eur' => 50,    'gbp' => 30,
		'hkd' => 400,   'huf' => 17500, 'idr' => 50,    'ils' => 50,
		'inr' => 50,    'jpy' => 50,    'krw' => 50,    'mxn' => 1000,
		'myr' => 200,   'nok' => 300,   'nzd' => 50,    'php' => 50,
		'pln' => 200,   'ron' => 200,   'rub' => 50,    'sek' => 300,
		'sgd' => 50,    'thb' => 1000,  'zar' => 50,
	);

	/**
	 * Maximum charge, in the currency's minor unit.
	 *
	 * Bounded by the digit count Stripe accepts, which differs for three
	 * currencies. Card networks may impose lower limits of their own.
	 */
	private const MAXIMUM_CHARGE = array(
		'idr' => 999999999999,
		'cop' => 9999999999,
		'inr' => 999999999,
	);

	/**
	 * Maximum charge for every other currency, in minor units.
	 */
	private const MAXIMUM_CHARGE_DEFAULT = 99999999;

	/**
	 * The smallest charge Stripe will accept.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return int Minor units. 0 when no minimum is published, in which
	 *             case the settlement currency's minimum applies after
	 *             conversion.
	 */
	public static function minimum_charge( string $code ): int {
		return self::MINIMUM_CHARGE[ strtolower( trim( $code ) ) ] ?? 0;
	}

	/**
	 * The largest charge Stripe will accept.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return int Minor units.
	 */
	public static function maximum_charge( string $code ): int {
		return self::MAXIMUM_CHARGE[ strtolower( trim( $code ) ) ] ?? self::MAXIMUM_CHARGE_DEFAULT;
	}

	/**
	 * Every currency, keyed by lowercase code.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all(): array {
		return self::CURRENCIES;
	}

	/**
	 * Every currency code, uppercase.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function codes(): array {
		return array_map( 'strtoupper', array_keys( self::CURRENCIES ) );
	}

	/**
	 * One currency's metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code, any case.
	 *
	 * @return array<string, mixed>|null Null when unsupported.
	 */
	public static function get( string $code ): ?array {
		return self::CURRENCIES[ strtolower( trim( $code ) ) ] ?? null;
	}

	/**
	 * Reduce a caller-supplied code to something safe to display.
	 *
	 * Currency codes routinely arrive from a query string or a JSON body
	 * — `?currency=` on a headless checkout is the obvious one — and the
	 * display accessors below fall back to echoing the code when they do
	 * not recognise it. Without this, `Money::format( 100, $_GET['c'] )`
	 * would render whatever the caller sent straight into the page.
	 *
	 * A well-formed code is returned uppercase whether or not it is one
	 * of the 136 supported ones, because an unknown-but-plausible code is
	 * a data problem, not an attack. Anything else comes back empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code Raw code.
	 *
	 * @return string Three uppercase letters, or ''.
	 */
	public static function sanitize_code( string $code ): string {
		$code = strtoupper( trim( $code ) );

		return 1 === preg_match( '/^[A-Z]{3}$/', $code ) ? $code : '';
	}

	/**
	 * Whether a currency is supported.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return bool
	 */
	public static function supports( string $code ): bool {
		return isset( self::CURRENCIES[ strtolower( trim( $code ) ) ] );
	}

	/**
	 * The decimal exponent for a currency.
	 *
	 * The number of digits after the point, and therefore the power of ten
	 * separating the major unit from the minor one. 2 for USD, 0 for JPY,
	 * 3 for BHD.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return int Defaults to 2 for an unknown currency, which is the
	 *             least-surprising guess.
	 */
	public static function decimals( string $code ): int {
		return self::get( $code )['decimals'] ?? 2;
	}

	/**
	 * The display symbol.
	 *
	 * Falls back to the uppercase code, because many currencies share a
	 * glyph — printing a bare "$" for SGD, CAD and AUD alike is worse than
	 * printing the code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return string
	 */
	public static function symbol( string $code ): string {
		return self::get( $code )['symbol'] ?? self::sanitize_code( $code );
	}

	/**
	 * The full display name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return string
	 */
	public static function name( string $code ): string {
		return self::get( $code )['name'] ?? self::sanitize_code( $code );
	}

	/**
	 * The primary issuing country.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code ISO-4217 code.
	 *
	 * @return string Alpha-2 code, or '' for supranational currencies.
	 */
	public static function country( string $code ): string {
		return self::get( $code )['country'] ?? '';
	}

	/**
	 * Code => label pairs for a select element.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> e.g. `USD` => `USD - US Dollar ($)`.
	 */
	public static function options(): array {
		$options = array();

		foreach ( self::CURRENCIES as $code => $meta ) {
			$upper             = strtoupper( $code );
			$options[ $upper ] = $upper . ' - ' . $meta['name'] . ' (' . $meta['symbol'] . ')';
		}

		asort( $options );

		return $options;
	}
}
