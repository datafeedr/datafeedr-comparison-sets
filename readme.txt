=== Datafeedr Comparison Sets ===

Contributors: datafeedr.com
Donate link: https://www.datafeedr.com/
Tags: comparison, comparison sets, price comparison, price compare, price comparison set
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4
Requires at least: 3.8
Tested up to: 6.6-RC2
Stable tag: 0.9.71

Automatically create price comparison sets for your WooCommerce products or by using a shortcode.

== Description ==

The Datafeedr Comparison Set plugin automatically creates price comparison sets for any product in your WooCommerce store or by using a shortcode.

> **Heads-up!** — This plugin requires that you have an active subscription to one of our API plans. [Purchase Subscription](https://datafeedr.me/pricing).

*For personal-use only. Please contact us if you have any questions.*

**Requirements**

* PHP 7.4 or greater
* MySQL version 5.6 or greater
* [WordPress memory limit of 256 MB or greater](https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP)
* [Datafeedr API Plugin](https://wordpress.org/plugins/datafeedr-api/)
* [HTTPS support](https://wordpress.org/news/2016/12/moving-toward-ssl/)

= API Usage =

Please know that the generating or updating of a single comparison set will require between 2 and 6 API requests. If you are using the API **Starter Plan**, consider [upgrading](https://datafeedr.me/dashboard) to the **Basic Plan**.

= Installation & Configuration =

> **[IMPORTANT] Before You Begin**
>
> This plugin requires that you have installed and activated the [Datafeedr API plugin](https://wordpress.org/plugins/datafeedr-api/). The following instructions will assume that you have installed the [Datafeedr API plugin](https://wordpress.org/plugins/datafeedr-api/) and selected your affiliate networks and merchants. Installation and configuration instructions can be found [here](https://wordpress.org/plugins/datafeedr-api/installation/).

= API Usage =

Please know that the generating or updating of a single comparison set will require between 2 and 6 API requests. If you are using the API **Starter Plan**, consider [upgrading](https://datafeedr.me/dashboard) to the **Basic Plan**.

= Plugin Installation =

1. Upload the `datafeedr-comparison-sets` folder to the `/wp-content/plugins/` directory.
1. Activate the *Datafeedr Comparison Sets* plugin through the 'Plugins' menu in WordPress.
1. Configure here: WordPress Admin Area > Datafeedr API > Comparison Sets.

= Enabling Amazon Products =

If you want Amazon products to appear in your comparison sets, do the following:

1. Go here WordPress Admin Area > Datafeedr API > Configuration > Amazon Settings.
1. Add your Amazon Access Key ID, Secret Access Key, Tracking ID and Locale.
1. Click the [Save Changes] button at the bottom of the page.
1. Go here WordPress Admin Area > Datafeedr API > Comparison Sets.
1. Change the **Cache Lifetime** field to `86400`.
1. Click the [Save Changes] button at the bottom of the page.

= Display Comparison Sets for WooCommerce Products =

To display comparison sets for products in your WooCommerce store, follow these instructions.

1. Go here WordPress Admin Area > Datafeedr API > Comparison Sets > Integrations.
1. Check the **WooCommerce** option.
1. Click the [Save Changes] button at the bottom of the page.

Now, when a product in your store is viewed for the first time, a price comparison set will be generated.

*Note: Enabling WooCommerce integration will not generate price comparison sets for all products in your store immediately. Sets will only be generated when the product is viewed for the first time.*

= Display Comparison Sets using Shortcodes =

To display comparison sets in your posts, pages or in other areas where WordPress shortcodes are allowed, use the formats below.


Create a price comparison set using name field:
`
[dfrcs name="baratza encore"]
`

Create a price comparison set using name and brand field:
`
[dfrcs name="j8006" brand="omega" ]
`

Create a price comparison set with a custom title:
`
[dfrcs name="chaos harness" brand="black diamond" title="{num_products} great deals on Black Diamond Chaos Harnesses"]
`

**Available Filters**

You can further filter the results of a Comparison Set by using the `filters` attribute. Available filters are:

`
currency
amazon_locale
image
onsale
direct_url
saleprice_min
saleprice_max
finalprice_min
finalprice_max
merchant_id
source_id
`

Here is what each filter controls:

`
// Return only products which are priced in USD.
currency=USD

// Return Amazon products from US Locale.
amazon_locale=US

// Return only products that have an image.
image=1

// Return only products that are on sale.
onsale=1

// Return only products that have a direct URL.
direct_url=1

// Return only products with a minimum sale price of $10.
saleprice_min=10

// Return only products with a maximum sale price of $100.
saleprice_max=100

// Return only products with a minimum final price of $20.
finalprice_min=20

// Return only products with a maximum final price of $200.
finalprice_max=200

// Return only products from merchants with specific Merchant IDs (merchant_id).
merchant_id=61316,33092,97391

// Return only products from networks with specific Network IDs (source_id).
source_id=126,3
`

Merchant IDs `merchant_id` (MID) and Network IDs `source_id` (NID) can be referenced on our [Affiliate Networks & Merchants page](https://datafeedr.me/networks).

Here are some examples of how to use these filters in your shortcodes:

Create a price comparison set using `onsale` filter:
`
[dfrcs name="farpoint 55" brand="osprey" filters="onsale=1"]
`

Create a price comparison set using currency filter:
`
[dfrcs name="aeropress coffee maker" brand="aerobie" filters="currency=USD"]
`

Create a price comparison set using multiple filters:
`
[dfrcs name="nomad 20 solar panel" filters="currency=USD&finalprice_max=400"]
`

Create a price comparison set using an EAN code:
`
[dfrcs ean="737416080066"]
`

Create a price comparison set using an Amazon ASIN code:
`
[dfrcs asin="B07BN6KH6W"]
`

Create a price comparison set using an UPC code:
`
[dfrcs upc="050946872827"]
`

Create a price comparison set limited to specific merchants and networks:

`
[dfrcs brand="patagonia" name="hoodie" filters="merchant_id=61316,33092,97391&source_id=126"]
`

Shortcode to use on WooCommerce **single product pages** (ie. in blocks, page builders, widgets, etc...)
`
[dfrcs_wc]
`

== Frequently Asked Questions ==

= Where can I get help?  =

Feel free to contact us [here](https://datafeedrapi.helpscoutdocs.com/contact?utm_campaign=dfrapiplugin&utm_medium=referral&utm_source=wporg).

== Screenshots ==

1. This is a price comparison set automatically generated for a product in a WooCommerce store.
2. This is a price comparison set automatically generated using a shortcode.
3. This is the configuration page of the Datafeedr Comparison Sets plugin.

== Changelog ==

= 0.9.71 - 2024/07/05 =
* Updated "tested up to" value

= 0.9.70 - 2023/11/10 =
* Updated "tested up to" value
* Declaring WooCommerce HPOS compatibility.

= 0.9.69 - 2023/10/31 =
* Fixed type declaration causing errors on sites running PHP < 8.0.

= 0.9.68 - 2023/10/30 =
* Added `signed` encoded source values.

= 0.9.67 - 2023/10/19 =
* Verifying hashes
* Better handling of source data string

= 0.9.66 - 2023/04/20 =
* Filter out products that are no longer available in the API.

= 0.9.63 - 2022/10/28 =
* Updated "tested up to" values

= 0.9.62 - 2022/07/12 =
* Changed the `dfrcs_wc` shortcode to `return` instead of `echo` to resolve issues with builders like Elementor.

= 0.9.61 - 2022/07/11 =
* Updated readme and tested up to values.

= 0.9.60 - 2022/04/06 =
* Changed "Date Format" label to "Date Timezone"
* Changed some site health info options to use absint() === 1 instead of less strict
* Added the following site health info items:
* Display Image
* Display Logo
* Display Price
* Display Button
* Display Promo
* Use Amazon Data
* Amazon Disclaimer Title
* Amazon Disclaimer Message
* Amazon Disclaimer More Info Link
* Amazon Date Format
* Amazon Date Timezone

= 0.9.59 - 2022/03/24 =
* Added support for `merchant_id` and `source_id` filters to be used in shortcodes.

= 0.9.58 - 2022/03/14 =
* Added option to disable using Amazon data in Comparison Set product search. Useful if Comparison Sets are returning inaccurate results.
* Added return values for some functions.
* Updated `require_once` statements.
* Added `DFRCS_PLUGIN_FILE` constant.
* Updated `WC tested up to` to 6.3.

= 0.9.57 - 2022/03/02 =
* Added a minimum WordPress version check to the `register_activation_hook`
* Added a Multisite check to the `register_activation_hook` to ensure that plugin can only be activated at Site-Level, not Network-Level
* Added "Requires PHP: 7.4" to plugin headers

= 0.9.56 - 2022/02/10 =
* Replaced `DFRAPI_DOMAIN` with `'datafeedr-comparison-sets'`.

= 0.9.55 - 2022/02/07 =
* Fixed issue where `&` in `filters` param in shortcode was being converted to `&amp;` causing the filters param to break.

= 0.9.54 - 2021/12/14 =
* Added support for new Amazon disclaimer message.

= 0.9.53 - 2021/11/29 =
* Added links to Documentation, Support and Configuration page for plugin on Plugins page.

= 0.9.52 - 2020/09/03 =
* Added new setting to display or hide the product image for each item in a Comparison Set. (WordPress Admin Area > Datafeedr API > Comparison Set)
* Added new setting to display or hide the merchant logo for each item in a Comparison Set. (WordPress Admin Area > Datafeedr API > Comparison Set)
* Added new setting to display or hide the product price for each item in a Comparison Set. (WordPress Admin Area > Datafeedr API > Comparison Set)
* Added new setting to display or hide the [View] button for each item in a Comparison Set. (WordPress Admin Area > Datafeedr API > Comparison Set)
* Added new setting to display or hide the product promo text for each item in a Comparison Set. (WordPress Admin Area > Datafeedr API > Comparison Set)

= 0.9.51 - 2020/09/01 =
* Made the list of Amazon fields to query filterable.

= 0.9.50 - 2020/04/01 =
* Fixed undefined notice.

= 0.9.49 - 2020/03/31 =
* Fixed {lowest_price} and {highest_price} Comparison Set title placeholders formatting.

= 0.9.48 - 2020/02/18 =
* Reverted changes from 2020/02/17 as it caused permission issues.

= 0.9.47 - 2020/02/17 =
* Fixed issue where dfrcs_manage_compsets_capability filter wasn't allowing new capabilities to access "Add Products" page.

= 0.9.46 - 2020/02/16 =
* Fixed jQuery migrate issues.
* Updated add_submenu_page() capability with dfrcs_manage_compsets_capability filter.

= 0.9.45 - 2020/02/09 =
* Added manage compsets capability filter.

= 0.9.44 - 2020/01/29 =
* Added Site Health Info

= 0.9.43 - 2020/01/14 =
* Added ability to automatically prune old records from the `dfrcs_compsets` database table.

= 0.9.42 - 2020/01/12 =
* Added support for Amazon "Used" prices.
* Added new `dfrapi_get_price()` function to render pricing.

= 0.9.41 - 2020/12/01 =
* Fixed "PHP Notice:  Trying to access array offset on value of type bool"

= 0.9.40 - 2020/09/30 =
* Added the ability to override "Last updated" text. `add_filter( 'dfrcs_last_updated_text', function($text, $compset){return 'UPDATED:';}, 10, 2 );`
* Added `asin` as a default barcode field.

= 0.9.39 - 2020/08/20 =
* Added new filter to remove Amazon products from compsets returned via shortcodes.

= 0.9.38 - 2020/07/27 =
* Updated for WooCommerce compatibility.

= 0.9.37 - 2020/03/30 =
* Added new `[dfrcs_wc]` shortcode to use on WooCommerce single product pages.

= 0.9.36 - 2020/03/11 =
* Added support for WooCommerce 4.0.

= 0.9.35 - 2020/02/24 =
* Requiring all Amazon API requests to be made over an HTTPS connection.

= 0.9.34 - 2019/12/15 =
* Updated `dfrcs_sort_products()` to pass variable, not array, to `array_multisort()`.

= 0.9.33 - 2019/11/12 =
* Updated version support for WC 3.8

= 0.9.32 - 2019/10/30 =
* Fixed more issues with accented characters. (#325)

= 0.9.31 - 2019/10/23 =
* Updated to handle source names with accented characters.

= 0.9.30 - 2019/08/20 =
* Updated to support latest version of WooCommerce

= 0.9.29 - 2019/07/17 =
* Escaped values in debug output.

= 0.9.28 - 2019/05/06 =
* Updated readme.

= 0.9.27 - 2019/04/23 =
* Updated readme.

= 0.9.26 - 2019/03/04 =
* Fixed handling of barcode values of 000000000000 or similar.

= 0.9.25 - 2019/02/19 =
* Updated readme.txt.

= 0.9.24 - 2018/12/07 =
* Updated readme.txt.

= 0.9.23 - 2018/11/01 =
* Updated readme.txt.

= 0.9.22 - 2018/09/03 =
* Fixed uncaught exception.

= 0.9.21 - 2018/05/07 =
* Fixed bug when Amazon keys are missing.

= 0.9.20 - 2018/04/25 =
* Updated readme.txt.

= 0.9.19 - 2018/03/14 =
* Declared required and supported WooCommerce version.

= 0.9.18 - 2018/02/05 =
* Fixed PHP compatibility issue in new function.

= 0.9.17 - 2018/02/01 =
* Added a couple of helper functions.

= 0.9.16 - 2018/01/17 =
* Updated readme.txt and added new README.md.

= 0.9.15 - 2018/01/10 =
* Fixed bug related to new class.

= 0.9.14 - 2018/01/10 =
* Added new `Datafeedr_Plugin_Dependency` class.

= 0.9.13 - 2017/10/18 =
* Amazon links are now generated via the `dfrapi_url()` function instead of returning the raw link from Amazon. (#15201)

= 0.9.12 - 2017/10/04 =
* Removed references to `$product['suid']` in the `query_amazon()` method. (#15084)

= 0.9.11 - 2017/09/19 =
* Fixed bug related to manually products that no longer exist still being displayed in Comparison Sets.

= 0.9.10 - 2017/04/26 =
* Updated readme and plugin info.

= 0.9.9 - 2017/04/18 =
* Changed permissions on `dfrcs_can_manage_compset()` function.

= 0.9.8 - 2017/02/21 =
* Modified so Comparison Sets are NO LONGER created or updated when a bot is viewing the page. Comparison Sets will be displayed to a bot if it's already cached however it will not be created or updated if the current USER AGENT matches any bot in the `dfrcs_visitor_is_bot()` function.

= 0.9.7 - 2017/02/16 =
* Changed references to `$product` properties to calls to public methods such as `get_id()` and `get_title()`. This is in preparation for WooCommerce 2.7.

= 0.9.6 - 2016/09/27 =
* Optimized merchant logo display. (#13884, #13881)

= 0.9.5 - 2016/08/05 =
* Replaced `intval()` with `sanitize_text_field()` on the source ID because 32 bit systems were converting long IDs to 2147483647. More info http://stackoverflow.com/a/25910134.

= 0.9.4 - 2016/08/01 =
* Added support for `asin` and `isbn` to be passed along as shortcode attributes for Amazon searches.

= 0.9.3 - 2016/04/04 =
* Initial PUBLIC release.
* Clarified help texts on configuration page.
* Updated readme.txt for WordPress repository.

= 0.9.2 - 2016/03/21 =
* Changed format of URL to merchant logos to new format `https://images.datafeedr.com/m/nnn.jpg`. (#12900)

= 0.9.1 - 2016/03/09 =
* Fixed currency sign position for products with sign of "kr".

= 0.9.0 - 2016/03/07 =
* Initial BETA release.

== Upgrade Notice ==

*None*

