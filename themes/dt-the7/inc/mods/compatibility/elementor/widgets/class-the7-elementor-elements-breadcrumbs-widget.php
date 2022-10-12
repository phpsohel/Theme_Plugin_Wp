<?php
/**
 * The7 breadcrumb widget for Elementor.
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use The7\Adapters\Elementor\The7_Elementor_Widget_Base;


defined( 'ABSPATH' ) || exit;

class The7_Elementor_Elements_Breadcrumbs_Widget extends The7_Elementor_Widget_Base {

	public function get_name() {
		return 'the7-breadcrumb';
	}

	public function get_title() {
		return __( 'Breadcrumbs', 'the7mk2' );
	}

	public function get_icon() {
		return 'eicon-navigation-horizontal the7-widget';
	}

	public function get_style_depends() {
		the7_register_style( 'the7-widget', PRESSCORE_THEME_URI . '/css/compatibility/elementor/the7-widget' );

		return [ 'the7-widget' ];
	}

	protected function _register_controls() {

		$this->start_controls_section( 'section_breadcrumb_style', [
				'label' => __( 'Style', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			] );
		$this->add_control( 'meta_separator', [
				'label'     => __( 'Separator Between', 'elementor-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '/',
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs li:not(:first-child):before' => 'content: "{{VALUE}}"',
				],
			] );
		$this->add_control( 'divider_color', [
				'label'     => __( 'Separator Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs li:not(:first-child):before' => 'color: {{VALUE}}',
				],
			] );

		$this->add_control( 'text_color', [
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs' => 'color: {{VALUE}}',
				],
			] );

		$this->add_control( 'link_color', [
				'label'     => __( 'Link Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs li > a' => 'color: {{VALUE}}',
				],
			] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .breadcrumbs',
			] );

		$this->add_responsive_control( 'alignment', [
				'label'     => __( 'Alignment', 'the7mk2' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .breadcrumbs' => 'text-align: {{VALUE}}',
				],
			] );

		$this->end_controls_section();
	}

	protected function render() {
		echo presscore_get_page_title_breadcrumbs();
	}

}
