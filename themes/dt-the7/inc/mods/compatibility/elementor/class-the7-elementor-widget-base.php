<?php
/**
 * @package The7
 */

namespace The7\Adapters\Elementor;

use Elementor\Plugin;
use Elementor\Widget_Base;
use The7\Adapters\Elementor\The7_Elementor_Less_Vars_Decorator;
use \The7_Less_Compiler;

defined( 'ABSPATH' ) || exit;

abstract class The7_Elementor_Widget_Base extends Widget_Base {

	const WIDGET_CSS_CACHE_ID = '_the7_elementor_widgets_css';

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the widget keywords.
	 *
	 * @since 1.0.10
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'the7' ];
	}

	/**
	 * Get widget category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'the7-elements', 'pro-elements' ];
	}

	/**
	 * Return unique shortcode class like {$unique_class_base}-{$sc_id}.
	 *
	 * @return string
	 */
	public function get_unique_class() {
		return $this->get_name() . '-' . $this->get_id();
	}

	protected function print_inline_css() {
		if ( Plugin::$instance->editor->is_edit_mode() ) {
			add_filter( 'dt_of_get_option-general-images_lazy_loading', '__return_false' );
			echo '<style type="text/css">';
			echo $this->generate_inline_css();
			echo '</style>';
		}
	}

	/**
	 * @return false|string
	 * @throws \Exception
	 */
	public function generate_inline_css() {
		$less_file = $this->get_less_file_name();

		if ( ! $less_file ) {
			return '';
		}

		$lessc = new The7_Less_Compiler( (array) $this->get_less_vars(), (array) $this->get_less_import_dir() );

		return $lessc->compile_file( $less_file, $this->get_less_imports() );
	}

		/**
	 * Return less import dir.
	 *
	 * @return array
	 */
	protected function get_less_import_dir() {
		return [ PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor' ];
	}

	/**
	 * @return array
	 */
	protected function get_less_vars() {
		$less_vars = new The7_Elementor_Less_Vars_Decorator( the7_get_new_shortcode_less_vars_manager() );

		$this->less_vars( $less_vars );

		return $less_vars->get_vars();
	}

	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		// Do nothing.
	}

	/**
	 * @return array
	 */
	protected function get_less_imports() {
		return [];
	}

	/**
	 * @return bool|string
	 */
	protected function get_less_file_name() {
		return false;
	}

	/**
	 * @param $dim
	 *
	 * @return string
	 */
	protected function combine_dimensions( $dim ) {
		$units = $dim['unit'];

		return "{$dim['top']}{$units} {$dim['right']}{$units} {$dim['bottom']}{$units} {$dim['left']}{$units}";
	}

	/**
	 * @param array $val
	 * @param int   $default
	 *
	 * @return int|string
	 */
	protected function combine_slider_value( $val, $default = 0 ) {
		if ( empty( $val['size'] ) || ! isset( $val['unit'] ) ) {
			return $default;
		}

		return $val['size'] . $val['unit'];
	}

	/**
	 * @return false|int
	 */
	protected function get_current_post_id() {
		// Elementor Pro >= 2.9.1
		if ( class_exists( 'ElementorPro\Core\Utils' ) ) {
			return \ElementorPro\Core\Utils::get_current_post_id();
		}

		// Elementor Pro < 2.9.1
		if ( class_exists( 'ElementorPro\Classes\Utils' ) ) {
			return \ElementorPro\Classes\Utils::get_current_post_id();
		}

		return get_the_ID();
	}
}
