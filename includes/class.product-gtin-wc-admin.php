<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The core admin plugin class.
 *
 * @since      1.0.0
 * @package    Product_GTIN_for_WooCommerce
 * @subpackage Product_GTIN_for_WooCommerce/includes
 * @author     Emanuela Castorina <emanuela@kream.it>
 */
class WPM_Product_GTIN_WC_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Product_GTIN_WC_Admin
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * The label of the GTIN Code from plugin settings
	 *
	 * @var WPM_Product_GTIN_WC_Admin
	 * @since 1.0
	 */
	public $label;

	/**
	 * Main WPM_Product_GTIN_WC_Admin Instance.
	 *
	 * Ensures only one instance of WPM_Product_GTIN_WC_Admin is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return WPM_Product_GTIN_WC_Admin - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->label = get_option( 'wpm_pgw_label', __('EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ) );

		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );
		add_action( 'woocommerce_product_options_sku', array( $this, 'gtin_product_option') );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_gtin_product_option') );

		add_action( 'woocommerce_variation_options_pricing', array( $this, 'gtin_product_option_on_variation'), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_gtin_product_option_on_variation'), 10, 2);

		//add the column EAN on product list
		add_filter( 'manage_product_posts_columns', array( $this, 'manage_product_columns' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'show_gtin_code' ) );



		if( 'yes' == get_option( 'wpm_pgw_admin_search_by_code', 'yes') ){
			add_action( 'pre_get_posts', array( $this, 'extend_admin_search') );
		}

		add_filter( 'plugin_action_links_' . plugin_basename( WPM_PRODUCT_GTIN_WC_DIR . '/' . basename( WPM_PRODUCT_GTIN_WC_FILE ) ), array( $this, 'action_links' ) );


		//Export product
		add_filter( 'woocommerce_product_export_product_default_columns', array( $this,'export_product_default_columns') );
		add_filter( 'woocommerce_product_export_column_names', array( $this,'export_product_default_columns') );
		add_filter( 'woocommerce_product_export_product_column_wpm_gtin_code', array( $this,'add_gtin_to_export_data'), 10, 2 );

		//Import product
		add_filter('woocommerce_csv_product_import_mapping_options', array( $this,'import_product_mapping_options'));
		add_filter('woocommerce_csv_product_import_mapping_default_columns', array( $this,'import_mapping_default_columns'));
		add_filter('woocommerce_product_import_pre_insert_product_object', array( $this,'importer_data'), 10, 2);
	}


	/**
	 * Extends the admin search product query also to GTIN Code
	 * @param $query
	 */
	function extend_admin_search( $query ) {
		$new_query = clone( $query );
		if ( $query->is_main_query() && isset( $new_query->query['post_type'] ) && 'product' == $new_query->query['post_type'] ) {
			$search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

			if ( empty( $search_term ) ) {
				return;
			}

			$new_query->query_vars['s'] = '';
			$old_product_in = $query->query_vars['post__in'];

			unset( $new_query->query['post__in'] );
			unset( $new_query->query_vars['post__in'] );

			$meta_query = array( 'relation' => 'OR' );
			array_push( $meta_query, array(
				'key'     => '_wpm_gtin_code',
				'value'   => $search_term,
				'compare' => 'LIKE'
			) );
			$new_query->set( 'meta_query', $meta_query );
			$new_query->set( 'fields', 'ids' );

			remove_action( 'pre_get_posts', array( $this, 'extend_admin_search' ) );
			$result = get_posts( $new_query->query_vars );
			$new_ids = $old_product_in;
			if ( $result ) {
				$new_ids = array_merge( $new_ids, $result );
			}

			//search for variation
			$new_query->set( 'post_type', 'product_variation' );
			$new_query->set( 'fields', 'id=>parent' );
			$result = get_posts( $new_query->query_vars );

			if ( $result ) {
				$new_ids = array_merge( $new_ids, $result );
			}

			$query->set( 'post__in', $new_ids );
		}
	}

	/**
	 * Add setting page.
	 *
	 * @param $settings
	 * @return array
	 */
	public function add_settings_page( $settings ) {
		$settings[] =  include( 'admin/settings/settings-wpm-product-gtin-wc.php' );
		return $settings;
	}

	/**
	 * Add the GTIN code field after the sku option in product editor.
	 */
	public function gtin_product_option() {
		global $thepostid;

		echo '<div class="options_group">';
		woocommerce_wp_text_input( array(
			'id'          => '_wpm_gtin_code',
			'label'       => sprintf( __( '%s Code:', 'product-gtin-ean-upc-isbn-for-woocommerce' ), $this->label ),
			'placeholder' => '',
			'desc_tip'    => true,
			'description' => sprintf( __( 'Add the %s code for this product', 'product-gtin-ean-upc-isbn-for-woocommerce' ), $this->label ),
		) );
		woocommerce_wp_text_input( array(
			'id'          => '_wpm_gtin_code_label',
			'label'       =>  sprintf( __( 'Override %s Code Label:', 'product-gtin-ean-upc-isbn-for-woocommerce' ), $this->label ),
			'placeholder' => '',
			'desc_tip'    => true,
			'description' => __( 'Change the general label code for this product.', 'product-gtin-ean-upc-isbn-for-woocommerce' ),
		) );
		echo '</div>';
	}

	/**
	 * Add the GTIN code field after the price options in product editor for variation products.
	 */
	public function gtin_product_option_on_variation( $loop, $variation_data, $variation ) {
		global $thepostid;
		$variation_object = wc_get_product( $variation->ID );
		$value            = $variation_object->get_meta( '_wpm_gtin_code' );
		$value_label      = $variation_object->get_meta( '_wpm_gtin_code_label' );

		$label            = sprintf( __( '%s Code:', 'product-gtin-ean-upc-isbn-for-woocommerce' ), $this->label );
		$label_label      = sprintf( __( 'Override %s Code Label:', 'product-gtin-ean-upc-isbn-for-woocommerce' ), $this->label );

		woocommerce_wp_text_input(
			array(
				'id'            => "_wpm_gtin_code_variable{$loop}",
				'name'          => "_wpm_gtin_code_variable[{$loop}]",
				'value'         => $value,
				'label'         => $label,
				'wrapper_class' => 'form-row form-row-first',
			)
		);

	}

	/**
	 * Save the GTIN Code of product.
	 *
	 * @param $product WC_Product
	 */
	public function save_gtin_product_option( $product ) {
		if ( isset( $_POST['_wpm_gtin_code'] ) ) {
			$product->update_meta_data( '_wpm_gtin_code', wc_clean( wp_unslash( $_POST['_wpm_gtin_code'] ) ) );
		}
		if ( isset( $_POST['_wpm_gtin_code_label'] ) ) {
			$product->update_meta_data( '_wpm_gtin_code_label', wc_clean( wp_unslash( $_POST['_wpm_gtin_code_label'] ) ) );
		}
	}

	/**
	 * Save the GTIN Code of current variation.
	 *
	 * @param $variation_id int
	 * @param $id index of current variation
	 */
	public function save_gtin_product_option_on_variation( $variation_id, $id ) {
		$variation = wc_get_product( $variation_id );

		if ( isset( $_POST['_wpm_gtin_code_variable'][$id] ) ) {
			$variation->update_meta_data( '_wpm_gtin_code', wc_clean( wp_unslash( $_POST['_wpm_gtin_code_variable'][$id] ) ) );
			$variation->save_meta_data();
		}
	}

	/**
	 * Add the column GTIN inside the product list table.
	 *
	 * @param $columns
	 *
	 * @return array
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	public function manage_product_columns( $columns ) {
		$product_items = array( 'wpm_pgw_code' => get_option('wpm_pgw_label') );
		$ref_pos       = array_search ( 'sku', array_keys ( $columns ) );
		$columns = array_slice ( $columns, 0, $ref_pos + 1, true ) + $product_items + array_slice ( $columns, $ref_pos + 1, count ( $columns ) - 1, true );

		return $columns;
	}

	/**
	 * Show the GTIN code inside the product list.
	 *
	 * @param $column
	 * @return void
	 *
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	public function show_gtin_code( $column ) {

		if ( 'wpm_pgw_code' == $column ) {
			global $post, $the_product;

			if ( empty( $the_product ) || $the_product->get_id() !== $post->ID ) {
				$the_product = wc_get_product( $post->ID );
			}

			$gtin = $the_product->get_meta( '_wpm_gtin_code' );

			echo esc_html( $gtin );

		}
	}

	/**
	 * Action Links
	 *
	 * add the action links to plugin admin page
	 *
	 * @param $links | links plugin array
	 *
	 * @return   mixed Array
	 * @since    1.0
	 * @return mixed
	 * @use      plugin_action_links_{$plugin_file_name}
	 */
	public function action_links( $links ) {
		$links[] = '<a href="' . admin_url( "admin.php?page=wc-settings&tab=wpm-product-gtin-wc" ) . '">' . __( 'Settings', 'product-gtin-ean-upc-isbn-for-woocommerce' ) . '</a>';
		return $links;
	}



	/**
	 * Add the GTIN field inside the "Export products to a CSV file" as column to export.
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function export_product_default_columns( $columns ) {
		$label = get_option( 'wpm_pgw_label', __('EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ) );
		$columns['wpm_gtin_code'] = $label;
		return $columns;
	}

	/**
	 * Add the GTIN field value inside the CSV file.
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function add_gtin_to_export_data( $value, $product ) {
		$value = $product->get_meta( '_wpm_gtin_code', true, 'edit' );
		return $value;
	}

	/**
	 * Map the GTIN option to the label GTIN, inside the WooCommerce Import products tool.
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	public function import_product_mapping_options( $options ) {
		$label = get_option( 'wpm_pgw_label', __('EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ) );
		$options['wpm_gtin_code'] = $label;
		return $options;
	}

	/**
	 * Add the label GTIN inside the WooCommerce Import products tool
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function import_mapping_default_columns( $columns ) {
		$label = get_option( 'wpm_pgw_label', __('EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ) );
		$columns[$label] = 'wpm_gtin_code';
		return $columns;
	}

	/**
	 * Save the value to product meta data during the import.
	 *
	 * @param $product WC_Product
	 * @param $data
	 *
	 * @return WC_Product
	 */
	public function importer_data(  $product, $data) {
		if ( is_a( $product, 'WC_Product' ) ) {
			if( isset($data[ 'wpm_gtin_code' ]) && ! empty( $data[ 'wpm_gtin_code' ] ) ) {
				$product->update_meta_data('_wpm_gtin_code', $data[ 'wpm_gtin_code' ] );
			}
		}
		return $product;
	}


}
