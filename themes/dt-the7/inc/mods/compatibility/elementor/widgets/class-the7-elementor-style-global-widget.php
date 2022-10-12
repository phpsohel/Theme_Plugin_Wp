<?php
/**
 * The7 global style widget for Elementor. It is designed only to generete elementior dynamic styles
 *
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use The7\Adapters\Elementor\The7_Elementor_Widget_Base;


defined( 'ABSPATH' ) || exit;

class The7_Elementor_Style_Global_Widget extends The7_Elementor_Widget_Base {

	public function get_name() {
		return 'the7-global';
	}

	protected function get_less_file_name() {
		return PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor/global.less';
	}

	protected function get_less_vars() {
		include_once PRESSCORE_DIR . '/less-vars.php';
		return presscore_compile_less_vars();
	}
}
