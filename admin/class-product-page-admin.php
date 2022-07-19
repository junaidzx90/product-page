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

		add_shortcode( "single_product", [$this, "single_product_view"] );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	function admin_styles(){
		?>
		<style>
			th#single_product {
				width: 170px;
			}
		</style>
		<?php
	}

	/**
	 * Register the stylesheets for the public area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/product-page.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/product-page.js', array( 'jquery' ), $this->version, false );

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
				$columns['single_product'] = 'Shortcode';
			}
			$i++;
		}
	
		return $columns;
	}

	function custom_product_columnns_value( $column, $postid ) {
		if ( $column == 'single_product' ) {
			echo '<input type="text" readonly value=\'[single_product id="'.$postid.'"]\'>';
		}
	}

	function single_product_view($atts){
		ob_start();
		$atts = shortcode_atts(
			array(
				'id' => null,
			), $atts, 'single_product' 
		);

		if ( empty( $atts ) ) {
			return '';
		}

		if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
			return '';
		}

		$args = array(
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => ( ! empty( $atts['status'] ) ) ? $atts['status'] : 'publish',
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1,
		);

		if ( isset( $atts['sku'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_sku',
				'value'   => sanitize_text_field( $atts['sku'] ),
				'compare' => '=',
			);

			$args['post_type'] = array( 'product', 'product_variation' );
		}

		if ( isset( $atts['id'] ) ) {
			$args['p'] = absint( $atts['id'] );
		}

		// Don't render titles if desired.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// Change form action to avoid redirect.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		$single_product = new WP_Query( $args );

		$preselected_id = '0';

		// Check if sku is a variation.
		if ( isset( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

			$variation  = wc_get_product_object( 'variation', $single_product->post->ID );
			$attributes = $variation->get_attributes();

			// Set preselected id to be used by JS to provide context.
			$preselected_id = $single_product->post->ID;

			// Get the parent product object.
			$args = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'p'                   => $single_product->post->post_parent,
			);

			$single_product = new WP_Query( $args );
			?>
			<script type="text/javascript">
				jQuery( function( $ ) {
					var $variations_form = $( '[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]' ).find( 'form.variations_form' );

					<?php foreach ( $attributes as $attr => $value ) { ?>
						$variations_form.find( 'select[name="<?php echo esc_attr( $attr ); ?>"]' ).val( '<?php echo esc_js( $value ); ?>' );
					<?php } ?>
				});
			</script>
			<?php
		}

		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = true;

		ob_start();

		global $wp_query;

		// Backup query object so following loops think this is a product page.
		$previous_wp_query = $wp_query;
		// @codingStandardsIgnoreStart
		$wp_query          = $single_product;
		// @codingStandardsIgnoreEnd

		wp_enqueue_script( 'wc-single-product' );

		while ( $single_product->have_posts() ) {
			$single_product->the_post()
			?>
			<div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
				<?php require_once plugin_dir_path( __FILE__ )."partials/product-page-display.php"; ?>
			</div>
			<?php
		}

		// Restore $previous_wp_query and reset post data.
		// @codingStandardsIgnoreStart
		$wp_query = $previous_wp_query;
		// @codingStandardsIgnoreEnd
		wp_reset_postdata();

		// Re-enable titles if they were removed.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		remove_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}

	function woocommerce_single_product_summary_script( ){
		global $product;
		$regular_price = $product->get_regular_price();
		$price = $product->get_price();

		$save = ((!empty($regular_price))?floatval($regular_price) - floatval($price): 0);
		?>
		<script>
			let currencySym = jQuery(".woocommerce-Price-currencySymbol")[0].outerHTML;
			<?php if($save > 0){ ?>
			let saved = "<?php echo $save ?>";
			jQuery("p.price").first().append(`<div class="priceBadge"><img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAwIDEwMDAiIGZpbGw9IiNmZmYiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48cGF0aCBkPSJNOTcxLjcgNDY5LjlMNTMxLjQgMjhjLTExLjgtMTEuOS0yNy44LTE4LjUtNDQuNS0xOC41aC00MTRDMzguMiA5LjUgMTAgMzcuNyAxMCA3Mi40djQxNGMwIDE2LjcgNi42IDMyLjcgMTguNCA0NC41TDQ2OS41IDk3MmMxMi4zIDEyLjMgMjguNCAxOC40IDQ0LjQgMTguNCAxNi4xIDAgMzIuMi02LjEgNDQuNC0xOC40bDQxMy4xLTQxMy4yYzI0LjctMjQuNCAyNC43LTY0LjIuMy04OC45aDB6TTgyMS4yIDUzNkw1MzUuNiA4MjEuNWEzMC41NCAzMC41NCAwIDAgMS00My4zIDBMMTMyLjUgNDYxLjdWMTYyLjhjMC0xNi45IDEzLjctMzAuNiAzMC42LTMwLjZoMjk5bDM1OS4xIDM2MC41YzEyIDExLjkgMTEuOSAzMS4zIDAgNDMuM2gwek00MzguOCAzNDYuN2MwIDUwLjgtNDEuMiA5Mi05MS45IDkyLTUwLjggMC05MS45LTQxLjItOTEuOS05MnM0MS4xLTkyIDkxLjktOTJjNTAuNy4xIDkxLjkgNDEuMiA5MS45IDkyeiIvPjwvc3ZnPg=="/>SAVE&nbsp;${currencySym}${parseFloat(saved).toFixed(2)}</div>`);
			<?php } ?>
		</script>
		<?php
	}
}
