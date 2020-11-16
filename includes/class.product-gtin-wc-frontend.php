<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The core frontend plugin class.
 *
 * @since      1.0.0
 * @package    Product_GTIN_for_WooCommerce
 * @subpackage Product_GTIN_for_WooCommerce/includes
 * @author     Emanuela Castorina <emanuela@kream.it>
 */
class WPM_Product_GTIN_WC_Frontend {

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Product_GTIN_WC_Frontend
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main WPM_Product_GTIN_WC_Frontend Instance.
	 *
	 * Ensures only one instance of WPM_Product_GTIN_WC_Frontend is loaded or can be loaded.
	 *
	 * @return WPM_Product_GTIN_WC_Frontend - Main instance.
	 * @since 1.0
	 * @static
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

		add_shortcode( 'wpm_product_gtin', array( $this, 'product_gtin_shortcode' ) );

		if ( 'yes' == get_option( 'wpm_pgw_single_product', 'yes' ) ) {

			$this->show_code_on_single_product_page();

			//custom styles and javascripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 20 );
			add_filter( 'woocommerce_available_variation', array( $this, 'add_params_to_available_variation' ), 10, 3 );
		}
		if ( 'yes' == get_option( 'wpm_pgw_loop', 'no' ) ) {
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'show_code' ), 3 );
		}

		if ( 'yes' == get_option( 'wpm_pgw_cart', 'no' ) || 'yes' == get_option( 'wpm_pgw_checkout_page', 'no' ) ) {
			add_action( 'woocommerce_get_item_data', array( $this, 'show_code_on_cart' ), 10, 2 );
		}

		if ( 'yes' == get_option( 'wpm_pgw_search_by_code', 'no' ) ) {
			add_action( 'pre_get_posts', array( $this, 'extend_product_search' ), 10 );
		}

		if ( 'yes' == get_option( 'wpm_pgw_order_item_meta', 'no' ) ) {
			add_action( 'woocommerce_checkout_create_order_line_item', array(
				$this,
				'display_order_item_data'
			), 20, 3 );
		}
	}

	/**
	 * Show the code in a specified position inside the single product page.
	 */
	public function show_code_on_single_product_page() {
		$position                = get_option( 'wpm_pgw_position', 'meta' );
		$priority                = 10;
		$action                  = '';
		$priority_single_excerpt = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt' );

		$priority_after_meta     = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta' );

		switch ( $position ) {
			case 'meta':
				$action = 'woocommerce_product_meta_start';
				break;
			case 'before_add_to_cart':
				$action = 'woocommerce_before_add_to_cart_form';
				break;
			case 'after_add_to_cart':
				$action = 'woocommerce_after_add_to_cart_form';
				break;
			case 'before_excerpt':
				$action   = 'woocommerce_single_product_summary';
				$priority = $priority_single_excerpt ? $priority_single_excerpt - 1 : 18;
				break;
			case 'after_excerpt':
				$action   = 'woocommerce_single_product_summary';
				$priority = $priority_single_excerpt ? $priority_single_excerpt + 1 : 22;
				break;
			case 'after_meta':
				$action   = 'woocommerce_single_product_summary';
				$priority = $priority_after_meta ? $priority_after_meta + 1 : 42;
				break;
			default:
				break;
		}

		$action   = apply_filters( 'wpm_pgw_show_single_product_message_position_action', $action, $position, $priority );
		$priority = apply_filters( 'wpm_pgw_show_single_product_message_position_priority', $priority, $position, $action );

		add_action( $action, array( $this, 'show_code' ), $priority );

	}

	/**
	 * Display the EAN in order item meta.
	 *
	 * @param $item
	 * @param $cart_item_key
	 * @param $values
	 * @param $order
	 *
	 * @since 1.0.3
	 */
	public function display_order_item_data( $item, $cart_item_key, $values ) {

		$label = get_option( 'wpm_pgw_public_label', __( 'EAN', 'product-gtin-ean-upc-isbn-for-woocommerce' ) );
		$label = substr( $label, - 1 ) == ':' ? str_replace( ':', '', $label ) : $label;

		if ( isset( $values['data'] ) ) {
			$product    = $values['data'];
			$gtin       = $product->get_meta( '_wpm_gtin_code' );
			$gtin_label = $product->get_meta( '_wpm_gtin_code_label' );

			$label = empty( $gtin_label ) ? $label : $gtin_label;
			$label = substr( $label, - 1 ) == ':' ? str_replace( ':', '', $label ) : $label;

			if ( ! empty( $gtin ) ) {
				$item->add_meta_data( $label, $gtin );
			}
		}
	}

	public function enqueue_styles_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wpm_product_gtin_frontend', WPM_PRODUCT_GTIN_WC_ASSETS_URL . '/js/product-gtin-wc-frontend' . $suffix . '.js', array( 'jquery' ), WPM_PRODUCT_GTIN_WC_VERSION, true );
		wp_localize_script( 'wpm_product_gtin_frontend', 'wpm_product_gtin', array(
			'hide_is_empty' => get_option( 'wpm_pgw_hide_code_empty', 'no' )
		) );
	}

	public function show_code() {

		echo do_shortcode( "[wpm_product_gtin]" );
	}

	/**
	 * @param $atts array
	 *
	 * @return string
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	public function product_gtin_shortcode( $atts ) {
		global $post;

		$atts = shortcode_atts( array(
			'id'            => '',
			'label'         => get_option( 'wpm_pgw_public_label', __( "Code EAN:", 'product-gtin-ean-upc-isbn-for-woocommerce' ) ),
			'wrapper'       => is_shop() ? 'div' : 'span',
			'wrapper_code'  => 'span',
			'class_wrapper' => 'wpm_gtin_code_wrapper',
			'class'         => 'wpm_gtin',
		), $atts, 'wpm_product_gtin' );

		if ( ! empty( $atts['id'] ) ) {
			$product_data = get_post( $atts['id'] );
		} elseif ( ! is_null( $post ) ) {
			$product_data = $post;
		} else {
			return '';
		}

		$product = is_object( $product_data ) && in_array( $product_data->post_type, array(
			'product',
			'product_variation'
		), true ) ? wc_setup_product_data( $product_data ) : false;

		if ( ! $product ) {
			return '';
		}

		//check if the label is changed for the current product
		$override_label = $product->get_meta( '_wpm_gtin_code_label' );
		$atts['label']  = empty( $override_label ) ? $atts['label'] : $override_label;
		$atts           = apply_filters( 'wpm_show_gtin_shortcode_attributes', $atts, $product );
		ob_start();
		$this->gtin_code( $product, $atts );

		return ob_get_clean();
	}

	/**
	 * @param $product
	 * @param $atts
	 *
	 */
	public function gtin_code( $product, $atts ) {
		$gtin = $product->get_meta( '_wpm_gtin_code' );

		$hide_empty = get_option( 'wpm_pgw_hide_code_empty' );
		if ( ! empty( $gtin ) || empty( $gtin ) && 'no' == $hide_empty || ( is_single() && $product->is_type( 'variable' ) ) ) {
			$gtin = ( empty( $gtin ) && 'no' == $hide_empty ) ? esc_html__( 'N/A', 'product-gtin-ean-upc-isbn-for-woocommerce' ) : $gtin;
			$code = sprintf( '<%1$s class="%3$s">%2$s <%4$s class="%5$s wpm_pgw_code" data-product-id="%7$s">%6$s</%4$s></%1$s>', $atts['wrapper'], $atts['label'], esc_attr( $atts['class_wrapper'] ), $atts['wrapper_code'], esc_attr( $atts['class'] ), $gtin, $product->get_id() );
			echo apply_filters( 'wpm_show_gtin_code', $code, $product, $atts );
		}

	}

	/**
	 * Add custom params to variations
	 *
	 * @access public
	 *
	 * @param $args      array
	 * @param $product   object
	 * @param $variation object
	 *
	 * @return array
	 * @since  1.1.1
	 */
	public function add_params_to_available_variation( $args, $product, $variation ) {
		$args['wpm_pgw_code'] = $variation->get_meta( '_wpm_gtin_code' );

		return $args;
	}

	/**
	 * Add the code GTIN inside the list of item data when
	 * it is showed on cart.
	 *
	 * @param $item_data
	 * @param $cart_item
	 *
	 * @return mixed
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	public function show_code_on_cart( $item_data, $cart_item ) {
		if ( ! isset( $cart_item['data'] ) ) {
			return $item_data;
		}
		if ( is_cart() && 'yes' == get_option( 'wpm_pgw_cart', 'no' ) || is_checkout() && 'yes' == get_option( 'wpm_pgw_checkout_page', 'no' ) ) {
			$product = $cart_item['data'];
			if ( is_a( $product, 'WC_Product' ) ) {
				$gtin       = $product->get_meta( '_wpm_gtin_code' );
				$gtin_label = $product->get_meta( '_wpm_gtin_code_label' );
				if ( ! empty( $gtin ) ) {
					$label = empty( $gtin_label ) ? get_option( 'wpm_pgw_public_label', __( "Code EAN:", 'product-gtin-ean-upc-isbn-for-woocommerce' ) ) : $gtin_label;

					//to avoid double ':' char inside the cart.
					$label = substr( $label, - 1 ) == ':' ? str_replace( ':', '', $label ) : $label;
					$label = apply_filters( 'wpm_pgw_public_label_on_cart', $label, $cart_item );

					$item_data['wpm_pgw_code']['name']  = $label;
					$item_data['wpm_pgw_code']['value'] = $gtin;
				}
			}
		}

		return $item_data;
	}

	/**
	 * Search also for GTIN Code in WooCommerce Search Widget
	 *
	 * @param $wp_query
	 */
	public function extend_product_search( $wp_query ) {
		global $wpdb;

		if ( ! isset( $wp_query->query['s'] ) ) {
			return;
		}

		$posts = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wpm_gtin_code' AND meta_value LIKE %s;", esc_sql( '%' . $wp_query->query['s'] . '%' ) ) );
		if ( ! $posts ) {
			return;
		}

		unset( $wp_query->query['s'] );
		unset( $wp_query->query_vars['s'] );
		$wp_query->query['post__in'] = array();
		foreach ( $posts as $id ) {
			$post = get_post( $id );
			if ( $post->post_type == 'product_variation' ) {
				$wp_query->query['post__in'][]      = $post->post_parent;
				$wp_query->query_vars['post__in'][] = $post->post_parent;
			} else {
				$wp_query->query_vars['post__in'][] = $post->ID;
			}
		}
	}
}
