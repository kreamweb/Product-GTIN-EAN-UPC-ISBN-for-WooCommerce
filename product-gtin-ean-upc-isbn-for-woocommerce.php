<?php

/**
 * Plugin Name: Product GTIN (EAN, UPC, ISBN) for WooCommerce
 * Author URI: https://emanuelacastorina.com/
 * Description: Add a Global Trade Item Number (EAN, UPC, ISBN, JAN, ITF-14) field to WooCommerce
 * Version: 1.1.1
 * Author: Emanuela Castorina
 * Text Domain: product-gtin-ean-upc-isbn-for-woocommerce
 * Domain Path: /languages
 * WC tested up to: 4.7
 * WC requires at least: 3.0
 */


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Currently plugin version.
 */
if (!defined('WPM_PRODUCT_GTIN_WC_VERSION')) {
	define('WPM_PRODUCT_GTIN_WC_VERSION', '1.1.1');
}

// Define WPM_PRODUCT_GTIN_WC_FILE.
if (!defined('WPM_PRODUCT_GTIN_WC_FILE')) {
	define('WPM_PRODUCT_GTIN_WC_FILE', __FILE__);
}

// Define WPM_PRODUCT_GTIN_WC_DIR.
if (!defined('WPM_PRODUCT_GTIN_WC_DIR')) {
	define('WPM_PRODUCT_GTIN_WC_DIR', plugin_dir_path(__FILE__));
}

// Define WPM_PRODUCT_GTIN_WC_URL.
if (!defined('WPM_PRODUCT_GTIN_WC_URL')) {
	define('WPM_PRODUCT_GTIN_WC_URL', plugins_url('/', __FILE__));
}

// Define WPM_PRODUCT_GTIN_WC_ASSETS_URL.
if (!defined('WPM_PRODUCT_GTIN_WC_ASSETS_URL')) {
	define('WPM_PRODUCT_GTIN_WC_ASSETS_URL', WPM_PRODUCT_GTIN_WC_URL . 'assets');
}

// Include the main class.
if (!class_exists('WPM_Product_GTIN_WC')) {
	include_once dirname(__FILE__) . '/includes/class.product-gtin-wc.php';
}
/**
 * Return instance of WPM_Product_GTIN_WC.
 *
 * @return WPM_Product_GTIN_WC
 */
if (!function_exists('wpm_product_gtin_wc')) {

	/**
	 * Return the instance of WPM_Free_Gift_Coupons.
	 *
	 * @return WPM_Free_Gift_Coupons
	 */
	function wpm_product_gtin_wc()
	{
		return WPM_Product_GTIN_WC::instance();
	}

	$GLOBALS['wpm_product_gtin_wc'] = wpm_product_gtin_wc();
}


/**
 * Return instance of WPM_Product_GTIN_WC.
 *
 * @return WPM_Product_GTIN_WC
 */
if (!function_exists('wpm_get_code_gtin_by_product')) {

	/**
	 * Return the code of the product.
	 *
	 * @return string
	 */
	function wpm_get_code_gtin_by_product($product)
	{
		if (is_numeric($product)) {
			$product = wc_get_product($product);
		}

		if ($product instanceof WC_Product) {
			$code = $product->get_meta('_wpm_gtin_code');
			$parent_id = $product->get_parent_id();
			if (empty($code) && $parent_id) {
				return wpm_get_code_gtin_by_product($parent_id);
			} else {
				return $code;
			}
		}

		return '';
	}
}
