<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Product_Page
 * @subpackage Product_Page/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Product_Page
 * @subpackage Product_Page/admin
 * @author     Developer Junayed <admin@easeare.com>
 */
class Product_Page_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_Page_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Page_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/product-page-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_Page_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Page_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/product-page-admin.js', array( 'jquery' ), $this->version, false );

	}
	function remove_all_product_tabs( $tabs ) {
		unset( $tabs['description'] );        // Remove the description tab
		unset( $tabs['reviews'] );       // Remove the reviews tab
		unset( $tabs['additional_information'] );    // Remove the additional information tab
		return $tabs;
	  }

	function woocommerce_product_tab_remove($the_content){
		if(strstr( $the_content, '[product_page' )){
			add_filter( 'woocommerce_product_tabs', [$this, 'remove_all_product_tabs'], 11 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 20 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}
		return $the_content;
	}

	function custom_product_columnns( $columns ){
		$newColumns = $columns;
		foreach($columns as $key => $column){
			unset($columns[$key]);
		}
	
		$i = 0;
		foreach($newColumns as $key1 => $val){
			$columns[$key1] = $val;
			if($i === 2){
				$columns['product_page'] = 'Shortcode';
			}
			$i++;
		}
	
		return $columns;
	}
	function custom_product_columnns_value( $column, $postid ) {
		if ( $column == 'product_page' ) {
			echo '<input type="text" readonly value=\'[product_page id="'.$postid.'"]\'>';
		}
	}
}
