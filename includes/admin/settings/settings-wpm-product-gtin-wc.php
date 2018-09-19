<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPM_Product_GTIN_WC_Settings', false ) ) :

	/**
	 * WPM_Product_GTIN_WC_Settings.
	 */
	class WPM_Product_GTIN_WC_Settings extends WC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'wpm-product-gtin-wc';
			$this->label = __( 'Product GTIN', 'product-gtin-ean-upc-isbn-for-woocommerce' );

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters( 'wpm_product_gtin_wc_settings', array(

				array(
					'title'    => __( 'Product GTIN Settings', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'type'     => 'title',
					'desc'     => __( 'In this area you can set the plugin options', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm-pgw-section-title',
				),

				array(
					'title'    => __( 'Label of GTIN Code', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'The label of code (i.e. EAN, UPC, ISBN, JAN, ITF-14) )', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_label',
					'default'  => __( 'EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'type'     => 'text',
					'desc_tip' => true,
				),


				array(
					'title'    => __( 'Public label of GTIN Code', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'The label that the customers will see on site before the code. (i.e. "EAN Code:", "UPC:", "ISBN-") )', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_public_label',
					'default'  => __( 'EAN:', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'type'     => 'text',
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Show Code GTIN:', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'Display the code in single product page.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_single_product',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'checkboxgroup' => 'start',
				),

				array(
					'desc'     => __( 'Display the code in loop.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_loop',
					'default'  => 'no',
					'type'     => 'checkbox',
					'checkboxgroup' => '',
				),

				array(
					'desc'     => __( 'Display the code in cart.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_cart',
					'default'  => 'no',
					'type'     => 'checkbox',
					'checkboxgroup' => '',
				),

				array(
					'desc'     => __( 'Display the code in checkout page.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_checkout_page',
					'default'  => 'no',
					'type'     => 'checkbox',
					'checkboxgroup' => '',
				),

				array(
					'desc'     => __( 'Display the code as order item meta.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_order_item_meta',
					'default'  => 'no',
					'type'     => 'checkbox',
					'checkboxgroup' => '',
				),


				array(
					'title'    => __( 'Hide Code GTIN if empty', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'Set this option if you want hide the code when it is empty.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_hide_code_empty',
					'default'  => 'no',
					'type'     => 'checkbox',
				),


				array(
					'title'    => __( 'Search by Code GTIN', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'Set this option if you want search products also by GTIN Code', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_search_by_code',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'checkboxgroup' => 'start',
				),

				array(
					'title'    => __( 'Search by Code GTIN in Admin product search', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'desc'     => __( 'Set this option if you want search products also by GTIN Code in administrator product list.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
					'id'       => 'wpm_pgw_admin_search_by_code',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'checkboxgroup' => '',
				),



				array( 'type' => 'sectionend', 'id' => 'wpm-product-gtin-wc-section-title' ),

			) );

			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
		}



		/**
		 * Output the settings.
		 */
		public function output() {
			$settings = $this->get_settings();

			WC_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();

			WC_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new WPM_Product_GTIN_WC_Settings();
