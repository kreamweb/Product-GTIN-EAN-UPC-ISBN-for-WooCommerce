=== Product GTIN (EAN, UPC, ISBN) for WooCommerce ===

Contributors: kreamweb
Donate link: https://paypal.me/pools/c/85ErI7sdcf
Tags: GTIN, EAN, ISBN, UPC, JAN, ITF-14
Requires at least: 4.0.0
Requires PHP: 7.0
Tested up to: 5.6
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The **Product GTIN (EAN, UPC, ISBN) for WooCommerce** plugin adds the Global Trade Item Number (EAN, UPC, ISBN, JAN, ITF-14) field to WooCommerce products.

== Description ==

**Product GTIN (EAN, UPC, ISBN) for WooCommerce** lets you set the GTIN (Global Trade Item Number) for each product of your shop.
By default, WooCommerce allows to set the SKU custom field for a product, but sometimes the SKU isn't enough.
Google and Amazon use global trade item numbers (GTINs) to group identical products.
The most common GTINs are:

* UPC: this is the primary GTIN in North America.
* EAN/UCC: the major GTIN used outside of North America
* JAN: the Japanese GTIN
* ISBN: a GTIN for books

Another type of Identification Number is the MPN (Manufacturer Part Number). This code is required for all products that do not have an assigned GTIN.
These type of codes are important both for selling and for tracking your products.

= Main features of Product GTIN (EAN, UPC, ISBN) for WooCommerce: =

* Assign a name to the code (EAN, UPC, ISBN, JAN, ITF-14)
* Add a code for main products and for product variations.
* Extend the WooCommerce Product Search by GTIN Code.
* Add the GTIN code column inside the product list table.
* Search by GTIN inside the product list table.
* Option to show GTIN code in WooCommerce product detail page.
* Option to show GTIN code in WooCommerce shop page.
* Option to show GTIN code in WooCommerce cart page.
* Option to show GTIN code in WooCommerce checkout page.
* Option to show GTIN code in WooCommerce Order Items.
* Hide empty GTIN code in your WooCommerce shop.
* Shortcode available to show GTIN code for a specific product.
* Support for Schema.org/Product with an option to choose the property name to set inside the structured data
* Option to choose the position of GTIN code inside the single product page
* Integrated with the WooCommerce Export Products CVS tool
* Integrated with the WooCommerce Import products from a CSV file tool
* WooCommerce Rest API Product integration: GTIN code is passed inside the 'ean_code' field


== Installation ==
Important: First of all, you have to download and activate the WooCommerce plugin, without which Product GTIN (EAN, UPC, ISBN) for WooCommerce cannot work correctly.

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site, or install the plugin through the WordPress plugins screen.

3. Activate `Product GTIN (EAN, UPC, ISBN) for WooCommerce` from the Plugins page

= Configuration =
Product GTIN (EAN, UPC, ISBN) for WooCommerce will add a new tab called "Product GTIN" in "WooCommerce > Settings" page.

== Frequently Asked Questions ==

= How can I set a GTIN code for a product? =
Inside the product editor page, open the “Inventory” tab, and fill out the GTIN field.
If your product is variable, you can set a GTIN code for each variation; you can fill out the GTIN field that you'll find inside the variation detail panel.

= How can I get a GTIN code for a product? =

Since version 1.1.0 it is possible use the function wpm_get_code_gtin_by_product that need the product id or the product object.
The function returns the GTIN code as string.

`$gtin = wpm_get_code_gtin_by_product($product_id);`

= Where is stored the GTIN code ? =
The GTIN code is a private custom field of product with *meta_key* `_wpm_gtin_code`
If you have the product object you can get the GTIN code in this way:

`$gtin = $product->get_meta('_wpm_gtin_code');`

= How can use the shortcode to show the GTIN code where I want? =
The shortcode to show the GTIN code is `[wpm_product_gtin]` you can use these attributes:

