=== Datafeedr Comparison Sets ===

Contributors: datafeedr.com
Donate link: https://www.datafeedr.com/
Tags: comparison, comparison sets, compsets, price, price comparison, price compare, price comparison set, datafeedr, affiliate products, dfrapi
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.8
Tested up to: 4.9.8
Stable tag: 0.9.22

Automatically create price comparison sets for your WooCommerce products or by using a shortcode.

== Description ==

The Datafeedr Comparison Set plugin automatically creates price comparison sets for any product in your WooCommerce store or by using a shortcode.

> **Heads-up!** — This plugin requires that you have an active subscription to one of our API plans. Purchase a subscription [here](https://members.datafeedr.com/subscribe/api?utm_campaign=dfrapiplugin&utm_medium=referral&utm_source=wporg).

= API Usage =

Please know that the generating or updating of a single comparison set will require between 2 and 6 API requests. If you are using the API **Starter Plan**, consider [upgrading](https://members.datafeedr.com/api?utm_campaign=dfrapiplugin&utm_medium=referral&utm_source=wporg) to the **Basic Plan**.

= Installation & Configuration =

> **[IMPORTANT] Before You Begin**
>
> This plugin requires that you have installed and activated the [Datafeedr API plugin](https://wordpress.org/plugins/datafeedr-api/). The following instructions will assume that you have installed the [Datafeedr API plugin](https://wordpress.org/plugins/datafeedr-api/) and selected your affiliate networks and merchants. Installation and configuration instructions can be found [here](https://wordpress.org/plugins/datafeedr-api/installation/).

= API Usage =

Please know that the generating or updating of a single comparison set will require between 2 and 6 API requests. If you are using the API **Starter Plan**, consider [upgrading](https://members.datafeedr.com/api?utm_campaign=dfrapiplugin&utm_medium=referral&utm_source=wporg) to the **Basic Plan**.

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
`

Here is what each filter controls:

`
// Return only products which are priced in USD.
​currency=USD

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
`

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

Create a price comparison set using an UPC code:
`
[dfrcs upc="050946872827"]
`

== Frequently Asked Questions ==

= Where can I get help?  =

Feel free to contact us [here](https://datafeedrapi.helpscoutdocs.com/contact?utm_campaign=dfrapiplugin&utm_medium=referral&utm_source=wporg).

== Screenshots ==

1. This is a price comparison set automatically generated for a product in a WooCommerce store.
2. This is a price comparison set automatically generated using a shortcode.
3. This is the configuration page of the Datafeedr Comparison Sets plugin.

== Changelog ==

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

