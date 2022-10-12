<?php
/*
 * The7 elements product add to cart widget for Elementor.
 *
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class The7_Elementor_Elements_Woocommerce_Product_Add_To_Cart extends Widget_Base {

	public function get_name() {
		return 'the7-woocommerce-product-add-to-cart';
	}

	public function get_title() {
		return __( 'Add To Cart', 'elementor-pro' );
	}

	public function get_icon() {
		return 'eicon-product-add-to-cart the7-widget';
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'cart', 'product', 'button', 'add to cart', 'the7' ];
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single' ];
	}

	public function render_plain_content() {
	}


	protected function _register_controls() {
		$this->start_controls_section( 'section_product_tabs_style', [
			'label' => __( 'Styles', 'the7mk2' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'wc_style_warning', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => __( 'The style of this widget is often can be affected by thirdparty plugins. If you experience any such issue, try to deactivate related plugins.', 'the7mk2' ),
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		] );
		$this->end_controls_section();
	}

	protected function render() {
		global $product;

		$product = wc_get_product();

		if ( empty( $product ) ) {
			return;
		}
		?>

		<div class="the7-elementor-widget the7-elementor-product-<?php echo esc_attr( wc_get_product()->get_type() ); ?>">
			<?php woocommerce_template_single_add_to_cart(); ?>
		</div>

		<?php
	}
}