* `id` the product id
* `label` the label that will be displayed before the code. By default is "Code EAN:", but this can be changed inside the settings panel.
* `wrapper` you can wrap the label and GTIN code in div or span. By default is 'div' for the shop page and 'span' on the other pages.
* `wrapper_code` the container of GTIN code. By default is a 'span'.
* `class` the class of wrapper container. By default is 'wpm_gtin'.
* `class_wrapper` the class of wrapper code container. By default is 'wpm_gtin_code_wrapper'.

= How can use a different GTIN label for specific products? =
Inside the product editor page, open the “Inventory” tab, and fill out the field
"Override GTIN Code Label"

= How set GTIN code with WooCommerce REST API? =
When you create or update a product via REST API it is possible set or update the custom meta:

  `"meta_data": [
    {
      "key": "_wpm_gtin_code",
      "value":"123798123987132"
    }
  ]`

== Screenshots ==
1. Settings Options Page of Product GTIN (EAN, UPC, ISBN) for WooCommerce.
2. Product editor > Inventory > GTIN Code field.
3. Product editor > Variations > GTIN Code field.
4. GTIN Code in WooCommerce product detail page.
5. GTIN Code in WooCommerce shop page.
6. GTIN Code in WooCommerce cart page.
7. GTIN Code in WooCommerce checkout page.
8. GTIN code in WooCommerce Order Items.
9. WooCommerce Product List Page with GTIN Code Column.
10. Shortcode
11. WooCommerce Export Products CVS file integration: settings
12. WooCommerce Export Products CVS file integration: cvs sample
13. WooCommerce Import products from a CSV file: settings
14. WooCommerce Rest API product detail
15. WooCommerce product structured data

== Changelog ==
= 1.1.1 - Released: 16 Nov, 2020 =
*  New: Support for WordPress 5.6
*  Dev: Added attribute product-id inside the wrapper of GTIN code to avoide some issue with Elementor Widget

= 1.1.0 - Released: 25 Oct, 2020 =
*  New: Support for WordPress 5.5
*  New: Support for WooCommerce 4.7
*  Dev: New function wpm_get_code_gtin_by_product to get the GTIN code by product id or product

= 1.0.9 - Released: 25 April, 2020 =
*  New: Support for WooCommerce 4.1

= 1.0.8 - Released: Jan 6, 2020 =
* Fix: Fixed the PHP warning

= 1.0.7 - Released: Jan 5, 2020 =
*  New: Added the product GTIN field inside the "WooCommerce Import products from a CSV file" section. The name of the column is getting from your plugin settings.

= 1.0.6 - Released: Jan 5, 2020 =
*  New: Support for WordPress 5.3
*  New: Support for WooCommerce 3.9
*  New: Support for Schema.org/Product, added a new option to choose the property name to set inside the structured data (thanks to @stroykamarketcom)
*  New: New option in product editor Inventory to override the general code label
*  New: New option to choose the product GTIN position inside the single product page
*  New: Added the product GTIN field inside the "WooCommerce Export Products CVS file" section. The name of the column is getting from your plugin settings.

= 1.0.5 - Released: Jun 19, 2019 =
*  New: Support for WordPress 5.2.x
*  New: Support for WooCommerce 3.6.x
*  New: Integration with Google Product Feed, registers GTIN field from Product GTIN for WooCommerce plugin as a pre-population option (thanks to Lee Willis)
*  Fix: Public label fix (thanks to davado)

= 1.0.4 - Released: Oct 26, 2018 =
*  New: Support for WooCommerce 3.5.x
*  New: Added 'ean_code' in WooCommerce Rest product data
*  Fix: Search by GTIN Code with Wordpress Search Widget

= 1.0.3 - Released: Sep 19, 2018 =
* New: Option to show GTIN code in WooCommerce Order Items.
* Fix: Search by GTIN inside the product list table issue

= 1.0.2 - Released: Aug 17, 2018 =
* Update: Added Settings Link on Action Links

= 1.0.1 - Released: Aug 10, 2018 =
* Update: Text domain

= 1.0.0 - Released: Aug 9, 2018 =

* Initial release

== Suggestions ==
If you have any suggestions concerning how to improve Product GTIN (EAN, UPC, ISBN) for WooCommerce, you can contact @emanuelacastorina on [Slack](https://make.wordpress.org/chat/).
