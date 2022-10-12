<?php
/**
 * The7 elements product data tabs widget for Elementor.
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class The7_Elementor_Elements_Woocommerce_Product_Tabs extends Widget_Base {

	public function get_name() {
		return 'the7-woocommerce-product-data-tabs';
	}

	public function get_title() {
		return __( 'Product Data Tabs', 'the7mk2' );
	}

	public function get_icon() {
		return 'eicon-product-tabs the7-widget';
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'data', 'product', 'tabs', 'the7' ];
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single' ];
	}

	public function render_plain_content() {
	}

	protected function _register_controls() {
		$this->start_controls_section( 'section_product_tabs_style', [
			'label' => __( 'Info', 'the7mk2' ),
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
			<?php
			setup_postdata( $product->get_id() );
			wc_get_template( 'single-product/tabs/tabs.php' );
			?>
        </div>
		<?php
	}
}
