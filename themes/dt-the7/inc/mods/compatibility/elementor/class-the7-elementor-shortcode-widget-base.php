<?php

namespace The7\Adapters\Elementor;

use DT_Shortcode_With_Inline_Css;
use The7\Adapters\Elementor\ShortcodeAdapters\The7_Shortcode_Adapter_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class The7_Elementor_Shortcode_Renderer_Widget_Base
 */
abstract class The7_Elementor_Shortcode_Adaptor_Widget_Base extends The7_Elementor_Widget_Base {

	protected $shortcode_adapter;

	public function __construct( $data = [], $args = null, DT_Shortcode_With_Inline_Css $shortcode_adapter ) {
		parent::__construct( $data, $args );
		if ( ! $shortcode_adapter instanceof The7_Shortcode_Adapter_Interface ) {
			throw new \Exception( "The class should implement The7ShortcodeAdaptorInterface" );
		}
		$this->shortcode_adapter = $shortcode_adapter;
	}

	public function generate_inline_css() {
		$this->shortcode_adapter->set_unique_class( $this->get_unique_class() );
		$this->shortcode_adapter->adapter_init_shortcode( $this->get_adapted_settings() );

		return parent::generate_inline_css();
	}

 	abstract protected function get_adapted_settings();

	/**
	 * Return shortcode less file absolute path to output inline.
	 * @return string
	 */
	protected function get_less_file_name() {
		return $this->shortcode_adapter->adapter_get_less_file_name();
	}

	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		return $this->shortcode_adapter->adapter_less_vars( $less_vars );
	}

	protected function get_less_import_dir() {
		return $this->shortcode_adapter->adapter_get_less_import_dir();
	}

	/**
	 * @return array
	 */
	protected function get_less_imports() {
		return $this->shortcode_adapter->adapter_get_less_imports();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$this->shortcode_adapter->set_unique_class( $this->get_unique_class() );
		$this->print_inline_css();
		echo $this->shortcode_adapter->adapter_shortcode( $this->get_adapted_settings() );
	}
}
