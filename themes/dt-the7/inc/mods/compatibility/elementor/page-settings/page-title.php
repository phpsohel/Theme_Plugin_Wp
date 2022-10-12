<?php
/**
 * @package The7
 */

namespace The7\Adapters\Elementor\Page_Settings;

use Elementor\Controls_Manager;
use Elementor\Modules\PageTemplates\Module as PageTemplatesModule;
use LS_Sliders;
use The7_Elementor_Compatibility;

defined( 'ABSPATH' ) || exit;

$template_option_name = The7_Elementor_Compatibility::instance()->page_settings->template_option_name;
$template_condition = [ PageTemplatesModule::TEMPLATE_CANVAS ];

$rev_sliders = $layer_sliders = array( 'none' => __( 'none', 'the7mk2' ) );
$slideshow_mode_options = array();

if ( class_exists( 'RevSlider' ) ) {

	$rev = new \RevSlider();

	$arrSliders = $rev->getArrSliders();
	foreach ( (array) $arrSliders as $revSlider ) {
		$rev_sliders[ $revSlider->getAlias() ] = $revSlider->getTitle();
	}

	$slideshow_mode_options['revolution'] = array( __( 'Slider Revolution', 'the7mk2' ) );
}

if ( function_exists('lsSliders') || class_exists('LS_Sliders') ) {
	if ( class_exists('LS_Sliders') ) {
		$layerSliders = LS_Sliders::find( array( 'limit' => 9999, 'orderby' => 'date_m' ) );
	}
	else {
		$layerSliders = lsSliders( 9999 );
	}

	foreach ( $layerSliders as $lSlide ) {

		$layer_sliders[ $lSlide['id'] ] = $lSlide['name'];
	}

	$slideshow_mode_options['layer'] = array( __( 'LayerSlider', 'the7mk2' ) );
}
reset( $slideshow_mode_options );

$title_options = [
	'enabled'  => __( 'Show page title', 'the7mk2' ),
	'disabled' => __( 'Hide page title', 'the7mk2' ),
];
if ( ! empty( $slideshow_mode_options ) ) {
	$title_options['slideshow'] = __( 'Slideshow', 'the7mk2' );
}

