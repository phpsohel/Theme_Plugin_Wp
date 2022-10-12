<?php
/**
 * @package The7
 */

namespace The7\Adapters\Elementor\Upgrade\Widgets;

use The7\Adapters\Elementor\Upgrade\The7_Elementor_Widget_Migrations;

use A;
use The7_Less_Vars_Value_Color;

defined( 'ABSPATH' ) || exit;

class The7_Elementor_Masonry_Migrations extends The7_Elementor_Widget_Migrations {

	public static function get_widget_name() {
		return 'the7_elements';
	}

	public static function _8_9_0_migration( $element, $args ) {
		if ( empty( $element['widgetType'] ) || $element['widgetType'] !== self::get_widget_name() ) {
			return $element;
		}

		$settings = $element['settings'];

		// Content bg color.
		if ( isset( $settings['content_bg'] ) && $settings['content_bg'] === '' ) {
			$settings['custom_content_bg_color'] = 'rgba(0,0,0,0)';
		}
		unset( $settings['content_bg'] );

		// Image ratio.
		$settings['item_ratio'] = [
			'unit'  => 'px',
			'size'  => 1,
			'sizes' => [],
		];
		if ( isset( $settings['image_sizing'] ) && $settings['image_sizing'] === 'proportional' ) {
			$settings['item_ratio'] = [
				'unit'  => 'px',
				'size'  => '',
				'sizes' => [],
			];
		} elseif ( isset( $settings['resize_image_to_width'], $settings['resize_image_to_height'] ) ) {
			$width                  = empty( $settings['resize_image_to_width'] ) ? 1 : $settings['resize_image_to_width'];
			$height                 = empty( $settings['resize_image_to_height'] ) ? 1 : $settings['resize_image_to_height'];
			$settings['item_ratio'] = [
				'unit'  => 'px',
				'size'  => $width / $height,
				'sizes' => [],
			];
		}
		unset( $settings['image_sizing'], $settings['resize_image_to_height'], $settings['resize_image_to_width'] );

		// Image padding.
		if ( isset( $settings['image_padding'] ) ) {
			$image_padding_unit = $settings['image_padding']['unit'];

			if ( $settings['image_padding']['top'] ) {
				$settings['box_padding'] = [
					'top'      => $settings['image_padding']['top'],
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => $image_padding_unit,
					'isLinked' => false,
				];
			}

			if ( $image_padding_unit === '%' ) {
				$settings['classic_image_max_width'] = [
					'size' => 100 - (int) $settings['image_padding']['left'] - (int) $settings['image_padding']['right'],
					'unit' => '%',
				];
			}

			if ( isset( $settings['post_content_padding'] ) && $image_padding_unit === $settings['post_content_padding']['unit'] ) {
				$settings['post_content_padding']['top'] = (int) $settings['post_content_padding']['top'] + (int) $settings['image_padding']['bottom'];
			}
		}
		unset( $settings['image_padding'] );

		// Image border radius.
		if ( isset( $settings['image_border_radius'] ) ) {
			$settings['img_border_radius'] = [
				'unit'     => $settings['image_border_radius']['unit'],
				'top'      => $settings['image_border_radius']['size'],
				'bottom'   => $settings['image_border_radius']['size'],
				'left'     => $settings['image_border_radius']['size'],
				'right'    => $settings['image_border_radius']['size'],
				'isLinked' => true,
			];
		}
		unset( $settings['image_border_radius'] );

		// Image shadow.
		if ( isset( $settings['image_decoration'] ) && $settings['image_decoration'] === 'shadow' ) {
			$settings['img_shadow_box_shadow_type'] = 'yes';
			$settings['img_shadow_box_shadow']      = [
				'horizontal' => isset( $settings['shadow_h_length']['size'] ) ? $settings['shadow_h_length']['size'] : '',
				'vertical'   => isset( $settings['shadow_v_length']['size'] ) ? $settings['shadow_v_length']['size'] : '',
				'blur'       => isset( $settings['shadow_blur_radius']['size'] ) ? $settings['shadow_blur_radius']['size'] : '',
				'spread'     => isset( $settings['shadow_spread']['size'] ) ? $settings['shadow_spread']['size'] : '',
				'color'      => isset( $settings['shadow_color'] ) ? $settings['shadow_color'] : '',
			];
		}
		unset( $settings['image_decoration'], $settings['shadow_h_length'], $settings['shadow_v_length'], $settings['shadow_blur_radius'], $settings['shadow_spread'], $settings['shadow_color'] );

		// Image hover color.
		if ( isset( $settings['image_hover_bg_color'] ) ) {
			if ( $settings['image_hover_bg_color'] === 'disabled' ) {
				$settings['overlay_hover_background_background'] = 'classic';
				$settings['overlay_hover_background_color']      = 'rgba(0,0,0,0)';
			} elseif ( $settings['image_hover_bg_color'] === 'solid_rollover_bg' ) {
				$settings['overlay_hover_background_background'] = 'classic';
				$settings['overlay_hover_background_color']      = isset( $settings['custom_rollover_bg_color'] ) ? $settings['custom_rollover_bg_color'] : 'rgba(0,0,0,0.5)';
			}
		}
		unset( $settings['image_hover_bg_color'], $settings['custom_rollover_bg_color'] );

		// Widget columns.
		$columns = [
			'desktop_columns'  => 'widget_columns',
			'tablet_h_columns' => 'widget_columns_tablet',
			'phone_columns'    => 'widget_columns_mobile',
		];
		unset( $settings['tablet_v_columns'] );
		if ( ! isset( $settings['widget_columns_wide_desktop'] ) ) {
			$settings['widget_columns_wide_desktop'] = 3;
		}
		if ( isset( $settings['desktop_columns'] ) ) {
			$settings['widget_columns_wide_desktop'] = $settings['desktop_columns'];
		}
		foreach ( $columns as $old_column => $new_column ) {
			if ( ! empty( $settings[ $old_column ] ) ) {
				$settings[ $new_column ] = $settings[ $old_column ];
			}
			unset( $settings[ $old_column ] );
		}

		// Bottom overlap conten width.
		if ( isset( $settings['post_layout'] ) && $settings['post_layout'] === 'bottom_overlap' ) {
			if ( ! isset( $settings['bo_content_width'] ) ) {
				$settings['bo_content_width'] = [
					'unit' => '%',
					'size' => 75,
				];
			}
		} else {
			$settings['bo_content_width'] = [
				'unit' => '%',
				'size' => '',
			];
		}

		// Gradient rollover content background.
		if ( isset( $settings['post_layout'] ) && $settings['post_layout'] === 'gradient_overlay' ) {
			$settings['custom_content_bg_color'] = '';
		}

		// Content box bg color.
		if ( isset( $settings['custom_content_bg_color'] ) && ( empty( $settings['post_layout'] ) || $settings['post_layout'] === 'classic' ) ) {
			$settings['box_background_color'] = $settings['custom_content_bg_color'];
		}

		// Content box alignment.
		if ( ! isset( $settings['post_content_box_alignment'] ) ) {
			$settings['post_content_box_alignment'] = 'center';
		}

		// Title spacing.
		if ( ! isset( $settings['post_title_bottom_margin'] ) ) {
			$settings['post_title_bottom_margin'] = [
				'unit' => 'px',
				'size' => 5,
			];
		}

		// Rename post_category to post_terms.
		if ( isset( $settings['post_category'] ) ) {
			$settings['post_terms'] = $settings['post_category'];
		}
		unset( $settings['post_category'] );

		// Post meta spacing.
		if ( isset( $settings['post_meta_bottom_margin'], $settings['read_more_button'], $settings['post_content'] ) && $settings['post_meta_bottom_margin']['size'] === '' && $settings['read_more_button'] === 'off' && $settings['post_content'] !== 'show_excerpt' ) {
			$settings['post_meta_bottom_margin']['size'] = 0;
		} elseif ( ! isset( $settings['post_meta_bottom_margin'] ) ) {
			$settings['post_meta_bottom_margin'] = [
				'unit' => 'px',
				'size' => 15,
			];
		}

		// Post content marging.
		if ( ! isset( $settings['post_content_bottom_margin'] ) ) {
			$settings['post_content_bottom_margin'] = [
				'unit' => 'px',
				'size' => 5,
			];
		}

		if ( isset( $settings['post_content_bottom_margin'] ) ) {
			$settings['post_content_bottom_margin']['size'] += 10;
		}

		// Read More button.
		if ( isset( $settings['read_more_button'] ) ) {
			if ( $settings['read_more_button'] === 'off' ) {
				$settings['show_read_more_button'] = '';
			} elseif ( $settings['read_more_button'] === 'default_link' ) {
				$settings['button_background_color']       = 'rgba(0,0,0,0)';
				$settings['button_text_color']             = of_get_option( 'content-headers_color' );
				$settings['button_typography_typography']  = 'custom';
				$settings['button_typography_font_size']   = [
					'unit' => 'px',
					'size' => 14,
				];
				$settings['button_typography_font_weight'] = '700';
				$settings['button_typography_line_height'] = [
					'unit' => 'px',
					'size' => 18,
				];
				$settings['button_border_border']          = 'solid';
				$settings['button_border_color']           = 'rgba(0,0,0,0)';
				$settings['button_hover_border_color']     = the7_theme_accent_color();
				$settings['button_border_width']           = [
					'unit'     => 'px',
					'top'      => '0',
					'bottom'   => '2',
					'left'     => '0',
					'right'    => '0',
					'isLinked' => false,
				];
				$settings['button_text_padding']           = [
					'unit'     => 'px',
					'top'      => '0',
					'bottom'   => '5',
					'left'     => '0',
					'right'    => '0',
					'isLinked' => false,
				];
			}
		}
		unset( $settings['read_more_button'] );

		// Hover icon.
		if ( isset( $settings['show_details'] ) ) {
			$settings['show_details_icon'] = $settings['show_details'];
		}
		unset( $settings['show_details'] );

		if ( ! isset( $settings['project_icon_size'] ) ) {
			$settings['project_icon_size'] = [
				'unit' => 'px',
				'size' => 16,
			];
		}

		if ( ! isset( $settings['project_icon_bg_size'] ) ) {
			$settings['project_icon_bg_size'] = [
				'unit' => 'px',
				'size' => 44,
			];
		}

		// Hover icon border width.
		if ( isset( $settings['project_icon_border_width']['size'] ) ) {
			$settings['project_icon_border_width'] = [
				'top'      => $settings['project_icon_border_width']['size'],
				'right'    => $settings['project_icon_border_width']['size'],
				'bottom'   => $settings['project_icon_border_width']['size'],
				'left'     => $settings['project_icon_border_width']['size'],
				'unit'     => $settings['project_icon_border_width']['unit'],
				'isLinked' => true,
			];
		}

		// Hover icon border radius.
		if ( isset( $settings['project_icon_border_radius']['size'] ) ) {
			$settings['project_icon_border_radius'] = [
				'top'      => $settings['project_icon_border_radius']['size'],
				'right'    => $settings['project_icon_border_radius']['size'],
				'bottom'   => $settings['project_icon_border_radius']['size'],
				'left'     => $settings['project_icon_border_radius']['size'],
				'unit'     => $settings['project_icon_border_radius']['unit'],
				'isLinked' => true,
			];
		} else {
			$settings['project_icon_border_radius'] = [
				'top'      => 100,
				'right'    => 100,
				'bottom'   => 100,
				'left'     => 100,
				'unit'     => 'px',
				'isLinked' => true,
			];
		}

		// Hover icon margin.
		if ( isset( $settings['project_icon_above_gap'], $settings['project_icon_below_gap'] ) ) {
			$settings['project_icon_margin'] = [
				'top'      => $settings['project_icon_above_gap']['size'],
				'right'    => '0',
				'bottom'   => $settings['project_icon_below_gap']['size'],
				'left'     => '0',
				'unit'     => 'px',
				'isLinked' => false,
			];
		}
		unset( $settings['project_icon_above_gap'], $settings['project_icon_below_gap'] );

		// Hover icon colors.
		foreach ( [ 'project_icon_bg_color' ] as $icon_color ) {
			if ( ! isset( $settings[ $icon_color ] ) ) {
				$settings[ $icon_color ] = 'rgba(255,255,255,0.3)';
			}
		}
		$hover_icon_colors = [
			'show_project_icon_border'       => 'project_icon_border_color',
			'project_icon_bg'                => 'project_icon_bg_color',
			'show_project_icon_hover_border' => 'project_icon_border_color_hover',
			'project_icon_bg_hover'          => 'project_icon_bg_color_hover',
		];
		foreach ( $hover_icon_colors as $color_switch => $color ) {
			if ( isset( $settings[ $color_switch ] ) && $settings[ $color_switch ] === '' ) {
				$settings[ $color ] = 'rgba(0,0,0,0)';
			}
			unset( $settings[ $color_switch ] );
		}

		if ( empty( $settings['project_icon_color'] ) ) {
			$settings['project_icon_color'] = the7_theme_accent_color();
		}

		if ( empty( $settings['project_icon_color_hover'] ) && ! empty( $settings['enable_project_icon_hover'] ) ) {
			$settings['project_icon_color_hover'] = of_get_option( 'content-links_color' );
		}
		unset( $settings['enable_project_icon_hover'] );

		// Filter colors.
		if ( isset( $settings['navigation_font_color'] ) ) {
			$settings['filter_hover_text_color']  = $settings['navigation_font_color'];
			$settings['filter_active_text_color'] = $settings['navigation_font_color'];
		}
		if ( isset( $settings['navigation_accent_color'] ) ) {
			$settings['filter_hover_pointer_color']  = $settings['navigation_accent_color'];
			$settings['filter_active_pointer_color'] = $settings['navigation_accent_color'];
		}
		unset( $settings['navigation_accent_color'] );

		// Load more button.
		if ( isset( $settings['loading_mode'] ) && $settings['loading_mode'] === 'js_more' ) {
			$title_color_obj = new The7_Less_Vars_Value_Color(
				of_get_option( 'content-headers_color' )
			);

			$settings['pagination_pointer_normal_color']   = $title_color_obj->opacity( 10 )->get_rgba();
			$settings['pagination_pointer_hover_color']    = $title_color_obj->opacity( 20 )->get_rgba();
			$settings['pagination_style']                  = 'framed';
			$settings['pagination_typography_typography']  = 'custom';
			$settings['pagination_typography_font_weight'] = 'bold';
			$settings['pagination_element_padding']        = [
				'top'      => '17',
				'right'    => '60',
				'bottom'   => '17',
				'left'     => '60',
				'unit'     => 'px',
				'isLinked' => false,
			];
		}

		$element['settings'] = $settings;
		$args['do_update']   = true;

		return $element;
	}

}