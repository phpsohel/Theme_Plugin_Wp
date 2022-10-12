<?php
/**
 * The7 elements scroller widget for Elementor.
 *
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Controls_Stack;
use The7_Query_Builder;
use The7\Adapters\Elementor\The7_Elementor_Widget_Base;
use Elementor\Core\Responsive\Responsive;
use The7\Adapters\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class The7_Elementor_Photo_Scroller_Widget extends The7_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve photo scroller widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'the7_photo-scroller';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve photo scroller widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Photo Scroller', 'the7mk2' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve photo scroller widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-slider-push the7-widget';
	}

	public function get_style_depends() {
		the7_register_style('dt-photo-scroller', PRESSCORE_THEME_URI . '/css/photo-scroller' );

		return [ 'dt-photo-scroller' ];
	}
	public function get_script_depends() {

		the7_register_script( 'dt-photo-scroller', PRESSCORE_THEME_URI . '/js/photo-scroller' );
		if ( Plugin::$instance->preview->is_preview_mode() ) {

			wp_register_script(
				'the7-photo-scroller-widget-preview',
				PRESSCORE_ADMIN_URI . '/assets/js/elementor/photo-scroller-widget-preview.js',
				[],
				THE7_VERSION,
				true
			);

			return [ 'the7-photo-scroller-widget-preview', 'dt-photo-scroller' ];

		}

		return ['dt-photo-scroller'];
	}
	/**
	 * Register photo scroller widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		

		$this->start_controls_section(
			'section_photo_scroller_img',
			[
				'label' => __( 'Images', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);

		$this->add_control(
			'scroller',
			[
				'label' => __( 'Add Images', 'the7mk2' ),
				'type' => Controls_Manager::GALLERY,
				'default' => [],
				'show_label' => false,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'more_options',
			[
				'label' => __( 'Landscape images behavior', 'the7mk2' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_responsive_control(
			'dk_ls_images_view',
			[
				'label' => __( 'Filling mode:', 'the7mk2' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'fit' => [
						'title' => __( 'Contain', 'plugin-domain' ),
						'icon' => 'eicon-frame-minimize',
					],
					'fill' => [
						'title' => __( 'Cover', 'plugin-domain' ),
						'icon' => 'eicon-frame-expand',
					],
				],
				//'devices' => [ 'desktop', 'mobile' ],
				'default' => 'fill',
				'tablet_default' => 'fit',
				'mobile_default' => 'fit',
				'toggle' => false,
				'show_label' => true,
			]
		);
		$this->add_responsive_control(
			'ls_max_width',
			[
				'label' => __( 'Max width (%)', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '',
				],
				'show_label' => true,
			]
		);
		$this->add_responsive_control(
			'ls_min_width',
			[
				'label' => __( 'Min width (%):', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '',
				],
				'show_label' => true,
				'separator' => 'after',
			]
		);
		
		$this->add_control(
			'p_title_options',
			[
				'label' => __( 'Portrait images behavior', 'the7mk2' ),
				'type' => Controls_Manager::HEADING,
			]
		);
		
		$this->add_responsive_control(
			'dk_pt_images_view',
			[
				'label' => __( 'Filling mode:', 'the7mk2' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'fit' => [
						'title' => __( 'Contain', 'plugin-domain' ),
						'icon' => 'eicon-frame-minimize',
					],
					'fill' => [
						'title' => __( 'Cover', 'plugin-domain' ),
						'icon' => 'eicon-frame-expand',
					],
				],
				'default' => 'fit',
				'tablet_default' => 'fit',
				'mobile_default' => 'fit',
				//'devices' => [ 'desktop', 'mobile' ],
				'toggle' => false,
				'show_label' => true,
			]
		);
		

		$this->add_responsive_control(
			'pt_max_width',
			[
				'label' => __( 'Max width  (%)', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '',
				],
				'show_label' => true,
			]
		);
		$this->add_responsive_control(
			'pt_min_width',
			[
				'label' => __( 'Min width (%):', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '',
				],
				'show_label' => true,
				'separator' => 'after',
			]
		);
		
		$this->add_control(
			'view',
			[
				'label' => __( 'View', 'the7mk2' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'section_photo_scroller',
			[
				'label' => __( 'Scroller', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);

		$this->add_responsive_control(
			'_element_height',
			[
				'label' => __( 'Height', 'the7mk2' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'initial',
				'tablet_default' => 'initial',
				'mobile_default' => 'initial',
				'options' => [
					'inherit' => __( 'Full Height', 'the7mk2' ) . ' (100%)',
					'initial' => __( 'Custom', 'the7mk2' ),
				],
				'selectors_dictionary' => [
					'inherit' => '100%',
				],
				//'prefix_class' => 'elementor-widget%s__width-',
				
			]
		);

		$this->add_responsive_control(
			'photo_scroller_height',
			[
				'label' => __( 'Custom Height', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1000,
						'step' => 1,
					],
				],
				'default' => [
					'size' => '',
				],
				'condition' => [
					'_element_height' => 'initial',
				],
				'device_args' => [
					Controls_Stack::RESPONSIVE_TABLET => [
						'condition' => [
							'_element_height_tablet' => [ 'initial' ],
						],
					],
					Controls_Stack::RESPONSIVE_MOBILE => [
						'condition' => [
							'_element_height_mobile' => [ 'initial' ],
						],
					],
				],
				'size_units' => [ 'px' ],
			]
		);
	

		$this->end_controls_section();

		$this->start_controls_section(
			'section_thumbs_options',
			[
				'label' => __( 'Navigation', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);
			$this->add_control(
				'arrows',
				[
					'label' => __( 'Arrows visibility', 'the7mk2' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'the7mk2' ),
					'label_off' => __( 'Hide', 'the7mk2' ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_control(
				'thumbnails',
				[
					'label' => __( 'Thumbnails visibility', 'the7mk2' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', 'the7mk2' ),
					'label_off' => __( 'Hide', 'the7mk2' ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);
			$this->add_control(
				'thumb_position',
				[
					'label' => __( 'Position', 'the7mk2' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'inside-hidden',
					'options' => [
						'inside' => __( 'On the scroller, fixed', 'the7mk2' ),
						'inside-visible' => __( 'On the scroller, visible by default', 'the7mk2' ),
						'inside-hidden' => __( 'On the scroller, hidden by default', 'the7mk2' ),
						'outside' => __( 'Below the scroller', 'the7mk2' ),
					],
					'condition' => [
						'thumbnails' => [ 'yes'],
					],
					
				]
			);
			$this->add_control(
				'thumb_width',
				[
					'label' => __( 'Thumbnails width', 'the7mk2' ),
					'type' => Controls_Manager::SLIDER,
					'default' => [
						'size' => '',
					],
					'range' => [
						'px' => [
							'min' => 2,
							'max' => 400,
							'step' => 2,
						],
					],

					'condition' => [
						'thumbnails' => [ 'yes'],
					],
				]
			);
			$this->add_control(
				'thumb_height',
				[
					'label' => __( 'Thumbnails height', 'the7mk2' ),
					'type' => Controls_Manager::SLIDER,
					'default' => [
						'size' => '',
					],
					'range' => [
						'px' => [
							'min' => 2,
							'max' => 400,
							'step' => 2,
						],
					],
					'condition' => [
						'thumbnails' => [ 'yes'],
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_options',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);
		$this->add_control(
			'overlay_title',
			[
				'label' => __( 'Title', 'the7mk2' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', 'the7mk2' ),
					'title' => __( 'Title', 'the7mk2' ),
					'caption' => __( 'Caption', 'the7mk2' ),
					'alt' => __( 'Alt', 'the7mk2' ),
					'description' => __( 'Description', 'the7mk2' ),
				],
			]
		);

		$this->add_control(
			'overlay_description',
			[
				'label' => __( 'Description', 'the7mk2' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', 'the7mk2' ),
					'title' => __( 'Title', 'the7mk2' ),
					'caption' => __( 'Caption', 'the7mk2' ),
					'alt' => __( 'Alt', 'the7mk2' ),
					'description' => __( 'Description', 'the7mk2' ),
				],
			]
		);
		$this->add_control(
			'inactive_content',
			[
				'label' => __( 'Inactive images content', 'the7mk2' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'the7mk2' ),
				'label_off' => __( 'Hide', 'the7mk2' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_additional_options',
			[
				'label' => __( 'Additional Options', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'the7mk2' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'the7mk2' ),
					'no' => __( 'No', 'the7mk2' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'the7mk2' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '',
				'condition' => [
					'autoplay' => 'yes',
				],
				'frontend_available' => true,
			]
		);
		$this->add_control(
				'autoplay_on_hover',
				[
					'label' => __( 'Stop on hover', 'the7mk2' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'the7mk2' ),
					'label_off' => __( 'No', 'the7mk2' ),
					'return_value' => 'yes',
					'default' => 'yes',
					'condition' => [
						'autoplay' => 'yes',
					],
				]
			);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_photo_images_style',
			[
				'label' => __( 'Images', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

	
		$this->add_responsive_control(
			'scroller_padding',
			[
				'label'      => __( 'Image paddings', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px'],
				'default'    => [
					'top'      => '',
					'right'     => '',
					'bottom'   => '',
					'left'     => '',
					'unit'     => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_control(
			'overlay',
			[
				'label' => __( 'Pixel overlay', 'the7mk2' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'the7mk2' ),
				'label_off' => __( 'Hide', 'the7mk2' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);
		$this->add_control(
			'image_opacity',
			[
				'label' => __( 'Inactive image opacity (%)', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => '',
				],
			]
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'navigation_style',
			[
				'label' => __( 'Navigation & Thumbnails', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'thumbnails' => [ 'yes'],
				],
			]
		);
		$this->add_control(
			'thumb_bg_color',
			[
				'label'       => __( 'Thumbnails background', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .scroller-thumbnails' => 'background: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'contr_bg_color',
			[
				'label'       => __( 'Controls background', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .btn-cntr a' => 'background: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'contrl_icon_color',
			[
				'label'       => __( 'Controls icon color', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .btn-cntr a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'arrows_style',
			[
				'label' => __( 'Arrows', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);
		$this->add_control(
			'arrows_heading',
			[
				'label'     => __( 'Arrow Icon', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'next_icon',
			[
				'label'     => __( 'Choose next arrow icon', 'the7mk2' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'icomoon-the7-font-the7-arrow-09',
					'library' => 'the7-icons',
				],
				'classes'   => [ 'elementor-control-icons-svg-uploader-hidden' ],
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'prev_icon',
			[
				'label'     => __( 'Choose previous arrow icon', 'the7mk2' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'icomoon-the7-font-the7-arrow-08',
					'library' => 'the7-icons',
				],
				'classes'   => [ 'elementor-control-icons-svg-uploader-hidden' ],
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_icon_size',
			[
				'label'      => __( 'Arrow icon size', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .scroller-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrows_background_heading',
			[
				'label'     => __( 'Arrow Background', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_bg_width',
			[
				'label'      => __( 'Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .scroller-arrow' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_bg_height',
			[
				'label'      => __( 'Height', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],

				'selectors' => [
					'{{WRAPPER}} .scroller-arrow' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_border_radius',
			[
				'label'      => __( 'Arrow border radius', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],

				'selectors' => [
					'{{WRAPPER}} .scroller-arrow' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .scroller-arrow:before' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .scroller-arrow:after' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_border_width',
			[
				'label'      => __( 'Arrow border width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 25,
						'step' => 1,
					],
				],

				'selectors' => [
					'{{WRAPPER}} .dt-arrow-border-on .scroller-arrow:before' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
					'{{WRAPPER}} .dt-arrow-hover-border-on .scroller-arrow:after' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
				],
				'condition'  => [
					'arrows' => [ 'yes'],
				],
			]
		);
		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);
		$this->add_control(
			'arrow_icon_color',
			[
				'label'       => __( 'Arrow icon color', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .scroller-arrow:not(:hover) span' => 'color: {{VALUE}}; background: none;',
				],
				'condition'   => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_icon_border',
			[
				'label'        => __( 'Show arrow border color', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => 'n',
				'condition'    => [
					'arrows' => ['yes'],
				],
			]
		);

		$this->add_control(
			'arrow_border_color',
			[
				'label'       => __( 'Arrow border color', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .dt-arrow-border-on .scroller-arrow:before' => 'border-color: {{VALUE}};',
				],
				'condition'   => [
					'arrow_icon_border' => 'y',
					'arrows'            => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrows_bg_show',
			[
				'label'        => __( 'Show arrow background', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => 'n',
				'condition'    => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label'       => __( 'Arrow background color', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .arrows-bg-on .scroller-arrow:before' => 'background: {{VALUE}};',
				],
				'condition'   => [
					'arrows_bg_show' => 'y',
					'arrows'         => [ 'yes'],
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
			'arrows_hover_color_heading',
			[
				'label'     => __( 'Hover Color Setting', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_icon_color_hover',
			[
				'label'       => __( 'Arrow icon color hover', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors' => [
					'{{WRAPPER}} .scroller-arrow:hover span' => 'color: {{VALUE}}; background: none;',
				],
				'condition'   => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_icon_border_hover',
			[
				'label'        => __( 'Show arrow border color hover', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => 'n',
				'condition'    => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_border_color_hover',
			[
				'label'       => __( 'Arrow border color hover', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .dt-arrow-hover-border-on .scroller-arrow:after' => 'border-color: {{VALUE}};',
				],
				'condition'   => [
					'arrow_icon_border_hover' => 'y',
					'arrows'                  => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrows_bg_hover_show',
			[
				'label'        => __( 'Show arrow background hover', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => 'n',
				'condition'    => [
					'arrows' => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'arrow_bg_color_hover',
			[
				'label'       => __( 'Arrow background hover color', 'the7mk2' ),
				'description' => __( 'Live empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .arrows-hover-bg-on .scroller-arrow:after' => 'background: {{VALUE}};',
				],
				'condition'   => [
					'arrows_bg_hover_show' => 'y',
					'arrows'               => [ 'yes'],
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();
		
		$this->add_control(
		    'right_arrow_position_heading',
		    [
		        'label' => __( 'Right Arrow Position', 'the7mk2' ),
		        'type' => Controls_Manager::HEADING,
		        'separator' => 'before',
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrow_icon_paddings',
		    [
		        'label'      => __( 'Icon paddings', 'the7mk2' ),
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
					'{{WRAPPER}} .scroller-arrow.next span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrow_v_position',
		    [
		        'label'   => __( 'Vertical position', 'the7mk2' ),
		        'type'    => Controls_Manager::SELECT,
		        'default' => 'center',
		        'options' => [
					'top' => 'Top',
					'center' => 'Center',
					 'bottom' => 'Bottom',
		        ],
		       
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrow_h_position',
		    [
		        'label'   => __( 'Horizontal position', 'the7mk2' ),
		        'type'    => Controls_Manager::SELECT,
		        'default' => 'right',
		        'options' => [
					"left" => "Left",
					"center" => "Center",
					"right" => "Right",
		        ],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrow_v_offset',
		    [
		        'label' => __( 'Vertical offset', 'the7mk2' ),
		        'type' => Controls_Manager::SLIDER,
		        'default'    => [
		            'unit' => 'px',
		            'size' => '',
		        ],
		        'size_units' => [ 'px' ],
		        'range'      => [
		            'px' => [
		                'min'  => -1000,
		                'max'  => 1000,
		                'step' => 1,
		            ],
		        ],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrow_h_offset',
		    [
		        'label' => __( 'Horizontal offset', 'the7mk2' ),
		        'type' => Controls_Manager::SLIDER,
		        'default'    => [
		            'unit' => 'px',
		            'size' => '',
		        ],
		        'size_units' => [ 'px' ],
		        'range'      => [
		            'px' => [
		                'min'  => -1000,
		                'max'  => 1000,
		                'step' => 1,
		            ],
		        ],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'left_arrow_position_heading',
		    [
		        'label' => __( 'Left Arrow Position', 'the7mk2' ),
		        'type' => Controls_Manager::HEADING,
		        'separator' => 'before',
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'l_arrow_icon_paddings',
		    [
		        'label'      => __( 'Icon paddings', 'the7mk2' ),
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
					'{{WRAPPER}} .scroller-arrow.prev span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
			'l_arrow_v_position',
			[
				'label'   => __( 'Vertical position', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'top' => 'Top',
					'center' => 'Center',
					'bottom' => 'Bottom',
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'l_arrow_h_position',
			[
				'label'   => __( 'Horizontal position', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					"left" => "Left",
					"center" => "Center",
					"right" => "Right",
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'l_arrow_v_offset',
			[
				'label' => __( 'Vertical offset', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
			]
		);

		$this->add_control(
			'l_arrow_h_offset',
			[
				'label' => __( 'Horizontal offset', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => -1000,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
			]
		);

		$this->add_control(
		    'arrows_responsiveness_heading',
		    [
		        'label' => __( 'Arrows responsiveness', 'the7mk2' ),
		        'type' => Controls_Manager::HEADING,
		        'separator' => 'before',
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'arrow_responsiveness',
		    [
		        'label'   => __( 'Responsive behaviour', 'the7mk2' ),
		        'type'    => Controls_Manager::SELECT,
		        'default' => 'no-changes',
		        'options' => [
					 'reposition-arrows' => 'Reposition arrows',
					 'no-changes' => 'Leave as is',
					 'hide-arrows' => 'Hide arrows',
		        ],
				'condition'   => [
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'hide_arrows_mobile_switch_width',
		    [
		        'label' => __( 'Hide arrows if browser width is less then', 'the7mk2' ),
		        'type' => Controls_Manager::NUMBER,
		        'default' => '',
				'condition'   => [
					'arrow_responsiveness' => 'hide-arrows',
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'reposition_arrows_mobile_switch_width',
		    [
		        'label' => __( 'Reposition arrows after browser width', 'the7mk2' ),
		        'type' => Controls_Manager::NUMBER,
		        'default' => '',
				'condition'   => [
					'arrow_responsiveness' => 'reposition-arrows',
					'arrows'               => [ 'yes'],
				],
				
		    ]
		);

		$this->add_control(
		    'l_arrows_mobile_h_position',
		    [
		        'label' => __( 'Left arrow horizontal offset', 'the7mk2' ),
		        'type' => Controls_Manager::SLIDER,
		        'default'    => [
		            'unit' => 'px',
		            'size' => '',
		        ],
		        'size_units' => [ 'px' ],
		        'range'      => [
		            'px' => [
		                'min'  => -10000,
		                'max'  => 10000,
		                'step' => 1,
		            ],
		        ],
				'condition'   => [
					'arrow_responsiveness' => 'reposition-arrows',
					'arrows'               => [ 'yes'],
				],
		    ]
		);

		$this->add_control(
		    'r_arrows_mobile_h_position',
		    [
		        'label' => __( 'Right arrow horizontal offset', 'the7mk2' ),
		        'type' => Controls_Manager::SLIDER,
		        'default'    => [
		            'unit' => 'px',
		            'size' => '',
		        ],
		        'size_units' => [ 'px' ],
		        'range'      => [
		            'px' => [
		                'min'  => -10000,
		                'max'  => 10000,
		                'step' => 1,
		            ],
		        ],
				'condition'   => [
					'arrow_responsiveness' => 'reposition-arrows',
					'arrows'               => [ 'yes'],
				],
		    ]
		);
		$this->end_controls_section();


		$this->start_controls_section(
			'overlay_content_style',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'conditions'   => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'overlay_title',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'name'     => 'overlay_description',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
				
				//TODO: add conditions for this section
			]
		);

		$this->add_control(
			'content_alignment',
			[
				'label' => __( 'Alignment', 'the7mk2' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'toggle' => false,
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .album-content-description' => 'text-align: {{VALUE}}; left: 0;
					margin: 0; height: 100%;',
				],
			]
		);

		$this->add_control(
			'content_vertical_position',
			[
				'label' => __( 'Vertical Position', 'the7mk2' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'top' => [
						'title' => __( 'Top', 'the7mk2' ),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __( 'Middle', 'the7mk2' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'the7mk2' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'toggle' => false,
				'default' => 'middle',
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .album-content-description' => 'display: inline-flex; flex-flow:column wrap; justify-content: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'content_horizontal_position',
			[
				'label' => __( 'Horizontal Position', 'the7mk2' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon' => 'eicon-v-align-middle',
					],
					'right' => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'center',
				'selectors_dictionary' => [
					'left' => 'margin-right: auto',
					'center' => 'left: 50%; transform: translate3d(-50%, 0, 0);',
					'right' => 'left: auto; right: 0;',
				],
				'toggle' => false,
				'selectors' => [
					'{{WRAPPER}} .album-content-description' => '{{VALUE}};',
				],
			]
		);
		$this->add_control(
			'content_bg_color',
			[
				'label'       => __( 'Background color', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',

				'selectors' => [
					'{{WRAPPER}} .album-content-description .content-description-inner' => 'background: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'_content_width',
			[
				'label' => __( 'Content width', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'range' => [
					'px' => [
						'min' => 40,
						'max' => 2000,
						'step' => 1,
					],
				],
				'default' => [
					'size' => '',
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .album-content-description' => 'width: {{SIZE}}{{UNIT}};',
				],
				'show_label' => true,
			]
		);
		$this->add_responsive_control(
		    'content_padding',
		    [
		        'label'      => __( 'Content paddings', 'the7mk2' ),
		        'type'       => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'default'    => [
		            'top'      => '',
		            'right'    => '',
		            'bottom'   => '',
		            'left'     => '',
		            'unit'     => 'px',
		            'isLinked' => true,
		        ],
				'selectors' => [
					'{{WRAPPER}} .content-description-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'conditions'   => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'overlay_title',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'name'     => 'overlay_description',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
		    ]
		);

		$this->add_control(
			'scroller_heading_title',
			[
				'label' => __( 'Title', 'the7mk2' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'overlay_title!' => '',
				],
			]
		);

		$this->add_control(
			'scroller_title_color',
			[
				'label' => __( 'Color', 'the7mk2' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .album-content-description .entry-title' => 'color: {{VALUE}}; margin: 0',
				],
				'default'     => '',

				'condition' => [
					'overlay_title!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
			//	'scheme' => Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .album-content-description .entry-title',
				'condition' => [
					'overlay_title!' => '',
				],
			]
		);

		$this->add_control(
			'title_spacing',
			[
				'label' => __( 'Spacing', 'the7mk2' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .album-content-description .entry-title + p' => 'margin: {{SIZE}}{{UNIT}} 0 0 0',
				],

				'condition' => [
					'overlay_title!' => '',
				],
			]
		);

		$this->add_control(
			'heading_description',
			[
				'label' => __( 'Description', 'the7mk2' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'overlay_description!' => '',
				],
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => __( 'Color', 'the7mk2' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .album-content-description p' => 'color: {{VALUE}}',
				],
				'default'     => '',
				'condition' => [
					'overlay_description!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				//'scheme' => Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .album-content-description p',
				'condition' => [
					'overlay_description!' => '',
				],
			]
		);

		$this->end_controls_section(); // overlay_content

	}

	/**
	 * Render photo scroller widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->print_inline_css();

		if ( empty( $settings['scroller'] ) ) {
			return;
		}

		$slides = [];

		$has_description = ! empty( $settings['overlay_description'] );

		$has_title = ! empty( $settings['overlay_title'] );

		foreach ( $settings['scroller'] as $index => $attachment ) {
			$image_url = wp_get_attachment_image_src( $attachment['id'], 'full' );
			$thumb_args = [
				'img_id' => $attachment['id'],
				'class'  => 'post-thumbnail-rollover',
				'href'   => '',
				'custom' => ' aria-label="' . esc_attr__( 'Post image', 'the7mk2' ) . '"',
				//'wrap'   => '<a %HREF% %CLASS% target="' . $target . '" %CUSTOM%><img %IMG_CLASS% %SRC% %ALT% %IMG_TITLE% %SIZE% /></a>',
				'echo'   => false,
			];
			if ( presscore_lazy_loading_enabled() ) {
				$thumb_args['lazy_loading'] = true;
			}
			$post_media = dt_get_thumb_img( $thumb_args );


			$link_tag = '';

			$link = $this->get_link_url( $attachment, $settings );

			if ( $link ) {
				$link_key = 'link_' . $index;

				if ( Plugin::$instance->editor->is_edit_mode() ) {
					$this->add_render_attribute( $link_key, [
						'class' => 'elementor-clickable',
					] );
				}

				$this->add_link_attributes( $link_key, $link );

				$link_tag = '<a ' . $this->get_render_attribute_string( $link_key ) . '>';
			}
			$desc_html = '';
			$attachment_img = dt_get_attachment( $attachment['id'] );
			$title_type = $settings['overlay_title'];
			$description_type = $settings['overlay_description'];
			$image_data = [
				'caption' => wp_get_attachment_caption($attachment['id']),
				'description' => $attachment_img['description'],
				'title' => $attachment_img['title'],
				'alt' => $attachment_img['alt'],
			];
			$title = array_key_exists( $title_type, $image_data ) ? $image_data[ $title_type ] : '';
			$description = array_key_exists( $description_type, $image_data ) ? $image_data[ $description_type ] : '';
			if ( $title || $description ) {
						// $image_data = [
						// 	'caption' => wp_get_attachment_caption($attachment['id']),
						// 	'description' => $attachment_img['description'],
						// 	'title' => $attachment_img['title'],
						// 	'alt' => $attachment_img['alt'],
						// ];
				if(! empty( $image_data['caption'] ) || ! empty( $image_data['description'] ) || ! empty($image_data['title'] ) || ! empty( $image_data['alt'] )){
					$desc_html = '<div class="album-content-description" ><div class="content-description-inner">';
							if ( $has_title ) {
								$title = $image_data[ $settings['overlay_title'] ];

								if ( ! empty( $title ) ) {
								$desc_html .= '<h4 class="entry-title" >' .  $title . '</h3>';
								}

							}
							if ( $has_description ) {
								$description = $image_data[ $settings['overlay_description'] ];
								if ( ! empty( $description ) ) {
								$desc_html .= '<p class="text-small">' . $description . '</p>';
								}
							}

					$desc_html .= '</div></div>';
				}
			}

			$slide_html = '<figure data-width="' . esc_attr( $image_url[1] ) . '" data-height="' . esc_attr( $image_url[2] ) . '"><a href=" ' . esc_attr ($image_url[0]) . ' "'  . $post_media;
			$slide_html .= '</a>' . $desc_html . '</figure>';


			$slides[] = $slide_html;

		}

		if ( empty( $slides ) ) {
			return;
		}
		$data = [];
		
		$this->add_render_attribute( [
			'scroller' => [
				'class' => 'photoSlider',
			],
			'scroller-wrapper' => [
				'class' => 'photo-scroller ' . $this->get_unique_class() .'',
				'data-scale' => $settings['dk_ls_images_view'],
				'data-autoslide' => ( $settings['autoplay'] == 'yes' ? 'true' : 'false' ),
				'data-play-on-hover' =>( $settings['autoplay_on_hover'] == 'yes' ? 'true' : 'false' ),
				'data-delay'     => $settings['autoplay_speed'] !== '' ? absint( $settings['autoplay_speed'] ) : 5000,
				'data-show-thumbnails' => ( $settings['thumbnails'] == 'yes' ? 'true' : 'false' ),
				'data-thumb-position' =>  $settings['thumb_position'],

				'data-thumb-height' => $settings['thumb_height']['size'] !== '' ? absint( $settings['thumb_height']['size']) : 60,
				'data-thumb-width' => $settings['thumb_width']['size'] !== '' ? absint( $settings['thumb_width']['size']) : 60,

				'data-transparency' => $settings['image_opacity']['size'] !== '' ? absint($settings['image_opacity']['size'])/100 : 0.15,
				'data-arrows' => ( $settings['arrows'] == 'yes' ? 'true' : 'false' ),
				'data-next-icon'            => $settings['next_icon']['value'],
				'data-prev-icon'            => $settings['prev_icon']['value'],
				'data-r-arrow-v-position'   => $settings['r_arrow_v_position'] ,
				'data-r-arrow-h-position'   => $settings['r_arrow_h_position'],
				'data-l-arrow-v-position'   => $settings['l_arrow_v_position'] ,
				'data-l-arrow-h-position'   => $settings['l_arrow_h_position'] ,
				
				'data-ls-fill-dt'  => esc_attr( $settings['dk_ls_images_view'] ),
				'data-ls-fill-tablet' => esc_attr( $settings['dk_ls_images_view_tablet'] ),
				'data-ls-fill-mob' => esc_attr( $settings['dk_ls_images_view_mobile'] ),

				'data-pt-fill-dt'  => esc_attr( $settings['dk_pt_images_view']),
				'data-pt-fill-tablet' => esc_attr( $settings['dk_pt_images_view_tablet']),
				'data-pt-fill-mob' => esc_attr( $settings['dk_pt_images_view_mobile']),
			],
		] );

		$data['data-padding-top'] = $settings['scroller_padding']['top'] !== '' ? absint( $settings['scroller_padding']['top']) : 0;
		$data['data-padding-bottom'] = $settings['scroller_padding']['bottom'] !== '' ? absint( $settings['scroller_padding']['bottom']) : 0;
		$data['data-padding-side'] = $settings['scroller_padding']['right'] !== '' ? absint( $settings['scroller_padding']['right']) : 0;
		$data['data-padding-left'] = $settings['scroller_padding']['left'] !== '' ? absint( $settings['scroller_padding']['left']) : 0;

		$data['data-t-padding-top'] = $settings['scroller_padding_tablet']['top'] !== '' ? absint( $settings['scroller_padding_tablet']['top']) : $data['data-padding-top'];
		$data['data-t-padding-bottom'] = $settings['scroller_padding_tablet']['bottom'] !== '' ? absint( $settings['scroller_padding_tablet']['bottom']) : $data['data-padding-bottom'];
		$data['data-t-padding-side']   = $settings['scroller_padding_tablet']['right'] !== '' ? absint( $settings['scroller_padding_tablet']['right']) :  $data['data-padding-side'];
		$data['data-t-padding-left']   = $settings['scroller_padding_tablet']['left'] !== '' ? absint( $settings['scroller_padding_tablet']['left']) : $data['data-padding-left'];

		$data['data-m-padding-top'] = $settings['scroller_padding_mobile']['top'] !== '' ? absint( $settings['scroller_padding_mobile']['top']) : $data['data-t-padding-top'];
		$data['data-m-padding-bottom'] = $settings['scroller_padding_mobile']['bottom'] !== '' ? absint( $settings['scroller_padding_mobile']['bottom']) : $data['data-t-padding-bottom'];
		$data['data-m-padding-side'] = $settings['scroller_padding_mobile']['right'] !== '' ? absint( $settings['scroller_padding_mobile']['right']) : $data['data-t-padding-side'];
		$data['data-m-padding-left' ] = $settings['scroller_padding_mobile']['left'] !== '' ? absint( $settings['scroller_padding_mobile']['left']) : $data['data-t-padding-left'];

		$data['data-ls-max'] = $settings['ls_max_width']['size'] !== '' ? absint( $settings['ls_max_width']['size']) : 100;
		$data['data-t-ls-max'] = $settings['ls_max_width_tablet']['size'] !== '' ? absint( $settings['ls_max_width_tablet']['size']) : $data['data-ls-max'];
		$data['data-m-ls-max'] = $settings['ls_max_width_mobile']['size'] !== '' ? absint( $settings['ls_max_width_mobile']['size']) : $data['data-t-ls-max'];
		$data['data-ls-min'] =  $settings['ls_min_width']['size'] !== ''? absint( $settings['ls_min_width']['size'] ) : 0;
		$data['data-t-ls-min'] =  $settings['ls_min_width_tablet']['size'] !== ''? absint( $settings['ls_min_width_tablet']['size'] ) : $data['data-ls-min'];
		$data['data-m-ls-min'] =  $settings['ls_min_width_mobile']['size'] !== ''? absint( $settings['ls_min_width_mobile']['size'] ) : $data['data-t-ls-min'];
		$data['data-pt-max'] = $settings['pt_max_width']['size'] !== '' ? absint( $settings['pt_max_width']['size']) : 100;
		$data['data-t-pt-max'] = $settings['pt_max_width_tablet']['size'] !== '' ? absint( $settings['pt_max_width_tablet']['size']) : $data['data-pt-max'];
		$data['data-m-pt-max'] = $settings['pt_max_width_mobile']['size'] !== '' ? absint( $settings['pt_max_width_mobile']['size']) : $data['data-t-pt-max'];
		$data['data-pt-min'] = $settings['pt_min_width']['size'] !== '' ? absint( $settings['pt_min_width']['size'] ): 0;
		$data['data-t-pt-min'] = $settings['pt_min_width_tablet']['size'] !== '' ? absint( $settings['pt_min_width_tablet']['size'] ): $data['data-pt-min'];
		$data['data-m-pt-min'] = $settings['pt_min_width_mobile']['size'] !== '' ? absint( $settings['pt_min_width_mobile']['size'] ): $data['data-t-pt-min'];

		$this->add_render_attribute('scroller-wrapper',  $data );

		$desktop = $settings['photo_scroller_height']['size'] !== '' ? absint($settings['photo_scroller_height']['size']): 300;
		$tablet = $settings['photo_scroller_height_tablet']['size'] !== '' ? absint($settings['photo_scroller_height_tablet']['size']): $desktop;
		$mobile = $settings['photo_scroller_height_mobile']['size'] !== '' ? absint($settings['photo_scroller_height_mobile']['size']): $tablet;
		
		if ( 'initial' == $settings['_element_height'] ) {
			$this->add_render_attribute( 'scroller-wrapper', 'data-height', $desktop );
		}
		if (  'initial' == $settings['_element_height_tablet']) {
			$this->add_render_attribute( 'scroller-wrapper', 'data-tablet-height', $tablet );
		}
		if (  'initial' == $settings['_element_height_mobile']) {
			$this->add_render_attribute( 'scroller-wrapper', 'data-mobile-height', $mobile );
		}
		if ( 'yes' === $settings['overlay'] ) {
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'show-overlay' );
		}
		if ( 'yes' != $settings['inactive_content'] ) {
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'hide-inactive-content' );
		}

		if ('inside-hidden' === $settings['thumb_position'] ) {
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'hide-thumbs' );
		}
		if ( 'y' === $settings['arrow_icon_border'] ) {

			$this->add_render_attribute( 'scroller-wrapper', 'class', 'dt-arrow-border-on' );
		}
		if ( 'y' === $settings['arrow_icon_border_hover'] ) {
			
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'dt-arrow-hover-border-on' );
		}
		if ( 'y' === $settings['arrows_bg_show'] ) {

			$this->add_render_attribute( 'scroller-wrapper', 'class', 'arrows-bg-on' );
		}
		if ( 'y' === $settings['arrows_bg_hover_show'] ) {
			
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'arrows-hover-bg-on' );
		}

		if ( 'reposition-arrows' === $settings['arrow_responsiveness'] ) {
			
			$this->add_render_attribute( 'scroller-wrapper', 'class', 'reposition-arrows' );
		}
		

		$slides_count = count( $settings['scroller'] );
	

			?>
		<div <?php echo $this->get_render_attribute_string( 'scroller-wrapper' ); ?>>
			<div <?php echo $this->get_render_attribute_string( 'scroller' ); ?>>
				<?php echo implode( '', $slides ); ?>
			</div>
			<div class="btn-cntr">
				<a href="#" class="hide-thumb-btn"></a>
				<a href="#" class="auto-play-btn"></a>
				<a href="#" class="full-screen-btn"></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve photo scroller link URL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $attachment
	 * @param object $instance
	 *
	 * @return array|string|false An array/string containing the attachment URL, or false if no link.
	 */
	private function get_link_url( $attachment, $instance ) {

		return [
			'url' => wp_get_attachment_url( $attachment['id'] ),
		];
	}

	/**
	 * Return shortcode less file absolute path to output inline.
	 *
	 * @return string
	 */
	protected function get_less_file_name() {
		return PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor/the7-photo-scroller-widget.less';
	}

	/**
	 * Specify a vars to be inserted in to a less file.
	 */
	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		// For project icon style, see `selectors` in settings declaration.

		$settings = $this->get_settings_for_display();

		$less_vars->add_keyword(
			'unique-shortcode-class-name',
			$this->get_unique_class() . '.photo-scroller',
			'~"%s"'
		);
		foreach ( Responsive::get_breakpoints() as $size => $value ) {
			$less_vars->add_pixel_number( "elementor-{$size}-breakpoint", $value );
		}

		$less_vars->add_keyword( 'arrow-right-v-position', $settings['r_arrow_v_position'] );
		$less_vars->add_keyword( 'arrow-right-h-position', $settings['r_arrow_h_position'] );

		$less_vars->add_pixel_number( 'r-arrow-v-position', $settings['r_arrow_v_offset']['size'] !== '' ? $settings['r_arrow_v_offset'] : 0 );
		$less_vars->add_pixel_number( 'r-arrow-h-position', $settings['r_arrow_h_offset']['size'] !== '' ? $settings['r_arrow_h_offset'] : 10 );

		$less_vars->add_keyword( 'arrow-left-v-position', $settings['l_arrow_v_position'] );
		$less_vars->add_keyword( 'arrow-left-h-position', $settings['l_arrow_h_position'] );
	//	$less_vars->add_pixel_number( 'l-arrow-v-position', $settings['l_arrow_v_offset'] );

		$less_vars->add_pixel_number( 'l-arrow-h-position', $settings['l_arrow_h_offset']['size'] !== '' ? $settings['l_arrow_h_offset'] : 10 );
		$less_vars->add_pixel_number( 'l-arrow-v-position', $settings['l_arrow_v_offset']['size'] !== '' ? $settings['l_arrow_v_offset'] : 0 );

		$less_vars->add_pixel_number( 'hide-arrows-switch', $settings['hide_arrows_mobile_switch_width'] !== '' ? $settings['hide_arrows_mobile_switch_width'] : 778 );
		$less_vars->add_pixel_number( 'reposition-arrows-switch', $settings['reposition_arrows_mobile_switch_width'] !== '' ? $settings['reposition_arrows_mobile_switch_width'] : 778 );
		$less_vars->add_pixel_number( 'arrow-left-h-position-mobile', $settings['l_arrows_mobile_h_position']['size'] !== '' ? $settings['l_arrows_mobile_h_position'] : 10 );
		$less_vars->add_pixel_number( 'arrow-right-h-position-mobile', $settings['r_arrows_mobile_h_position']['size'] !== '' ? $settings['r_arrows_mobile_h_position'] : 10 );
	}

}
