<?php

namespace The7\Elementor\Modules;


use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use The7\Adapters\Elementor\The7_Elementor_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class The7_Exend_Image_Widget {

	public function __construct() {
		//inject controls
		add_action( 'elementor/element/before_section_end', [ $this, 'update_controls' ], 20, 3 );
		add_filter( 'elementor/image_size/get_attachment_image_html', [ $this, 'get_attachment_image_html' ], 20, 4 );

		add_filter( 'elementor/files/svg/allowed_attributes', [ $this, 'get_allowed_attributes' ] );
		add_filter( 'elementor/files/svg/allowed_elements', [ $this, 'get_allowed_elements' ] );
	}

	public function update_controls( $widget, $section_id, $args ) {
		$widgets = [
			'image' => [
				'section_name' => [ 'section_image', 'section_style_image', ],
			],
		];

		if ( ! array_key_exists( $widget->get_name(), $widgets ) ) {
			return;
		}

		$curr_section = $widgets[ $widget->get_name() ]['section_name'];
		if ( ! in_array( $section_id, $curr_section ) ) {
			return;
		}

		if ( $section_id == 'section_style_image' ) {
			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			];
			The7_Elementor_Widgets::update_control_fields( $widget, 'width', $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			];
			The7_Elementor_Widgets::update_control_fields( $widget, 'space', $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'opacity: {{SIZE}};',
				],
			];
			The7_Elementor_Widgets::update_control_fields( $widget, 'opacity', $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image:hover img, {{WRAPPER}} .elementor-image:hover svg' => 'opacity: {{SIZE}};',
				],
			];
			The7_Elementor_Widgets::update_control_fields( $widget, 'opacity_hover', $control_data );

			$control_data = [
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg',
			];

			The7_Elementor_Widgets::update_control_group_fields( $widget, Group_Control_Css_Filter::get_type(), $control_data );

			$control_data = [
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .elementor-image:hover img, {{WRAPPER}} .elementor-image:hover svg',
			];

			The7_Elementor_Widgets::update_control_group_fields( $widget, Group_Control_Css_Filter::get_type(), $control_data );

			$control_data = [
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-image:hover img, {{WRAPPER}} .elementor-image:hover svg',
			];

			The7_Elementor_Widgets::update_control_group_fields( $widget, Group_Control_Box_Shadow::get_type(), $control_data );

			$control_data = [
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg',
			];

			The7_Elementor_Widgets::update_control_group_fields( $widget, Group_Control_Border::get_type(), $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'transition-duration: {{SIZE}}s',
				],
			];
			The7_Elementor_Widgets::update_control_fields( $widget, 'background_hover_transition', $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];
			The7_Elementor_Widgets::update_responsive_control_fields( $widget, 'image_border_radius', $control_data );

			$control_data = [
				'selectors' => [
					'{{WRAPPER}} .elementor-image img, {{WRAPPER}} .elementor-image svg' => 'width: {{SIZE}}{{UNIT}};',
				],
			];
			The7_Elementor_Widgets::update_responsive_control_fields( $widget, 'width', $control_data );
		}
		if ( $section_id == 'section_image' ) {
			$control_params = [
				'label' => __( 'Inline', 'the7mk2' ),
				'type'  => Controls_Manager::SWITCHER,
			];
			if ( isset( $widgets[ $widget->get_name() ]['condition'] ) ) {
				$control_params['condition'] = $widgets[ $widget->get_name() ]['condition'];
			}

			$widget->start_injection( [
				'of'       => 'lazy_load',
				'at'       => 'before',
				'fallback' => [
					'of' => 'open_lightbox',
				],
			] );

			$widget->add_control( 'inline_image', $control_params );

			$widget->end_injection();

			$control_data = [
				'condition' => [
					'inline_image!' => [ 'yes' ],
				],
			];

			The7_Elementor_Widgets::update_control_fields( $widget, 'lazy_load', $control_data );
		}
	}

	public function get_allowed_attributes( $allowed_attributes ) {
		$allowed_attributes[] = 'result';
		$allowed_attributes[] = 'in';
		$allowed_attributes[] = 'slope';
		$allowed_attributes[] = 'flood-color';
		$allowed_attributes[] = 'flood-opacity';

		return $allowed_attributes;
	}

	public function get_allowed_elements( $allowed_elements ) {
		$allowed_elements[] = 'feoffset';
		$allowed_elements[] = 'femerge';
		$allowed_elements[] = 'fecomponenttransfer';
		$allowed_elements[] = 'fefunca';
		$allowed_elements[] = 'femergenode';
		$allowed_elements[] = 'fedropshadow';

		return $allowed_elements;
	}

	public function get_attachment_image_html( $html, $settings, $image_size_key, $image_key ) {
		if ( ! isset( $settings['inline_image'] ) || empty( $settings['inline_image'] ) ) {
			return $html;
		}
		$image = $settings[ $image_key ];
		$image_src = Group_Control_Image_Size::get_attachment_image_src( $image['id'], $image_size_key, $settings );

		$image_content = $this->get_inline_image_by_url( $image_src, array( 'class' => 'inline-image' ) );
		if ( $image_content ) {
			$html = $image_content;
		}

		return $html;
	}

	/**
	 * Returns image tag or raw SVG
	 *
	 * @param  string $url  image URL.
	 * @param  array  $attr [description]
	 *
	 * @return string|false false if cannot get image content
	 */
	private function get_inline_image_by_url( $url = null, $attr = array() ) {

		$url = esc_url( $url );

		if ( empty( $url ) ) {
			return false;
		}

		$ext = pathinfo( $url, PATHINFO_EXTENSION );
		$attr['class'] .= ' inline-image-ext-' . $ext;

		$attr = array_merge( array(
			'alt'   => '',
			'class' => '',
		), $attr );

		$base_url = site_url( '/' );
		$image_path = str_replace( $base_url, ABSPATH, $url );
		$key = md5( $image_path . 'the7_key' );
		$image_content = get_transient( $key );
		if ( ! $image_content ) {
			$image_content = file_get_contents( $image_path );
			if ( 'svg' !== $ext ) {
				$image_content = base64_encode( $image_content );
			}
		}

		if ( ! $image_content ) {
			return false;
		}
		set_transient( $key, $image_content, DAY_IN_SECONDS );
		if ( 'svg' !== $ext ) {
			return sprintf( '<img  src="data:image/%1$s;base64,%2$s" %3$s>', $ext, $image_content, $this->get_attr( $attr ) );
		}

		unset( $attr['alt'] );

		return sprintf( '<div %2$s>%1$s</div>', $image_content, $this->get_attr( $attr ) );
	}

	/**
	 * Return attributes string from attributes array.
	 *
	 * @param  array $attr Attributes string.
	 *
	 * @return string
	 */
	private function get_attr( $attr = array() ) {

		if ( empty( $attr ) || ! is_array( $attr ) ) {
			return;
		}

		$result = '';

		foreach ( $attr as $key => $value ) {
			/*if ( empty( $value ) ) {
				continue;
			}*/
			$result .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return $result;
	}
}