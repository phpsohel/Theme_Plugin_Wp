<?php
namespace The7\Adapters\Elementor\QueryControl\Controls;

use Elementor\Control_Select2;
use The7\Adapters\Elementor\QueryControl\The7_Query_Control_Module;

defined( 'ABSPATH' ) || exit;

class The7_Control_Query extends Control_Select2 {

	public function get_type() {
		return The7_Query_Control_Module::QUERY_CONTROL_ID;
	}

	/**
	 * 'query' can be used for passing query args in the structure and format used by WP_Query.
	 * @return array
	 */
	protected function get_default_settings() {
		return array_merge(
			parent::get_default_settings(), [
				'query' => '',
			]
		);
	}
}