return [
	'args'     => [
		'label'      => __( 'Page Header Settings', 'the7mk2' ),
		'tab'        => Controls_Manager::TAB_SETTINGS,
		'conditions' => [
			'relation' => 'or',
			'terms'    => [
				[
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'the7_template_applied',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'name'     => $template_option_name,
							'operator' => '!in',
							'value'    => array_merge( [ 'default' ], $template_condition ),
						],
					],
				],
				[
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => $template_option_name,
							'operator' => '!in',
							'value'    => $template_condition,
						],
						[
							'name'     => 'the7_template_applied',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			],
		],
	],
	'controls' => [
		'the7_document_title'                  => [
			'meta' => '_dt_header_title',
			'args' => [
				'label'     => __( 'Page title', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'enabled',
				'options'   => $title_options,
				'separator' => 'none',
			],
		],
		//slider options
		'the7_document_slideshow_mode_heading' => [
			'args' => [
				'label'     => __( 'Slideshow options', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'the7_document_title' => 'slideshow',
				],
			],
		],
		'the7_document_slideshow_mode'         => [
			'meta' => '_dt_slideshow_mode',
			'args' => [
				'label'     => __( 'Slideshow type', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => key( $slideshow_mode_options ),
				'options'   => $slideshow_mode_options,
				'condition' => [
					'the7_document_title' => 'slideshow',
				],
			],
		],

		'the7_document_slideshow_revolution_slider'          => [
			'meta' => '_dt_slideshow_revolution_slider',
			'args' => [
				'label'     => __( 'Choose slider', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => $rev_sliders,
				'condition' => [
					'the7_document_title'          => 'slideshow',
					'the7_document_slideshow_mode' => 'revolution',
				],
			],
		],
		'the7_document_slideshow_layer_slider'               => [
			'meta' => '_dt_slideshow_layer_slider',
			'args' => [
				'label'     => __( 'Choose slider', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => $layer_sliders,
				'condition' => [
					'the7_document_title'          => 'slideshow',
					'the7_document_slideshow_mode' => 'layer',
				],
			],
		],
		'the7_document_slideshow_layer_show_bg_and_paddings' => [
			'meta' => '_dt_header_layer_show_bg_and_paddings',
			'args' => [
				'label'        => __( 'Enable slideshow background and paddings', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'enabled',
				'default'      => '',
				'condition'    => [
					'the7_document_title'          => 'slideshow',
					'the7_document_slideshow_mode' => 'layer',
				],
			],
		],

		// Disabled page title.
		'the7_document_disabled_header_heading'              => [
			'args' => [
				'label'     => __( 'Header options', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'the7_document_title' => 'disabled',
				],
			],
		],

		'the7_document_disabled_header_style'            => [
			'meta' => '_dt_header_disabled_background',
			'args' => [
				'label'     => __( 'Header style', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => [
					'normal'      => __( 'Normal', 'the7mk2' ),
					'transparent' => __( 'Transparent', 'the7mk2' ),
				],
				'condition' => [
					'the7_document_title' => 'disabled',
				],
			],
		],
		'the7_document_disabled_header_color_scheme'     => [
			'meta' => '_dt_header_disabled_transparent_bg_color_scheme',
			'args' => [
				'label'     => __( 'Color scheme', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'from_options',
				'options'   => [
					'from_options' => __( 'From options', 'the7mk2' ),
					'light'        => __( 'Light', 'the7mk2' ),
				],
				'separator' => 'none',
				'condition' => [
					'the7_document_title'                 => 'disabled',
					'the7_document_disabled_header_style' => 'transparent',
				],
			],
		],
		'the7_document_disabled_header_top_bar_color'    => [
			'meta'      => [
				'color'   => '_dt_header_disabled_transparent_top_bar_bg_color',
				'opacity' => '_dt_header_disabled_transparent_top_bar_bg_opacity',
			],
			'on_save'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'update_alpha_color',
			],
			'on_read'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'get_alpha_color',
			],
			'on_change' => 'do_not_reload_page',
			'args'      => [
				'label'     => __( 'Top bar color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.25)',
				'separator' => 'none',
				'condition' => [
					'the7_document_title'                 => 'disabled',
					'the7_document_disabled_header_style' => 'transparent',
				],
			],
		],
		'the7_document_disabled_header_backgraund_color' => [
			'meta'      => [
				'color'   => '_dt_header_disabled_transparent_bg_color',
				'opacity' => '_dt_header_disabled_transparent_bg_opacity',
			],
			'on_save'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'update_alpha_color',
			],
			'on_read'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'get_alpha_color',
			],
			'on_change' => 'do_not_reload_page',
			'args'      => [
				'label'     => __( 'Transparent background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.5)',
				'separator' => 'none',
				'condition' => [
					'the7_document_title'                 => 'disabled',
					'the7_document_disabled_header_style' => 'transparent',
				],
			],
		],

		// Fancy titles and slideshow.
		'the7_document_header_heading'       => [
			'args' => [
				'label'     => __( 'Header options', 'the7mk2' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'the7_document_title' => [ 'slideshow' ],
				],
			],
		],

		'the7_document__background_below_slideshow' => [
			'meta' => '_dt_header_background_below_slideshow',
			'args' => [
				'label'        => __( 'Show header below slideshow', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'enabled',
				'default'      => '',
				'condition'    => [
					'the7_document_title' => 'slideshow',
				],
			],
		],


		'the7_document_fancy_header_style'            => [
			'meta' => '_dt_header_background',
			'args' => [
				'label'     => __( 'Header style', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => [
					'normal'      => __( 'Normal', 'the7mk2' ),
					'transparent' => __( 'Transparent', 'the7mk2' ),
				],
				'condition' => [
					'the7_document_title' => [ 'slideshow' ],
				],
			],
		],
		'the7_document_fancy_header_color_scheme'     => [
			'meta' => '_dt_header_transparent_bg_color_scheme',
			'args' => [
				'label'     => __( 'Color scheme', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'from_options',
				'options'   => [
					'from_options' => __( 'From options', 'the7mk2' ),
					'light'        => __( 'Light', 'the7mk2' ),
				],
				'separator' => 'none',
				'condition' => [
					'the7_document_title'              => [ 'slideshow' ],
					'the7_document_fancy_header_style' => 'transparent',
				],
			],
		],
		'the7_document_fancy_header_top_bar_color'    => [
			'meta'      => [
				'color'   => '_dt_header_transparent_top_bar_bg_color',
				'opacity' => '_dt_header_transparent_top_bar_bg_opacity',
			],
			'on_save'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'update_alpha_color',
			],
			'on_read'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'get_alpha_color',
			],
			'on_change' => 'do_not_reload_page',
			'args'      => [
				'label'     => __( 'Top bar color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.25)',
				'separator' => 'none',
				'condition' => [
					'the7_document_title'              => [ 'slideshow' ],
					'the7_document_fancy_header_style' => 'transparent',
				],
			],
		],
		'the7_document_fancy_header_backgraund_color' => [
			'meta'      => [
				'color'   => '_dt_header_transparent_bg_color',
				'opacity' => '_dt_header_transparent_bg_opacity',
			],
			'on_save'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'update_alpha_color',
			],
			'on_read'   => [
				\The7\Adapters\Elementor\Meta_Adapters\The7_Elementor_Color_Meta_Adapter::class,
				'get_alpha_color',
			],
			'on_change' => 'do_not_reload_page',
			'args'      => [
				'label'     => __( 'Transparent background color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.5)',
				'separator' => 'none',
				'condition' => [
					'the7_document_title'              => [ 'slideshow' ],
					'the7_document_fancy_header_style' => 'transparent',
				],
			],
		],
	],
];
