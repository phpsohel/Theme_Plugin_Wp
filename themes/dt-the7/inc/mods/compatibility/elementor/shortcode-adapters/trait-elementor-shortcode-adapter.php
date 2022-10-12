<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.05.2020
 * Time: 12:18
 */

namespace The7\Adapters\Elementor\ShortcodeAdapters;

use The7\Adapters\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;
use The7_Elementor_Compatibility;
use WP_Query;

trait Trait_Elementor_Shortcode_Adapter {
	/**
	 * Output shortcode HTML.
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	function adapter_shortcode( $atts, $content = '' ) {
		if ( $this->doing_shortcode ) {
			return '';
		}

		$this->adapter_init_shortcode( $atts );

		$this->backup_post_object();
		$this->backup_theme_config();
		if ( wp_doing_ajax() ) {
			$document = The7_Elementor_Compatibility::get_frontend_document();
			if ( $document ) {
				presscore_config_base_init( $document->get_id() );
			}
		}

		ob_start();
		$this->doing_shortcode = true;

		do_action( 'the7_before_shortcode_output', $this );

		$this->setup_config();
		$this->do_shortcode( $atts, $content );

		do_action( 'the7_after_shortcode_output', $this );

		$this->doing_shortcode = false;
		$output = ob_get_clean();

		$this->restore_theme_config();
		$this->restore_post_object();

		return $output;
	}

	function adapter_init_shortcode( $atts = array() ) {
		$unique_class = $this->get_unique_class();
		$this->init_shortcode( $atts );
		$this->set_unique_class( $unique_class );
	}

	/**
	 * Setup theme config for shortcode.
	 */
	function adapter_setup_config() {
		$this->setup_config();
	}

	/**
	 * Return shortcode less file absolute path to output inline.
	 * @return string
	 */
	function adapter_get_less_file_name() {
		return $this->get_less_file_name();
	}

	/**
	 * Return less imports list.
	 * @return array
	 */
	function adapter_get_less_imports() {
		return $this->get_less_imports();
	}

	/**
	 * Return less import dir.
	 * @return array
	 */
	function adapter_get_less_import_dir() {
		return $this->get_less_import_dir();
	}

	/**
	 * Return array of prepared less vars to insert to less file.
	 * @return array
	 */
	function adapter_less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		return $this->less_vars( $less_vars );
	}

	protected function display_shortcode_content( WP_Query $query ) {
		if ( $query->found_posts ) {
			return true;
		}

		return false;
	}
}