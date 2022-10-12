<?php
/**
 * Setup Elementor widgets.
 * @package The7
 */

namespace The7\Adapters\Elementor;

use Elementor\Core\DynamicTags\Dynamic_CSS;
use Elementor\Plugin;
use Elementor\Widget_Base;
use ElementorPro\Modules\GlobalWidget\Widgets\Global_Widget;
use The7\Adapters\Elementor\QueryControl\The7_Query_Control_Module;
use The7\Elementor\Modules\The7_Exend_Image_Widget;
use The7\Elementor\Modules\The7_Lazy_Loading_Support;
use The7_Elementor_Compatibility;

defined( 'ABSPATH' ) || exit;

/**
 * Class The7_Elementor_Widgets
 */
class The7_Elementor_Widgets {

	const ELEMENTOR_WIDGETS_PATH = '\ElementorPro\Modules\Woocommerce\Widgets\\';
	protected $widgets_collection_before = [];
	protected $widgets_collection_after = [];
	protected $unregister_widgets_collection = [];

	public static function add_global_dynamic_css( \Elementor\Core\Files\CSS\Base $css_file ) {
		if ( ! The7_Elementor_Compatibility::instance()->scheme_manager_control->is_elementor_schemes_disabled() ) {
			return;
		}

		$global_styles = new \The7\Adapters\Elementor\Widgets\The7_Elementor_Style_Global_Widget();
		$css = $global_styles->generate_inline_css();

		if ( empty( $css ) ) {
			return;
		}

		$css = str_replace( array( "\n", "\r" ), '', $css );
		$css_file->get_stylesheet()->add_raw_css( $css );
	}

	public static function display_inline_global_styles() {
		if ( ! Plugin::$instance->preview->is_preview_mode() ) {
			return;
		}
		if ( ! The7_Elementor_Compatibility::instance()->scheme_manager_control->is_elementor_schemes_disabled() ) {
			return;
		}
		$global_styles = new \The7\Adapters\Elementor\Widgets\The7_Elementor_Style_Global_Widget();
		$css = $global_styles->generate_inline_css();
		if ( $css ) {
			printf( "<style id='the7-elementor-dynamic-inline-css' type='text/css'>\n%s\n</style>\n", $css );
		}
	}

	/**
	 * Bootstrap widgets.
	 */
	public function bootstrap() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets_before' ], 5 ); //init our widgets before elementor
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets_after' ], 50 ); //init our widgets before elementor
		add_action( 'elementor/init', [ $this, 'elementor_add_custom_category' ] );
		add_action( 'elementor/init', [ $this, 'load_dependencies' ] );
		add_action( 'elementor/preview/init', [ $this, 'turn_off_lazy_loading' ] );
		add_action( 'elementor/editor/init', [ $this, 'turn_off_lazy_loading' ] );

