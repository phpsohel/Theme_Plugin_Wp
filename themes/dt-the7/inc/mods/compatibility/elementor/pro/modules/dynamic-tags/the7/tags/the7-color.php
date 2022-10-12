<?php

namespace The7\Adapters\Elementor\Pro\DynamicTags\The7\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use The7\Adapters\Elementor\Pro\DynamicTags\The7\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class The7_Color extends Tag {

	public function get_categories() {
		return [ Module::COLOR_CATEGORY ];
	}

	public function get_group() {
		return Module::THE7_GROUP;
	}

	public function get_title() {
		return __( 'The7 color', 'the7mk2' );
	}

	public function get_name() {
		return 'the7-color';
	}

	protected function _register_controls() {
		$this->add_control( 'color-type', [
			'label'  => __( 'Color', 'the7mk2' ),
			'type'   => Controls_Manager::SELECT,
			'groups' => $this->get_custom_keys_array(),
		] );
	}

	public function render() {
		$color_type = $this->get_settings( 'color-type' );
		if ( ! empty( $color_type ) ) {
			$value = $this->get_color( $color_type );
		}

		if ( empty( $value ) && $this->get_settings( 'fallback' ) ) {
			$value = $this->get_settings( 'fallback' );
		}
		if ( empty( $value ) ) {
			return;
		}
		echo wp_kses_post( $value );
	}

	private function get_custom_keys_array() {
		$options = [
			''                                => __( 'Select...', 'the7mk2' ),
			'content-headers_color'           => __( 'Headings', 'the7mk2' ),
			'content-primary_text_color'      => __( 'Primary text', 'the7mk2' ),
			'content-secondary_text_color'    => __( 'Secondary text', 'the7mk2' ),
			'accent'                          => __( 'Accent', 'the7mk2' ),
			'buttons-color_mode'              => __( 'Button background normal', 'the7mk2' ),
			'buttons-hover_color_mode'        => __( 'Button background hover', 'the7mk2' ),
			'buttons-text_color_mode'         => __( 'Button text normal', 'the7mk2' ),
			'buttons-text_hover_color_mode'   => __( 'Button text hover', 'the7mk2' ),
			'buttons-border-color_mode'       => __( 'Button border normal', 'the7mk2' ),
			'buttons-hover-border-color_mode' => __( 'Button border hover', 'the7mk2' ),
		];

		return $options;
	}

	public function get_color( $color_type ) {
		switch ( $color_type ) {
			case 'accent':
				$color_val = the7_theme_accent_color();
				break;
			default:
				if ( substr_compare( $color_type, '_mode', - strlen( '_mode' ) ) === 0 ) {
					$color_val = $this->get_simplified_the7_color( $color_type );
					break;
				}
				$color_val = of_get_option( $color_type, '#000000' );
				break;
		}

		return $color_val;
	}

	/**
	 * Return simplified hex color.
	 */
	private function get_simplified_the7_color( $color_mode_option ) {
		$color_val = '';

		$color_option = str_replace( "_mode", "", $color_mode_option );
		switch ( of_get_option( $color_mode_option ) ) {
			case 'disabled':
				break;
			case 'accent':
				$color_val = the7_theme_accent_color();
				break;
			case 'color':
				$color_val = of_get_option( $color_option );
				break;
			case 'gradient':
				$gradient_obj = the7_less_create_gradient_obj( of_get_option( $color_option . '_gradient' ) );
				$color_val = $gradient_obj->get_color_stop( 1 )->get_color();
				break;
		}
		if ( empty( $color_val ) ) {
			$color_val = '#ffffff00';
		}

		return $color_val;
	}

}