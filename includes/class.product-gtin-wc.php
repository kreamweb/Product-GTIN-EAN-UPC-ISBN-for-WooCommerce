<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, general hooks, and
 * loads admin or public classes.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Product_GTIN_for_WooCommerce
 * @subpackage Product_GTIN_for_WooCommerce/includes
 * @author     Emanuela Castorina <emanuela@kream.it>
 */
class WPM_Product_GTIN_WC {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * File path of main plugin file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Absolute plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Absolute plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Absolute path to plugin includes dir.
	 *
	 * @var string
	 */
	public $includes_path;

	/**
	 * Admin instance
	 *
	 * @var WPM_Product_GTIN_WC_Admin
	 */
	public $admin;

	/**
	 * Frontend instance
	 *
	 * @var WPM_Product_GTIN_WC_Frontend
	 */
	public $frontend;

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Product_GTIN_WC
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main WPM_Product_GTIN_WC Instance.
	 *
	 * Ensures only one instance of WPM_Product_GTIN_WC is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return WPM_Product_GTIN_WC - Main instance.
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
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->version = WPM_PRODUCT_GTIN_WC_VERSION;
		$this->plugin_name = 'product-gtin-ean-upc-isbn-for-woocommerce';

		// Path.
		$this->file          = WPM_PRODUCT_GTIN_WC_FILE;
		$this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
		$this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
		$this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
		$this->load_dependencies();
		$this->hooks();

		add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'custom_products_api_data'), 90, 2 );

		//Integration with Google Product Feed
		add_filter( 'woocommerce_gpf_custom_field_list', array( $this,'wpm_woocommerce_gpf_custom_field_list') );

		//Structured data
		add_filter( 'woocommerce_structured_data_product', array( $this,'wpm_structured_data_product'), 10, 2 );

	}


	/**
	 * Add the GTIN code to structured data.
	 * thanks to @stroykamarketcom
	 * @param $data
	 *
	 * @return mixed
	 */
	public function wpm_structured_data_product( $data, $product ) {
		$property          = apply_filters( 'wpm_structured_data_product_property', get_option( 'wpm_pgw_structured_data_field', 'gtin' ), $product );
		$data[ $property ] = $product->get_meta( '_wpm_gtin_code' );
		return $data;
	}

	/**
     * Integration with Google Product Feed added thanks to Lee Willis.
     *
     * https://gist.github.com/leewillis77/a4043e2b70e0ada39132d52daae00c82
     *
	 * @param $list
	 *
	 * @return mixed
	 */
	public function wpm_woocommerce_gpf_custom_field_list( $list ) {
		$list['meta:_wpm_gtin_code'] = __('GTIN field from Product GTIN for WooCommerce plugin', 'product-gtin-ean-upc-isbn-for-woocommerce');
		return $list;
	}



	private function hooks(){
		add_action( 'plugins_loaded', array( $this, 'bootstrap' ) );

		//set locale
		add_action( 'init', array( $this, 'set_locale' ) );

	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once WPM_PRODUCT_GTIN_WC_DIR . 'includes/class.product-gtin-wc-admin.php';
		require_once WPM_PRODUCT_GTIN_WC_DIR . 'includes/class.product-gtin-wc-frontend.php';

	}


	/**
	 * Call the main classes of  plugin.
	 *
	 */
	public function bootstrap(){
		// Woocommerce installation check _________________________
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', array( $this, 'install_woocommerce_admin_notice') );
			return;
		}

		if ( $this->is_admin() ) {
			$this->admin = WPM_Product_GTIN_WC_Admin::instance();
		} else {
			$this->frontend = WPM_Product_GTIN_WC_Frontend::instance();
		}

	}

	/**
	 * Show an Admin Notice if WooCommerce is not installed.
	 */
	public function install_woocommerce_admin_notice(  ) {
		?>
        <div class="error">
            <p><?php _e( 'Product GTIN (EAN, UPC, ISBN) for WooCommerce is enabled but not effective. It requires WooCommerce in order to work.', 'product-gtin-ean-upc-isbn-for-woocommerce' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Check if is admin.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return boolean
	 */
	public function is_admin(){
		$context_check = isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend';
		$is_admin = is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && $context_check );
		return apply_filters( 'wpm_product_gtin_is_admin', $is_admin );
	}

	/**
	 * Load localization files.
	 *
	 * @since    1.0.0
	 */
	public function set_locale() {
		load_plugin_textdomain( 'product-gtin-ean-upc-isbn-for-woocommerce', false, plugin_basename( $this->plugin_path ) . '/languages' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * @param $response
	 * @param $post
	 *
	 * @return mixed
	 */
	public function custom_products_api_data( $response, $post ) {
		// retrieve a custom field and add it to API response
		if( is_a( $post, 'WC_Product') ){
			$response->data['ean_code'] = $post->get_meta( '_wpm_gtin_code' );
		}

		return $response;
	}

}