		add_action( 'wp_head', [ $this, 'display_inline_global_styles' ], 1000 );
		presscore_template_manager()->add_path( 'elementor', array( 'template-parts/elementor' ) );
		add_action( 'elementor/element/parse_css', [ $this, 'add_widget_css' ], 10, 2 );
		add_action( "elementor/css-file/global/parse", [ $this, 'add_global_dynamic_css' ] );
	}

	public function add_widget_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}
		$css = '';
		if ( $element instanceof Global_Widget ) {
			if ( $element->get_original_element_instance() instanceof The7_Elementor_Widget_Base ) {
				$css = $element->get_original_element_instance()->generate_inline_css();
			}
		} else if ( $element instanceof The7_Elementor_Widget_Base ) {
			$css = $element->generate_inline_css();
		}

		if ( empty( $css ) ) {
			return;
		}

		$css = str_replace( array( "\n", "\r" ), '', $css );
		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	/**
	 * Disable lazy loading with filter.
	 */
	public function turn_off_lazy_loading() {
		add_filter( 'dt_of_get_option-general-images_lazy_loading', '__return_false' );
	}

	/**
	 * Load dependencies and populate widgets collection.
	 * @throws Exception
	 */
	public function load_dependencies() {
		require_once __DIR__ . '/modules/lazy-loading/class-the7-lazy-loading-support.php';
		new The7_Lazy_Loading_Support();

		require_once __DIR__ . '/modules/extend-image-widget/class-the7-extend-image-widget.php';
		new The7_Exend_Image_Widget();


		require_once __DIR__ . '/pro/modules/query-contol/class-the7-group-contol-query.php';
		require_once __DIR__ . '/pro/modules/query-contol/class-the7-control-query.php';
		require_once __DIR__ . '/pro/modules/query-contol/class-the7-posts-query.php';

		require_once __DIR__ . '/pro/modules/query-contol/class-the7-query-control-module.php';
		require_once __DIR__ . '/class-the7-elementor-widget-terms-selector-mutator.php';
		require_once __DIR__ . '/trait-with-pagination.php';
		require_once __DIR__ . '/trait-with-post-excerpt.php';
		require_once __DIR__ . '/class-the7-elementor-widget-base.php';
		require_once __DIR__ . '/the7-elementor-less-vars-decorator-interface.php';
		require_once __DIR__ . '/class-the7-elementor-less-vars-decorator.php';

		require_once __DIR__ . '/class-the7-elementor-shortcode-widget-base.php';
		require_once __DIR__ . '/shortcode-adapters/trait-elementor-shortcode-adapter.php';
		require_once __DIR__ . '/shortcode-adapters/class-the7-shortcode-adapter-interface.php';
		require_once __DIR__ . '/shortcode-adapters/class-the7-shortcode-query-interface.php';

		require_once __DIR__ . '/shortcode-adapters/query-adapters/Products_Query.php';
		require_once __DIR__ . '/shortcode-adapters/query-adapters/Products_Current_Query.php';

		require_once __DIR__ . '/widgets/class-the7-elementor-style-global-widget.php';

		require_once __DIR__ . '/widgets/class-the7-elementor-photo-scroller-widget.php';
		require_once __DIR__ . '/widgets/class-the7-elementor-nav-menu.php';


		$terms_selector_mutator = new The7_Elementor_Widget_Terms_Selector_Mutator();
		$terms_selector_mutator->bootstrap();

		$init_widgets = [
			'class-the7-elementor-elements-widget' => ['position' => 'before'],
			'class-the7-elementor-elements-carousel-widget' => ['position' => 'before'],
			'class-the7-elementor-elements-breadcrumbs-widget'=> ['position' => 'before'],
			'class-the7-elementor-photo-scroller-widget' => ['position' => 'before'],
			'class-the7-elementor-nav-menu' => ['position' => 'before'],
		];

		if ( class_exists( 'DT_Shortcode_Products_Carousel', false ) ) {
			$init_widgets['class-the7-elementor-elements-woocommerce-carousel-widget'] = ['position' => 'before'];
		}
		if ( class_exists( 'DT_Shortcode_ProductsMasonry', false ) ) {
			$init_widgets['class-the7-elementor-elements-woocommerce-masonry-widget']  = ['position' => 'before'];
		}

		if ( class_exists( 'Woocommerce' ) ) {
			$document_types = Plugin::$instance->documents->get_document_types();
			if ( array_key_exists( 'product-post', $document_types ) ) {
				$sorted_wc_widgets = [
					'class-the7-elementor-elements-woocommerce-product-add-to-cart',
					'Product_Add_To_Cart',
					'class-the7-elementor-elements-woocommerce-product-tabs',
					'Product_Data_Tabs',
					'class-the7-elementor-elements-woocommerce-product-related',
					'Product_Related',
					'class-the7-elementor-elements-woocommerce-product-upsells',
					'Product_Upsell',
					'class-the7-elementor-elements-woocommerce-product-meta',
					'Product_Meta',
				];
				//initialize native and the7 woocommerce widgets
				foreach ( $sorted_wc_widgets as $class_name) {
					$class_path = self::ELEMENTOR_WIDGETS_PATH . $class_name;
					if ( class_exists( $class_path ) ) {
						$native_widget = new $class_path;
						$this->collection_add_unregister_widget( $native_widget );
						$init_widgets[$class_name] = ['position' => 'after', 'widget_instance' => $native_widget];
						continue;
					}
					//widget from theme
					$init_widgets[$class_name] = ['position' => 'after'];
				}
			}
		}

		//init all widgets
		foreach ( $init_widgets as $widget_filename => $widget_params) {
			$widget = null;
			if ( array_key_exists('widget_instance', $widget_params)) {
				$widget = $widget_params['widget_instance'];
			} else {
				require_once( __DIR__ . '/widgets/' . $widget_filename . '.php' );
				$class_name = str_replace( '-', '_', $widget_filename );
				$class_name = str_replace( 'class_', '', $class_name );
				$class_name = __NAMESPACE__ . '\Widgets\\' . $class_name;
				$widget = new $class_name();
			}
			$this->collection_add_widget( $widget, $widget_params['position']);
		}
	}

	protected function collection_add_widget( $widget, $widget_position ) {
		if ($widget_position === 'before') {
			$this->widgets_collection_before[ $widget->get_name() ] = $widget;
		}
		else {
			$this->widgets_collection_after[ $widget->get_name() ] = $widget;
		}
	}

	/**
	 * Register widgets before all elementor widgets were initialized
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets_before( $widgets_manager ) {
		foreach ( $this->widgets_collection_before as $widget ) {
			$widgets_manager->register_widget_type( $widget );
		}
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets_after( $widgets_manager ) {
		foreach ( $this->unregister_widgets_collection as $widget ) {
			$widgets_manager->unregister_widget_type( $widget->get_name() );
		}
		foreach ( $this->widgets_collection_after as $widget ) {
			$widgets_manager->register_widget_type( $widget );
		}
	}

	/**
	 * Add 'The7 elements' category.
	 */
	public function elementor_add_custom_category() {
		Plugin::$instance->elements_manager->add_category( 'the7-elements', [
			'title' => esc_html__( 'The7 elements', 'the7mk2' ),
			'icon'  => 'fa fa-header',
		] );
	}

	protected function collection_add_unregister_widget( $widget ) {
		$this->unregister_widgets_collection[ $widget->get_name() ] = $widget;
	}

	public static function update_control_fields( $widget, $control_id, array $args ) {
		$control_data = Plugin::instance()->controls_manager->get_control_from_stack( $widget->get_unique_name(), $control_id );
		if ( ! is_wp_error( $control_data ) ) {
			$widget->update_control( $control_id, $args );
		}
	}

	public static function update_control_group_fields( Widget_Base $widget, $group_name, $control_data ) {
		$group = Plugin::$instance->controls_manager->get_control_groups( $group_name );
		if ( ! $group ) {
			return;
		}
		$fields = $group->get_fields();
		$control_prefix = $control_data['name'] . "_";

		foreach ( $fields as $field_id => $field ) {
			$args = [];
			if ( ! empty( $field['selectors'] ) ) {
				$args['selectors'] = self::handle_selectors( $field['selectors'], $control_data, $control_prefix );
			}
			if ( count( $args ) ) {
				self::update_control_fields( $widget, $control_prefix . $field_id, $args );
			}
		}
	}

	private static function handle_selectors( $selectors, $args, $controls_prefix ) {
		$selectors = array_combine( array_map( function ( $key ) use ( $args ) {
			return str_replace( '{{SELECTOR}}', $args['selector'], $key );
		}, array_keys( $selectors ) ), $selectors );

		if ( ! $selectors ) {
			return $selectors;
		}

		foreach ( $selectors as &$selector ) {
			$selector = preg_replace_callback( '/\{\{\K(.*?)(?=}})/', function ( $matches ) use ( $controls_prefix ) {
				return preg_replace_callback( '/[^ ]+(?=\.)/', function ( $sub_matches ) use ( $controls_prefix ) {
					return $controls_prefix . $sub_matches[0];
				}, $matches[1] );
			}, $selector );
		}

		return $selectors;
	}

	public static function update_responsive_control_fields( Widget_Base $widget, $control_id, array $args ) {
		$devices = [
			$widget::RESPONSIVE_DESKTOP,
			$widget::RESPONSIVE_TABLET,
			$widget::RESPONSIVE_MOBILE,
		];

		foreach ( $devices as $device_name ) {
			$id_suffix = $widget::RESPONSIVE_DESKTOP === $device_name ? '' : '_' . $device_name;
			self::update_control_fields( $widget, $control_id . $id_suffix, $args );
		}
	}
}
