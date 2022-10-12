<?php

namespace The7\Adapters\Elementor\Pro\WoocommerceSupport;


use Elementor\Plugin;
use Elementor\Widget_Base;
use ElementorPro\Modules\Woocommerce\Documents\Product;
use ElementorPro\Modules\Woocommerce\Widgets\Products_Base;
use The7\Adapters\Elementor\The7_Elementor_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woocommerce_Support {

    /*defines the list of the widgets where The7 theme templates would not be applied*/
	protected static $excluded_widgets = [
		'woocommerce-archive-description',
		'woocommerce-breadcrumb',
		'wc-categories',
		'woocommerce-category-image',
		'woocommerce-menu-cart',
		'woocommerce-product-additional-information',
		'woocommerce-product-content',
		'woocommerce-product-data-tabs',
		'woocommerce-product-images',
		'woocommerce-product-meta',
		'woocommerce-product-price',
		'woocommerce-product-rating',
		'woocommerce-product-short-description',
		'woocommerce-product-stock',
		'woocommerce-product-title',
		'wc-products',
		'wc-single-elements',
		'woocommerce-product-add-to-cart',
		'wc-add-to-cart'
	];
	protected $current_widget;

	public function __construct() {
		add_filter( 'wc_get_template', [ $this, 'filter_woocommerce_templates' ], 50, 5 );
		add_filter( 'wc_get_template_part', [ $this, 'filter_woocommerce_template_part' ], 50, 3 );

		add_action( 'elementor/widget/before_render_content', [ $this, 'before_render_content' ] );
		add_filter( 'elementor/widget/render_content', [ $this, 'after_render_content' ], 10, 2 );

		add_filter( 'elementor/widget/render_content', [ $this, 'fix_pages_widget_preview' ], 10, 2 );

		//modify product controls
		add_action( 'elementor/element/before_section_end', [ $this, 'update_controls' ] );
	}

	public function before_render_content( Widget_Base $widget ) {
		$this->current_widget = $widget;
		if ( $this->is_ignore_theme_templates() ) {

			remove_filter( 'woocommerce_output_related_products_args', 'dt_woocommerce_related_products_args' );
			//add image from theme
			add_action( 'woocommerce_before_shop_loop_item_title', 'presscore_wc_template_loop_product_thumbnail', 10 );
			remove_action( 'dt_woocommerce_shop_loop_images', 'dt_woocommerce_get_alt_product_thumbnail', 11 );

			add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

			remove_action( 'woocommerce_shop_loop_item_title', 'dt_woocommerce_template_loop_product_title', 10 );
			remove_action( 'woocommerce_shop_loop_item_desc', 'dt_woocommerce_template_loop_product_short_desc', 15 );

			remove_action( 'woocommerce_before_single_product_summary', 'dt_woocommerce_hide_related_products' );
			remove_action( 'woocommerce_single_product_summary', 'dt_woocommerce_share_buttons_action', 60 );

			//revert category
			add_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
			add_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
			add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
			add_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );
			remove_action( 'woocommerce_shop_loop_subcategory_title', 'dt_woocommerce_template_loop_category_title', 10 );
		}
	}

	protected function is_ignore_theme_templates() {

		if ( ( $this->current_widget instanceof Products_Base ) || ( $this->current_widget && in_array( $this->current_widget->get_name(), self::$excluded_widgets, true ) ) ) {
			return true;
		}

		return false;

	}

	public function after_render_content( $widget_content, Widget_Base $widget ) {
		if ( $this->is_ignore_theme_templates() ) {
			add_filter( 'woocommerce_output_related_products_args', 'dt_woocommerce_related_products_args' );

			remove_action( 'woocommerce_before_shop_loop_item_title', 'presscore_wc_template_loop_product_thumbnail', 10 );
			add_action( 'dt_woocommerce_shop_loop_images', 'dt_woocommerce_get_alt_product_thumbnail', 11 );

			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

			add_action( 'woocommerce_shop_loop_item_title', 'dt_woocommerce_template_loop_product_title', 10 );
			add_action( 'woocommerce_shop_loop_item_desc', 'dt_woocommerce_template_loop_product_short_desc', 15 );

			add_action( 'woocommerce_before_single_product_summary', 'dt_woocommerce_hide_related_products' );
			add_action( 'woocommerce_single_product_summary', 'dt_woocommerce_share_buttons_action', 60 );

			//revert category
			remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
			remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
			remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
			remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );
			add_action( 'woocommerce_shop_loop_subcategory_title', 'dt_woocommerce_template_loop_category_title', 10 );

		}
		$this->current_widget = '';

		return $widget_content;
	}

	public function filter_woocommerce_templates( $template, $template_name, $args, $template_path, $default_path ) {
		if ( ( $this->is_ignore_theme_templates() && strpos( $template, PRESSCORE_THEME_DIR ) !== false ) || WC_TEMPLATE_DEBUG_MODE ) {
			// Get default template/.
			$default_path = WC()->plugin_path() . '/templates/';
			$template = $default_path . $template_name;
		}

		return $template;
	}

	public function filter_woocommerce_template_part( $template, $slug, $name ) {
		if ( $this->is_ignore_theme_templates() && strpos( $template, PRESSCORE_THEME_DIR ) !== false ) {
			$fallback = WC()->plugin_path() . "/templates/{$slug}-{$name}.php";
			$template = file_exists( $fallback ) ? $fallback : '';
		}

		return $template;
	}

	public function update_controls( $widget ) {
		if ( ! $widget instanceof Products_Base ) {
			return;
		}
		$control_data = [
			'selectors' => [
				'{{WRAPPER}}.elementor-wc-products ul.products li.product .button' => 'background: {{VALUE}};',
			],
		];
		The7_Elementor_Widgets::update_control_fields( $widget, 'button_background_color', $control_data );

		$control_data = [
			'options' => [
				''       => __( 'Default', 'the7mk2' ),
				'none'   => __( 'None', 'the7mk2' ),
				'solid'  => _x( 'Solid', 'Border Control', 'the7mk2' ),
				'double' => _x( 'Double', 'Border Control', 'the7mk2' ),
				'dotted' => _x( 'Dotted', 'Border Control', 'the7mk2' ),
				'dashed' => _x( 'Dashed', 'Border Control', 'the7mk2' ),
				'groove' => _x( 'Groove', 'Border Control', 'the7mk2' ),
			],
		];
		The7_Elementor_Widgets::update_control_fields( $widget, 'button_border_border', $control_data );

		$control_data = [
			'condition' => [
				'border!' => [ '', 'none' ],
			],
		];

		The7_Elementor_Widgets::update_control_fields( $widget, 'button_border_width', $control_data );

		$control_data = [
			'selectors' => [
				'{{WRAPPER}}.elementor-wc-products ul.products li.product span.onsale' => 'background: {{VALUE}}',
			],
		];
		The7_Elementor_Widgets::update_control_fields( $widget, 'onsale_text_background_color', $control_data );
	}
 

	public function fix_pages_widget_preview( $widget_content, Widget_Base $widget ) {
		if ( Plugin::$instance->editor->is_edit_mode() ) {
			$widgets = [ 'wc-elements', 'woocommerce-product-images' ];
			$widget_name = $widget->get_name();
		    if ( in_array( $widget_name, $widgets ) ) {
				ob_start();
				?>
                <script>
                    elementorFrontend.hooks.addAction('frontend/element_ready/<?php echo $widget_name; ?>.default', function ($scope, jQuery) {
                        $scope.find(".woocommerce-product-gallery").wc_product_gallery();
                    });
                </script>
				<?php
				return $widget_content . ob_get_clean();
			}
			$widgets = [ 'woocommerce-product-data-tabs', 'the7-woocommerce-product-data-tabs' ];
			if ( in_array( $widget_name, $widgets ) ) {
				ob_start();
				?>
                <script>
                    elementorFrontend.hooks.addAction('frontend/element_ready/<?php echo $widget_name; ?>.default', function ($scope, jQuery) {
                        $scope.find(".wc-tabs-wrapper, .woocommerce-tabs, #rating").trigger('init');
                    });
                </script>
				<?php
				return $widget_content . ob_get_clean();
			}
		}
		return $widget_content;
	}

}