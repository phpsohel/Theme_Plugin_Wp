<?php

namespace The7\Elementor\Modules;


use Elementor\Controls_Manager;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class The7_Lazy_Loading_Support {

	public function __construct() {
		//inject controls
		add_action( 'elementor/element/before_section_end', [ $this, 'update_controls' ], 10, 3 );
		add_filter( 'elementor/image_size/get_attachment_image_html', [ $this, 'get_attachment_image_html' ], 10, 4 );

	}

	public function update_controls( $widget, $section_id, $args ) {
		$widgets = [
			'image'          => [
				'section_name' => 'section_image',
			],
			'call-to-action' => [
				'section_name' => 'section_content',
				'condition'    => [
					'graphic_element' => 'image',
				],
			],
		];

		if ( ! array_key_exists( $widget->get_name(), $widgets ) ) {
			return;
		}
		if ( $widgets[ $widget->get_name() ]['section_name'] !== $section_id ) {
			return;
		}
		$control_data = Plugin::instance()->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'lazy_load' );
		if ( is_wp_error( $control_data ) ) {
			$control_params = [
				'label' => __( 'Lazy Load', 'the7mk2' ),
				'type'  => Controls_Manager::SWITCHER,
			];
			if ( isset( $widgets[ $widget->get_name() ]['condition'] ) ) {
				$control_params['condition'] = $widgets[ $widget->get_name() ]['condition'];
			}

			$widget->add_control( 'lazy_load', $control_params );
		}
	}

	public function get_attachment_image_html( $html, $settings, $image_size_key, $image_key ) {
		if ( Plugin::$instance->editor->is_edit_mode() ) {
			add_filter( 'dt_of_get_option-general-images_lazy_loading', '__return_false' );
		}

		if ( ! presscore_lazy_loading_enabled() || ! isset( $settings['lazy_load'] ) || empty( $settings['lazy_load'] ) ) {
			return $html;
		}

		if ( isset( $settings[ $image_size_key . '_size' ] ) && $settings[ $image_size_key . '_size' ] === 'custom' ) {
			$custom_dimension = $settings[ $image_size_key . '_custom_dimension' ];
			$attachment_size = [
				// Defaults sizes
				0 => null, // Width.
				1 => null, // Height.

				'bfi_thumb' => true,
				'crop'      => true,
			];

			$has_custom_size = false;
			if ( ! empty( $custom_dimension['width'] ) ) {
				$has_custom_size = true;
				$attachment_size[0] = $custom_dimension['width'];
			}

			if ( ! empty( $custom_dimension['height'] ) ) {
				$has_custom_size = true;
				$attachment_size[1] = $custom_dimension['height'];
			}

			if ( ! $has_custom_size ) {
				$attachment_size = 'full';
			}
			$image = $settings[ $image_key ];
			$image_src = wp_get_attachment_image_src( $image['id'], $attachment_size );
			if ( empty( $image_src[0] ) && 'thumbnail' !== $attachment_size ) {
				$image_src = wp_get_attachment_image_src( $image['id'] );
			}
			if ( $image_src ) {
				$img_width = isset( $image_src[1] ) ? $image_src[1] : 1000;
				$img_height = isset( $image_src[2] ) ? $image_src[2] : 1000;

				$html = str_replace( 'src="', 'width="' . $img_width . '" height="' . $img_height . '" src="', $html );

				if ( strpos( $html, 'class="' ) === false ) {
					$html = str_replace( 'src="', 'class="" src="', $html );
				}
			}
		}

		$re = '/width=[\'"](\d+).*height=[\'"](\d+)/';
		preg_match( $re, $html, $matches );
		if ( isset( $matches[1], $matches[2] ) ) {
			$_width = absint( $matches[1] );
			$_height = absint( $matches[2] );
			$_src_placeholder = "data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' viewBox%3D'0 0 $_width $_height'%2F%3E";
			$html = str_replace( 'src="', 'src="' . $_src_placeholder . '" data-src="', $html );
			$html = str_replace( 'srcset="', 'data-srcset="', $html );
			$html = str_replace( 'class="', 'class="lazy-load ', $html );
		}

		return $html;
	}
}