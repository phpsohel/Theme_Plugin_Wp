<?php

namespace The7\Adapters\Elementor\ShortcodeAdapters;

use The7\Adapters\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;

defined( 'ABSPATH' ) || exit;

interface The7_Shortcode_Adapter_Interface {
	const QUERY_CONTROL_NAME = 'query';
	/**
	 * Output shortcode HTML.
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	function adapter_shortcode( $atts, $content = '' );

	/**
	 * Setup theme config for shortcode.
	 */
	function adapter_setup_config();

	/**
	 * Return array of prepared less vars to insert to less file.
	 * @return array
	 */
	function adapter_less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars );

	/**
	 * Return shortcode less file absolute path to output inline.
	 * @return string
	 */
	function adapter_get_less_file_name();

	/**
	 * Return less imports list.
	 * @return array
	 */
	function adapter_get_less_imports();

	/**
	 * Return less import dir.
	 * @return array
	 */
	function adapter_get_less_import_dir();

	function adapter_init_shortcode( $atts = array() );
}