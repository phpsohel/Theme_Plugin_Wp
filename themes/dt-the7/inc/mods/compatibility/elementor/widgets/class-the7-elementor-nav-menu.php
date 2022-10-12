<?php

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Typography;
use The7\Adapters\Elementor\The7_Elementor_Widget_Base;
use Elementor\Icons_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class The7_Elementor_Nav_Menu extends The7_Elementor_Widget_Base {

	public function get_name() {
		return 'the7_nav-menu';
	}

	public function get_title() {
		return __( 'Vertical Menu', 'the7mk2' );
	}

	public function get_icon() {
		return 'eicon-nav-menu the7-widget';
	}

	public function get_script_depends() {
		if ( Plugin::$instance->preview->is_preview_mode() ) {
			wp_register_script(
				'the7-vertical-navigation-widget-preview',
				PRESSCORE_ADMIN_URI . '/assets/js/elementor/vertical-navigation-widget-preview.js',
				[],
				THE7_VERSION,
				true
			);

			return [ 'the7-vertical-navigation-widget-preview' ];
		}

		return [];
	}

	public function get_categories() {
		return [ 'the7-elements' ];
	}

	public function get_keywords() {
		return [ 'nav', 'menu', 'the7' ];
	}

	public function on_export( $element ) {
		unset( $element['settings']['menu'] );

		return $element;
	}

	private function get_available_menus() {
		$menus = wp_get_nav_menus();

		$options = [];

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'the7mk2' ),
			]
		);

		$menus = $this->get_available_menus();

		if ( ! empty( $menus ) ) {
			$this->add_control(
				'menu',
				[
					'label'        => __( 'Menu', 'the7mk2' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => $menus,
					'default'      => array_keys( $menus )[0],
					'save_default' => true,
					'description'  => sprintf(
						__(
							'Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.',
							'the7mk2'
						),
						admin_url( 'nav-menus.php' )
					),
				]
			);
		} else {
			$this->add_control(
				'menu',
				[
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => '<strong>' . __( 'There are no menus in your site.', 'the7mk2' ) . '</strong><br>' . sprintf( __( 'Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'the7mk2' ), admin_url( 'nav-menus.php?action=edit&menu=0' ) ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);
		}

		$this->add_control(
			'align_items',
			[
				'label'        => __( 'Menu items alignment', 'the7mk2' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
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
				'prefix_class' => 'dt-nav-menu__align-',
			]
		);

		$this->add_control(
			'submenu_display',
			[
				'label'              => __( 'Display the submenu', 'the7mk2' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'on_click',
				'options'            => [
					'always'        => __( 'Always', 'the7mk2' ),
					'on_click'      => __( 'On icon click (parent clickable)', 'the7mk2' ),
					'on_item_click' => __( 'On item click (parent unclickable)', 'the7mk2' ),
				],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout_icon',
			[
				'label' => __( 'Submenu Indicator Icons', 'the7mk2' ),

			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'   => __( 'Icon', 'the7mk2' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-caret-right',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'selected_active_icon',
			[
				'label'   => __( 'Active icon', 'the7mk2' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-caret-down',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'   => __( 'Align', 'the7mk2' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [
						'title' => __( 'Start', 'the7mk2' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'End', 'the7mk2' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => is_rtl() ? 'right' : 'left',
				'toggle'  => false,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_main-menu',
			[
				'label' => __( 'Main Menu', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'menu_typography',
				'scheme'   => Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .dt-nav-menu > li > a',
			]
		);

		$this->start_controls_tabs( 'tabs_menu_item_style' );

		$this->start_controls_tab(
			'tab_menu_item_normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_menu_item',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:not(:hover)' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_menu_item',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:not(:hover)' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_menu_item',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:not(:hover)' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_menu_item_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_menu_item_hover',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_menu_item_hover',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_menu_item_hover',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_menu_item_active',
			[
				'label' => __( 'Active', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_menu_item_active',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li.act > a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_menu_item_active',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li.act > a' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_menu_item_active',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li.act > a' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		/* This control is required to handle with complicated conditions */
		$this->add_control(
			'hr',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'border_menu_item_width',
			[
				'label'      => __( 'Border width', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a' => 'border-top-width: {{TOP}}{{UNIT}};
					border-right-width: {{RIGHT}}{{UNIT}}; border-bottom-width: {{BOTTOM}}{{UNIT}}; border-left-width:{{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding_menu_item',
			[
				'label'      => __( 'Item paddings', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_menu_item',
			[
				'label'      => __( 'Item margins', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_border_radius',
			[
				'label'     => __( 'Border Radius', 'the7mk2' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_sub-menu',
			[
				'label' => __( 'Sub Menu', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_menu_typography',
				'scheme'   => Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .vertical-sub-nav > li a',
			]
		);

		$this->start_controls_tabs( 'tabs_sub_menu_item_style' );

		$this->start_controls_tab(
			'tab_sub_menu_item_normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_sub_menu_item',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a:not(:hover)' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_sub_menu_item',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav a:not(:hover)' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_sub_menu_item',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav a:not(:hover)' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_sub_menu_item_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_sub_menu_item_hover',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_sub_menu_item_hover',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_sub_menu_item_hover',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_sub_menu_item_active',
			[
				'label' => __( 'Active', 'the7mk2' ),
			]
		);

		$this->add_control(
			'color_sub_menu_item_active',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li.act > a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_sub_menu_item_active',
			[
				'label'     => __( 'Background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li.act > a' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'border_sub_menu_item_active',
			[
				'label'     => __( 'Border color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li.act > a' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		/* This control is required to handle with complicated conditions */
		$this->add_control(
			'sub_hr',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'border_sub_menu_item_width',
			[
				'label'      => __( 'Border width', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a' => 'border-top-width: {{TOP}}{{UNIT}};
					border-right-width: {{RIGHT}}{{UNIT}}; border-bottom-width: {{BOTTOM}}{{UNIT}}; border-left-width:{{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding_sub_menu_item',
			[
				'label'      => __( 'Item paddings', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'margin_sub_menu_item',
			[
				'label'      => __( 'Item margins', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'default'    => [
					'top'      => '',
					'right'    => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a'              => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .vertical-sub-nav .vertical-sub-nav' => 'margin-left: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_sub_border_radius',
			[
				'label'     => __( 'Border Radius', 'the7mk2' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav li a' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_menu_style_icon',
			[
				'label' => __( 'Submenu Indicator Icons', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,

			]
		);

		$this->add_responsive_control(
			'icon_space',
			[
				'label'     => __( 'Spacing', 'the7mk2' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu .next-level-button.left'   => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}  .dt-nav-menu .next-level-button.right' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_color_options',
			[
				'label'     => __( 'Main menu', 'the7mk2' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Collapsed', 'the7mk2' ),
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dt-nav-menu > li > a .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dt-nav-menu > li > a svg'                => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'icon_hover_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dt-sub-menu-display-on_click .dt-nav-menu > li > a .next-level-button:hover, {{WRAPPER}} .dt-sub-menu-display-on_item_click .dt-nav-menu > li > a:hover .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dt-sub-menu-display-on_click .dt-nav-menu > li > a svg, {{WRAPPER}} .dt-sub-menu-display-on_item_click .dt-nav-menu > li > a:hover svg'                                     => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_open',
			[
				'label' => __( 'Open', 'the7mk2' ),
			]
		);

		$this->add_control(
			'icon_active_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}  .dt-nav-menu > li > .open-sub .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dt-nav-menu > li > .open-sub svg'                 => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'sub_icon_color_options',
			[
				'label'     => __( 'Sub menu', 'the7mk2' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'sub_tabs_button_style' );

		$this->start_controls_tab(
			'sub_button_normal',
			[
				'label' => __( 'Collapsed', 'the7mk2' ),
			]
		);

		$this->add_control(
			'sub_menu_icon_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav > li > a .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .vertical-sub-nav > li > a svg'                => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'sub_button_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'sub_menu_icon_hover_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dt-sub-menu-display-on_click .vertical-sub-nav > li > a .next-level-button:hover, {{WRAPPER}} .dt-sub-menu-display-on_item_click .vertical-sub-nav > li > a:hover .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dt-sub-menu-display-on_click .vertical-sub-nav > li > a svg,  {{WRAPPER}} .dt-sub-menu-display-on_item_click .vertical-sub-nav > li > a:hover svg'                                    => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'sub_button_open',
			[
				'label' => __( 'Open', 'the7mk2' ),
			]
		);

		$this->add_control(
			'sub_menu_icon_active_color',
			[
				'label'     => __( 'Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .vertical-sub-nav > li > .open-sub .next-level-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .vertical-sub-nav > li > .open-sub svg'                => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tab();

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! $this->get_available_menus() ) {
			return;
		}

		$settings = $this->get_active_settings();

		$sub_menu_icon = '';
		if ( $settings['selected_icon'] ) {
			ob_start();
			Icons_Manager::render_icon(
				$settings['selected_icon'],
				[ 'aria-hidden' => 'true', 'class' => 'open-button' ],
				'i'
			);
			$sub_menu_icon = ob_get_clean();
		}

		$this->add_render_attribute(
			[
				'main-menu' => [
					'role' => 'navigation',
				],
			]
		);

		$this->add_render_attribute(
			'main-menu',
			'class',
			[
				'dt-nav-menu--main',
				'dt-nav-menu__container',
				'dt-sub-menu-display-' . $settings['submenu_display'],
			]
		);

		$clickable_item_parent = $settings['submenu_display'] !== 'on_item_click';

		do_action( 'presscore_primary_nav_menu_before' );

		presscore_nav_menu(
			array(
				'menu'                => $settings['menu'],
				'items_wrap'          => '<nav ' . $this->get_render_attribute_string( 'main-menu' ) . '><ul class="dt-nav-menu"' . '">%3$s</ul></nav>',
				'submenu_class'       => implode( ' ', presscore_get_primary_submenu_class( 'vertical-sub-nav' ) ),
				'link_before'         => '<span class="item-content">',
				'link_after'          => '</span><span class="next-level-button ' . esc_attr( $settings['icon_align'] ) . '" data-icon = "' . $settings['selected_active_icon']['value'] . '">' . $sub_menu_icon . '</span>',
				'parent_is_clickable' => $clickable_item_parent,
			)
		);

		do_action( 'presscore_primary_nav_menu_after' );
	}

	public function render_plain_content() {
	}
}
