<?php
/**
 * The7 elements widget for Elementor.
 *
 * @package The7
 */

namespace The7\Adapters\Elementor\Widgets;

use Elementor\Core\Settings\Manager as Settings_Manager;
use Elementor\Controls_Stack;
use Elementor\Core\Responsive\Responsive;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Plugin;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Core\Schemes;
use The7\Adapters\Elementor\With_Post_Excerpt;
use The7_Query_Builder;
use The7_Categorization_Request;
use The7\Adapters\Elementor\The7_Elementor_Widget_Base;
use The7\Adapters\Elementor\With_Pagination;
use The7\Adapters\Elementor\The7_Elementor_Less_Vars_Decorator_Interface;

defined( 'ABSPATH' ) || exit;

class The7_Elementor_Elements_Widget extends The7_Elementor_Widget_Base {

	use With_Pagination;
	use With_Post_Excerpt;

	protected $has_img_preload_me_filter = false;

	/**
	 * Get element name.
	 *
	 * Retrieve the element name.
	 *
	 * @return string The name.
	 */
	public function get_name() {
		return 'the7_elements';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Masonry & Grid', 'the7mk2' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid the7-widget';
	}



	public function get_script_depends() {
		wp_register_script(
			'the7-elementor-masonry',
			PRESSCORE_THEME_URI . '/js/compatibility/elementor/the7-masonry-widget.js',
			[],
			THE7_VERSION,
			true
		);

		$scripts = [ 'the7-elementor-masonry' ];

		if ( $this->is_preview_mode() ) {
			wp_register_script(
				'the7-elements-widget-preview',
				PRESSCORE_ADMIN_URI . '/assets/js/elementor/elements-widget-preview.js',
				[],
				THE7_VERSION,
				true
			);

			$scripts[] = 'the7-elements-widget-preview';
		}

		return $scripts;
	}

	public function get_style_depends() {
		the7_register_style(
			'the7-elements-widget',
			PRESSCORE_THEME_URI . '/css/compatibility/elementor/the7-elements-widget'
		);

		return [ 'the7-elements-widget' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function _register_controls() {
		// Content Tab.
		$this->add_query_content_controls();
		$this->add_layout_content_controls();
		$this->add_content_controls();
		$this->add_filter_bar_content_controls();
		$this->add_pagination_content_controls();

		// Style Tab.
		$this->add_skin_style_controls();
		$this->add_box_style_controls();
		$this->add_image_style_controls();
		$this->add_hover_icon_style_controls();
		$this->add_content_style_controls();
		$this->add_post_title_style_controls();
		$this->add_post_meta_style_controls();
		$this->add_text_style_controls();
		$this->add_button_style_controls();
		$this->add_filter_bar_style_controls();
		$this->add_pagination_style_controls();
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( $settings['post_type'] !== 'current_query' && ! post_type_exists( $settings['post_type'] ) ) {
			echo the7_elementor_get_message_about_disabled_post_type();

			return;
		}

		$this->remove_image_hooks();
		$this->print_inline_css();

		echo '<div ' . $this->container_class() . $this->get_container_data_atts() . '>';

		$loading_mode = $this->get_loading_mode();

		$terms = [];
		if ( $settings['show_categories_filter'] ) {
			$terms = $this->get_posts_filter_terms( $settings['taxonomy'], $settings['terms'] );
		}

		$request = null;
		if ( $settings['post_type'] !== 'current_query' && $loading_mode === 'standard' ) {
			$request = new The7_Categorization_Request();

			if ( isset( $terms[0] ) && ! $this->filter_show_all() && ! $request->get_first_term() ) {
				$request->filter_by_term( $terms[0]->term_id );
			}
		}

		// Posts filter.
		$this->display_filter( $terms, $request );

		echo '<div class="' . esc_attr( $this->is_masonry_layout() ? 'iso-container dt-isotope custom-iso-columns' : 'dt-css-grid custom-pagination-handler' ) . '">';

		$data_post_limit        = $this->get_pagination_posts_limit();
		$show_image             = in_array( $settings['classic_image_visibility'], [ null, 'show' ], true );
		$icons_html_tpl         = $this->get_hover_icons_html_template();
		$is_overlay_post_layout = in_array(
			$settings['post_layout'],
			[ 'gradient_rollover', 'gradient_overlay' ],
			true
		);
		$query = $this->get_query( $request );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$follow_link = get_the_permalink();
				$icons_html  = str_replace( '#FOLLOW_LINK#', $follow_link, $icons_html_tpl );

				// Post is visible on the first page.
				$visibility = 'visible';
				if ( $data_post_limit >= 0 && $query->current_post >= $data_post_limit ) {
					$visibility = 'hidden';
				}

				$post_class_array = [
					'post',
					'visible',
				];

				if ( ! has_post_thumbnail() ) {
					$post_class_array[] = 'no-img';
				}

				if ( ! $icons_html && $is_overlay_post_layout ) {
					$post_class_array[] = 'forward-post';
				}

				echo '<div ' . $this->masonry_item_wrap_class( $visibility ) . presscore_tpl_masonry_item_wrap_data_attr() . '>';
				echo '<article class="' . esc_attr( implode( ' ', get_post_class( $post_class_array ) ) ) . '" data-name="' . esc_attr( get_the_title() ) . '" data-date="' . esc_attr( get_the_date( 'c' ) ) . '">';

				$details_btn = '';
				if ( $settings['show_read_more_button'] ) {
					$details_btn = $this->get_details_btn( $follow_link, $settings );
				}

				$post_meta_types    = [
					'terms',
					'author',
					'date',
					'comments',
				];
				$required_post_meta = [];
				foreach ( $post_meta_types as $post_meta_type ) {
					if ( ! empty( $settings["post_{$post_meta_type}"] ) ) {
						$required_post_meta[] = [
							'type' => $post_meta_type,
							'link' => ! empty( $settings["post_{$post_meta_type}_link"] ),
						];
					}
				}

				$post_title = '';
				if ( $settings['show_post_title'] ) {
					$post_title = $this->get_post_title( $follow_link, $settings['title_tag'] );
				}

				$post_excerpt = '';
				if ( $settings['post_content'] === 'show_excerpt' ) {
					$post_excerpt = $this->get_post_excerpt( $settings['excerpt_words_limit'] );
				}

				presscore_get_template_part(
					'elementor',
					'the7-elements/tpl-layout',
					$settings['post_layout'],
					[
						'settings'     => $settings,
						'post_title'   => $post_title,
						'post_media'   => $this->get_post_image( $follow_link, $show_image, $is_overlay_post_layout ),
						'post_meta'    => $this->get_post_meta( $required_post_meta ),
						'details_btn'  => $details_btn,
						'post_excerpt' => $post_excerpt,
						'icons_html'   => $icons_html,
						'follow_link'  => $follow_link,
					]
				);

				echo '</article>';
				echo '</div>';
			}
		}

		wp_reset_postdata();

		echo '</div><!-- iso-container|iso-grid -->';

		$this->display_pagination( $loading_mode, $query );

		echo '</div>';

		$this->add_image_hooks();
	}

	protected function remove_image_hooks() {
		$this->has_img_preload_me_filter = has_filter( 'dt_get_thumb_img-args', 'presscore_add_preload_me_class_to_images' );
		remove_filter( 'dt_get_thumb_img-args', 'presscore_add_preload_me_class_to_images' );
	}

	protected function add_image_hooks() {
		$this->has_img_preload_me_filter && add_filter(
			'dt_get_thumb_img-args',
			'presscore_add_preload_me_class_to_images',
			15
		);
	}

	protected function get_hover_icons_html_template() {
		$settings = $this->get_settings_for_display();

		if ( ! $settings['show_details_icon'] ) {
			return '';
		}

		return sprintf(
			'<a href="#FOLLOW_LINK#" class="%s" aria-label="%s">%s</a>',
			'project-details',
			__( 'Details link', 'the7mk2' ),
			$this->get_elementor_icon_html( $settings['project_link_icon'], 'span' )
		);
	}

	/**
	 * @param string $follow_link
	 * @param string $tag
	 *
	 * @return string
	 */
	protected function get_post_title( $follow_link, $tag = 'h4' ) {
		return sprintf(
			'<h3 class="entry-title %1$s-size"><a href="%2$s" title="%3$s" rel="bookmark">%4$s</a></h3>',
			$tag,
			esc_url( $follow_link ),
			the_title_attribute( 'echo=0' ),
			get_the_title()
		);
	}

	protected function get_post_image( $follow_link, $show_image, $is_overlay_post_layout ) {
		$settings   = $this->get_settings_for_display();
		$post_media = '';
		$target     = '';

		if ( has_post_thumbnail() && $show_image ) {
			$thumb_args = [
				'img_id' => get_post_thumbnail_id(),
				'class'  => 'post-thumbnail-rollover',
				'href'   => $follow_link,
				'custom' => ' aria-label="' . esc_attr__( 'Post image', 'the7mk2' ) . '"',
				'wrap'   => '<a %HREF% %CLASS% target="' . $target . '" %CUSTOM%><img %IMG_CLASS% %SRC% %ALT% %IMG_TITLE% %SIZE% /></a>',
				'echo'   => false,
			];

			$thumb_args['img_class'] = 'preload-me';

			if ( $settings['item_ratio']['size'] !== '' ) {
				$thumb_args['prop'] = $settings['item_ratio']['size'];
			}

			if ( 'browser_width_based' === $settings['responsiveness'] ) {
				$thumb_args['options'] = the7_calculate_bwb_image_resize_options(
					[
						'desktop'  => $settings['widget_columns'],
						'v_tablet' => $settings['widget_columns_tablet'],
						'h_tablet' => $settings['widget_columns_tablet'],
						'phone'    => $settings['widget_columns_mobile'],
					],
					$settings['gap_between_posts'],
					$this->current_post_is_wide()
				);
			} else {
				$thumb_args['options'] = the7_calculate_columns_based_image_resize_options(
					$settings['pwb_column_min_width']['size'],
					of_get_option( 'general-content_width' ),
					$settings['pwb_columns'],
					$this->current_post_is_wide()
				);
			}

			if ( presscore_lazy_loading_enabled() ) {
				$thumb_args['lazy_loading'] = true;
				if ( $this->is_masonry_layout() ) {
					$thumb_args['lazy_class'] = 'iso-lazy-load';
				}
			}

			$post_media = dt_get_thumb_img( $thumb_args );
		} elseif ( $is_overlay_post_layout ) {
			$image      = sprintf(
				'<img class="%s" src="%s" width="%s" height="%s">',
				'preload-me',
				get_template_directory_uri() . '/images/gray-square.svg',
				1500,
				1500
			);
			$post_media = sprintf(
				'<a class="%s" href="%s" aria-label="%s">%s</a>',
				'post-thumbnail-rollover',
				$follow_link,
				esc_attr__( 'Post image', 'the7mk2' ),
				$image
			);
		}

		return $post_media;
	}

	protected function get_details_btn( $follow_link, $settings ) {
		$icon = '';
		if ( $settings['show_read_more_button_icon'] ) {
			$icon = $this->get_elementor_icon_html( $settings['read_more_button_icon'] );
		}

		ob_start();
		presscore_get_template_part(
			'elementor',
			'the7-elements/read-more-button',
			null,
			[
				'settings'      => $settings,
				'follow_link'   => $follow_link,
				'caption'       => $settings['read_more_button_text'],
				'icon_position' => $settings['read_more_button_icon_position'],
				'icon'          => $icon,
				'aria_label'	=> the7_get_read_more_aria_label()
			]
		);

		return ob_get_clean();
	}

	protected function get_elementor_icon_html( $icon, $tag = 'i' ) {
		ob_start();
		Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ], $tag );

		return ob_get_clean();
	}

	protected function get_post_meta( $required_meta = [] ) {
		$parts = [];
		foreach ( $required_meta as $meta ) {
			$with_link = ! empty( $meta['link'] );
			switch ( $meta['type'] ) {
				case 'terms':
					$terms   = the7_get_post_terms( null, null, ', ', $with_link );
					$parts[] = $terms ? '<span class="meta-item category-link">' . $terms . '</span>' : '';
					break;
				case 'author':
					$parts[] = the7_get_post_author( null, $with_link );
					break;
				case 'date':
					$parts[] = the7_get_post_date( null, $with_link );
					break;
				case 'comments':
					$parts[] = the7_get_post_comments( null, $with_link );
					break;
			}
		}

		$html = '';
		if ( $parts ) {
			$html = '<div class="entry-meta">' . implode( '', $parts ) . '</div>';
		}

		return apply_filters( 'presscore_posted_on_html', $html, [] );
	}

	/**
	 * @param string    $loading_mode
	 * @param \WP_Query $query
	 */
	protected function display_pagination( $loading_mode, \WP_Query $query ) {
		if ( 'standard' === $loading_mode ) {
			$this->display_standard_pagination( $query->max_num_pages, $this->get_pagination_wrap_class() );
		} elseif ( in_array( $loading_mode, [ 'js_more', 'js_lazy_loading' ], true ) ) {
			$this->display_load_more_button( $this->get_pagination_wrap_class( 'paginator-more-button' ) );
		} elseif ( 'js_pagination' === $loading_mode ) {
			echo '<div class="' . esc_attr( $this->get_pagination_wrap_class() ) . '" role="navigation"></div>';
		}
	}

	/**
	 * @param int    $max_num_pages
	 * @param string $class
	 */
	protected function display_standard_pagination( $max_num_pages, $class = 'paginator' ) {
		$add_pagination_filter = has_filter( 'dt_paginator_args', 'presscore_paginator_show_all_pages_filter' );
		remove_filter( 'dt_paginator_args', 'presscore_paginator_show_all_pages_filter' );

		$num_pages  = $this->get_settings_for_display( 'show_all_pages' ) ? 9999 : 5;
		$item_class = 'page-numbers filter-item';
		$no_next    = '';
		$no_prev    = '';
		$prev_text  = '<i class="dt-icon-the7-arrow-35-1" aria-hidden="true"></i>';
		$next_text  = '<i class="dt-icon-the7-arrow-35-2" aria-hidden="true"></i>';

		dt_paginator( null, compact( 'max_num_pages', 'class', 'num_pages', 'item_class', 'no_next', 'no_prev', 'prev_text', 'next_text' ) );

		$add_pagination_filter && add_filter( 'dt_paginator_args', 'presscore_paginator_show_all_pages_filter' );
	}

	protected function display_load_more_button( $class = 'paginator-more-button' ) {
		echo dt_get_next_page_button(
			2,
			$class,
			$cur_page = 1,
			'highlighted filter-item',
			$this->get_settings_for_display( 'pagination_load_more_text' ),
			$this->get_elementor_icon_html( $this->get_settings_for_display( 'pagination_load_more_icon' ) ),
			$this->get_settings_for_display( 'pagination_load_more_icon_position' )
		);
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	protected function get_pagination_wrap_class( $class = '' ) {
		$settings = $this->get_settings_for_display();

		$wrap_class = [ 'paginator', 'filter-decorations', $class ];
		if ( $settings['pagination_style'] ) {
			$wrap_class[] = 'filter-pointer-' . $settings['pagination_style'];

			foreach ( $settings as $key => $value ) {
				if ( 0 === strpos( $key, 'pagination_animation' ) && $value ) {
					$wrap_class[] = 'filter-animation-' . $value;
					break;
				}
			}
		}

		return implode( ' ', array_filter( $wrap_class ) );
	}

	protected function current_post_is_wide() {
		global $post;

		if ( $this->get_settings_for_display( 'all_posts_the_same_width' ) ) {
			return false;
		}

		switch ( get_post_type( $post ) ) {
			case 'post':
				return get_post_meta( $post->ID, '_dt_post_options_preview', true ) === 'wide';
			case 'dt_gallery':
				return get_post_meta( $post->ID, '_dt_album_options_preview', true ) === 'wide';
			case 'dt_portfolio':
				return get_post_meta( $post->ID, '_dt_project_options_preview', true ) === 'wide';
		}

		return false;
	}

	protected function masonry_item_wrap_class( $class = array() ) {
		global $post;

		if ( ! is_array( $class ) ) {
			$class = explode( ' ', $class );
		}

		$settings = $this->get_settings_for_display();

		$class[] = 'wf-cell';

		if ( $this->current_post_is_wide() ) {
			$class[] = 'double-width';
		}

		if ( $this->is_masonry_layout() ) {
			$class[] = 'iso-item';
		}

		if ( $settings['show_categories_filter'] ) {
			$terms = get_the_terms( $post->ID, $settings['taxonomy'] );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$class[] = sanitize_html_class( 'category-' . $term->term_id );
				}
			} else {
				$class[] = 'category-0';
			}
		}

		return 'class="' . esc_attr( implode( ' ', $class ) ) . '" ';
	}

	/**
	 * Return container class attribute.
	 *
	 * @param array $class
	 *
	 * @return string
	 */
	protected function container_class( $class = [] ) {
		$class[] = 'portfolio-shortcode';
		$class[] = 'the7-elementor-widget';

		// Unique class.
		$class[] = $this->get_unique_class();

		$settings = $this->get_settings_for_display();

		$class[] = $this->is_masonry_layout() ? 'mode-masonry' : 'mode-grid dt-css-grid-wrap';

		$layout_classes = array(
			'classic'           => 'classic-layout-list',
			'bottom_overlap'    => 'bottom-overlap-layout-list',
			'gradient_overlap'  => 'gradient-overlap-layout-list',
			'gradient_overlay'  => 'gradient-overlay-layout-list',
			'gradient_rollover' => 'content-rollover-layout-list',
		);

		$layout = $settings['post_layout'];
		if ( array_key_exists( $layout, $layout_classes ) ) {
			$class[] = $layout_classes[ $layout ];
		}

		if ( in_array( $settings['post_layout'], [ 'gradient_overlay', 'gradient_rollover' ], true ) ) {
			$class[] = 'description-on-hover';
		} else {
			$class[] = 'description-under-image';
		}

		$loading_mode = $settings['loading_mode'];
		if ( 'standard' !== $loading_mode ) {
			$class[] = 'jquery-filter';
		}

		if ( 'js_lazy_loading' === $loading_mode ) {
			$class[] = 'lazy-loading-mode';
		}

		if ( $loading_mode === 'js_pagination' && $settings['show_all_pages'] ) {
			$class[] = 'show-all-pages';
		}

		if ( $settings['image_scale_animation_on_hover'] === 'quick_scale' ) {
			$class[] = 'quick-scale-img';
		} elseif ( $settings['image_scale_animation_on_hover'] === 'slow_scale' ) {
			$class[] = 'scale-img';
		}

		if ( ! $settings['post_date'] && ! $settings['post_terms'] && ! $settings['post_comments'] && ! $settings['post_author'] ) {
			$class[] = 'meta-info-off';
		}

		$gradient_layout = in_array( $layout, [ 'gradient_overlay', 'gradient_rollover' ] );
		if ( $gradient_layout && $settings['post_content'] !== 'show_excerpt' && ! $settings['show_read_more_button'] ) {
			$class[] = 'disable-layout-hover';
		}

		$class[] = 'content-bg-on';

		if ( ! $settings['overlay_background_background'] && ! $settings['overlay_hover_background_background']) {
			$class[] = 'enable-bg-rollover';
		}

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$class[] = 'resize-by-browser-width';
		}

		if ( $settings['show_categories_filter'] || $settings['show_orderby_filter'] || $settings['show_order_filter'] ) {
			$class[] = 'widget-with-filter';
		}

		$class[] = presscore_tpl_get_load_effect_class( $settings['loading_effect'] );

		if ( 'gradient_overlay' === $settings['post_layout'] ) {
			$class[] = presscore_tpl_get_hover_anim_class( $settings['go_animation'] );
		}

		return sprintf( ' class="%s" ', esc_attr( implode( ' ', $class ) ) );
	}

	protected function get_container_data_atts() {
		$settings = $this->get_settings_for_display();

		$data_pagination_mode = 'none';
		if ( in_array( $settings['loading_mode'], [ 'js_more', 'js_lazy_loading' ] ) ) {
			$data_pagination_mode = 'load-more';
		} elseif ( $settings['loading_mode'] === 'js_pagination' ) {
			$data_pagination_mode = 'pages';
		} elseif ( $settings['loading_mode'] === 'standard' ) {
			$data_pagination_mode = 'standard';
		}

		$data_atts = array(
			'data-padding="' . esc_attr( $this->combine_slider_value( $settings['gap_between_posts'] ) ) . '"',
			'data-cur-page="' . esc_attr( the7_get_paged_var() ) . '"',
			'data-post-limit="' . (int) $this->get_pagination_posts_limit() . '"',
			'data-pagination-mode="' . esc_attr( $data_pagination_mode ) . '"',
		);

		$target_width = $settings['pwb_column_min_width'];
		if ( $target_width && $target_width['size'] ) {
			$data_atts[] = 'data-width="' . absint( $target_width['size'] ) . '"';
		}

		if ( ! empty( $settings['pwb_columns'] ) ) {
			$data_atts[] = 'data-columns="' . absint( $settings['pwb_columns'] ) . '"';
		}

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$columns = [
				'mobile'       => $settings['widget_columns_mobile'],
				'tablet'       => $settings['widget_columns_tablet'],
				'desktop'      => $settings['widget_columns'],
				'wide-desktop' => $settings['widget_columns_wide_desktop'],
			];

			foreach ( $columns as $column => $val ) {
				$data_atts[] = 'data-' . $column . '-columns-num="' . esc_attr( $val ) . '"';
			}
		}

		return ' ' . implode( ' ', $data_atts );
	}

	protected function display_filter( $terms, $request ) {
		$settings     = $this->get_settings_for_display();
		$loading_mode = $this->get_loading_mode();
		$filter_class = [ 'iso-filter', 'filter-decorations' ];

		if ( $loading_mode === 'standard' ) {
			$filter_class[] = 'without-isotope';
		}

		if ( ! $this->is_masonry_layout() ) {
			$filter_class[] = 'css-grid-filter';
		}

		if ( ! $settings['show_orderby_filter'] && ! $settings['show_order_filter'] ) {
			$filter_class[] = 'extras-off';
		}

		$filter_class[] = 'filter';

		if ( $settings['filter_style'] ) {
			$filter_class[] = 'filter-pointer-' . $settings['filter_style'];

			foreach ( $settings as $key => $value ) {
				if ( 0 === strpos( $key, 'animation' ) && $value ) {
					$filter_class[] = 'filter-animation-' . $value;
					break;
				}
			}
		}

		if ( $settings['filter_style'] === 'default' ) {
			$filter_style = of_get_option( 'general-filter_style' );
			if ( $filter_style === 'minimal' ) {
				$filter_class[] = 'filter-bg-decoration';
			} elseif ( $filter_style === 'material' ) {
				$filter_class[] = 'filter-underline-decoration';
			} else {
				$filter_class[] = 'filter-without-decoration';
			}
		}

		$current_term = 'all';
		$sorting_args = [
			'show_order'   => $settings['show_order_filter'],
			'show_orderby' => $settings['show_orderby_filter'],
			'order'        => $settings['order'],
			'orderby'      => $settings['orderby'],
			'select'       => 'all',
			'term_id'      => 'none',
		];

		if ( $loading_mode === 'standard' && is_object( $request ) && $request->not_empty() ) {
			$sorting_args['select']  = 'only';
			$sorting_args['order']   = $request->order;
			$sorting_args['orderby'] = $request->orderby;
			$sorting_args['term_id'] = $request->get_first_term();
			$current_term            = $request->get_first_term();
		}

		$args_filter_priority = has_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg'
		);
		remove_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg',
			$args_filter_priority
		);

		presscore_get_category_list(
			[
				'data'       => [
					'terms'       => $terms,
					'all_count'   => false,
					'other_count' => false,
				],
				'class'      => implode( ' ', $filter_class ),
				'item_class' => 'filter-item',
				'all_class'  => 'show-all filter-item',
				'sorting'    => $sorting_args,
				'all_btn'    => $this->filter_show_all(),
				'all_text'   => $settings['filter_all_text'],
				'current'    => $current_term,
			]
		);

		$args_filter_priority !== false && add_filter(
			'presscore_get_category_list-args',
			'presscore_filter_categorizer_current_arg',
			$args_filter_priority
		);
	}

	/**
	 * @return string
	 */
	protected function get_loading_mode() {
		$settings = $this->get_settings_for_display();

		// Only standard pagination for current query.
		if ( $settings['post_type'] === 'current_query' ) {
			return 'standard';
		}

		return $settings['loading_mode'];
	}

	protected function filter_show_all() {
		return $this->get_settings_for_display( 'filter_show_all' );
	}

	/**
	 * Return shortcode less file absolute path to output inline.
	 *
	 * @return string
	 */
	protected function get_less_file_name() {
		return PRESSCORE_THEME_DIR . '/css/dynamic-less/elementor/the7-elements-widget.less';
	}

	/**
	 * Return less imports.
	 *
	 * @return array
	 */
	protected function get_less_imports() {
		$settings           = $this->get_settings_for_display();
		$dynamic_import_top = [];

		switch ( $settings['post_layout'] ) {
			case 'gradient_overlap':
				$dynamic_import_top[] = 'post-layouts/gradient-overlap-layout.less';
				break;
			case 'gradient_overlay':
				$dynamic_import_top[] = 'post-layouts/gradient-overlay-layout.less';
				break;
			case 'gradient_rollover':
				$dynamic_import_top[] = 'post-layouts/content-rollover-layout.less';
				break;
			case 'classic':
			default:
				$dynamic_import_top[] = 'post-layouts/classic-layout.less';
		}

		$dynamic_import_bottom = [];
		if ( ! $this->is_masonry_layout() ) {
			$dynamic_import_bottom[] = 'grid.less';
		}

		return compact( 'dynamic_import_top', 'dynamic_import_bottom' );
	}

	/**
	 * Return array of prepared less vars to insert to less file.
	 *
	 * @return array
	 */
	protected function less_vars( The7_Elementor_Less_Vars_Decorator_Interface $less_vars ) {
		$settings = $this->get_settings_for_display();

		$less_vars->add_keyword(
			'unique-shortcode-class-name',
			$this->get_unique_class() . '.portfolio-shortcode',
			'~"%s"'
		);

		if ( 'browser_width_based' === $settings['responsiveness'] ) {
			$columns = array(
				'desktop'      => $settings['widget_columns'],
				'tablet'       => $settings['widget_columns_tablet'],
				'mobile'       => $settings['widget_columns_mobile'],
				'wide-desktop' => $settings['widget_columns_wide_desktop'],
			);

			foreach ( $columns as $column => $val ) {
				$less_vars->add_keyword( $column . '-columns', $val );
			}
		}

		$less_vars->add_keyword( 'post-content-bg', $settings['custom_content_bg_color'] );

		$less_vars->add_pixel_number( 'grid-posts-gap', $settings['gap_between_posts'] );
		$less_vars->add_pixel_number( 'grid-post-min-width', $settings['pwb_column_min_width'] );

		$less_vars->add_paddings(
			array(
				'post-content-padding-top',
				'post-content-padding-right',
				'post-content-padding-bottom',
				'post-content-padding-left',
			),
			$settings['post_content_padding']
		);

		$less_vars->add_keyword( 'show-filter-by-tablet', $settings['show_orderby_filter_tablet'] );
		$less_vars->add_keyword( 'show-filter-by-mobile', $settings['show_orderby_filter_mobile'] );
		$less_vars->add_keyword( 'show-filter-sorting-tablet', $settings['show_order_filter_tablet'] );
		$less_vars->add_keyword( 'show-filter-sorting-mobile', $settings['show_order_filter_mobile'] );

		foreach ( Responsive::get_breakpoints() as $size => $value ) {
			$less_vars->add_pixel_number( "elementor-{$size}-breakpoint", $value );
		}

		$less_vars->add_pixel_number(
			'elementor-container-width',
			the7_elementor_get_content_width_string()
		);
	}

	protected function get_pagination_posts_limit() {
		$settings = $this->get_settings_for_display();

		$posts_limit = '-1';
		switch ( $settings['loading_mode'] ) {
			case 'js_pagination':
				$posts_limit = $settings['jsp_posts_per_page'];
				break;
			case 'js_more':
				$posts_limit = $settings['jsm_posts_per_page'];
				break;
			case 'js_lazy_loading':
				$posts_limit = $settings['jsl_posts_per_page'];
				break;
		}

		if ( ! $posts_limit ) {
			$posts_limit = get_option( 'posts_per_page' );
		}

		return $posts_limit;
	}

	protected function get_query( $request ) {
		$settings = $this->get_settings_for_display();

		if ( $settings['post_type'] === 'current_query' ) {
			return $GLOBALS['wp_query'];
		}

		$loading_mode = $settings['loading_mode'];

		// Loop query.
		$query_args = [
			'posts_offset'   => $settings['posts_offset'],
			'post_type'      => $settings['post_type'],
			'order'          => $settings['order'],
			'orderby'        => $settings['orderby'],
			'posts_per_page' => $this->get_posts_per_page( $loading_mode, $settings ),
		];

		$query_builder = ( new The7_Query_Builder( $query_args ) )->from_terms(
			$settings['taxonomy'],
			$settings['terms']
		);

		if ( $loading_mode === 'standard' ) {
			$query_builder->with_categorizaition( $request );
			$query_builder->set_page( the7_get_paged_var() );
		}

		return $query_builder->query();
	}

	protected function get_posts_filter_terms( $taxonomy, $terms = [] ) {
		$get_terms_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		];

		if ( $terms ) {
			$get_terms_args['include'] = $terms;
		}

		return get_terms( $get_terms_args );
	}

	/**
	 * @return bool
	 */
	protected function is_masonry_layout() {
		return $this->get_settings_for_display( 'layout' ) === 'masonry';
	}

	/**
	 * @return bool
	 */
	protected function is_preview_mode() {
		return Plugin::$instance->preview->is_preview_mode();
	}

	protected function add_filter_bar_content_controls() {
		$this->start_controls_section(
			'categorization_section',
			[
				'label'     => __( 'Filter Bar', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'show_categories_filter',
			[
				'label'        => __( 'Categories Filter', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->add_control(
			'filter_show_all',
			[
				'label'        => __( '"All" Filter', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_control(
			'filter_all_text',
			[
				'label'       => __( '"All" Filter Label', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'View all', 'the7mk2' ),
				'placeholder' => '',
				'condition'   => [
					'filter_show_all'        => 'y',
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_responsive_control(
			'show_orderby_filter',
			[
				'label'        => __( 'Name / Date Ordering', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->add_responsive_control(
			'show_order_filter',
			[
				'label'        => __( 'Asc. / Desc. Ordering', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	protected function add_pagination_content_controls() {
		$this->start_controls_section(
			'pagination',
			[
				'label' => __( 'Pagination', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'loading_mode',
			[
				'label'     => __( 'Pagination Mode', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'disabled',
				'options'   => [
					'disabled'        => 'Disabled',
					'standard'        => 'Standard',
					'js_pagination'   => 'JavaScript pages',
					'js_more'         => '"Load more" button',
					'js_lazy_loading' => 'Infinite scroll',
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'pagination_load_more_text',
			[
				'label'       => __( 'Button Text', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Load more', 'the7mk2' ),
				'placeholder' => '',
				'condition'   => [
					'loading_mode' => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_show_load_more_icon',
			[
				'label'        => __( 'Icon', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'loading_mode' => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_load_more_icon',
			[
				'label'     => '',
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-arrow-circle-down',
					'library' => 'fa-solid',
				],
				'condition' => [
					'loading_mode'                   => 'js_more',
					'pagination_show_load_more_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'pagination_load_more_icon_position',
			[
				'label'     => __( 'Icon Position', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'toggle'    => false,
				'default'   => 'before',
				'options'   => [
					'before' => __( 'Before', 'the7mk2' ),
					'after'  => __( 'After', 'the7mk2' ),
				],
				'condition' => [
					'loading_mode'                   => 'js_more',
					'pagination_show_load_more_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'pagination_load_more_icon_spacing',
			[
				'label'      => __( 'Icon Spacing', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => - 200,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .paginator a.button-load-more i:first-child' => 'margin: 0 {{SIZE}}{{UNIT}} 0 0;',
					'{{WRAPPER}} .paginator a.button-load-more i:last-child'  => 'margin: 0 0 0 {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'loading_mode'                   => 'js_more',
					'pagination_show_load_more_icon' => 'y',
				],
			]
		);

		// Disabled pagination.
		$this->add_control(
			'dis_posts_total',
			[
				'label'       => __( 'Total Number Of Posts', 'the7mk2' ),
				'description' => __( 'Leave empty to display all posts.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'disabled',
					'post_type!'   => 'current_query',
				],
			]
		);

		// Standard pagination.
		$this->add_control(
			'st_posts_per_page',
			[
				'label'       => __( 'Posts Per Page', 'the7mk2' ),
				'description' => __(
					'Leave empty to use value from the WP Reading settings. Set "-1" to show all posts.',
					'the7mk2'
				),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'standard',
					'post_type!'   => 'current_query',
				],
			]
		);

		// JS pagination.
		$this->add_control(
			'jsp_posts_total',
			[
				'label'       => __( 'Total Number Of Posts', 'the7mk2' ),
				'description' => __( 'Leave empty to display all posts.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_pagination',
					'post_type!'   => 'current_query',
				],
			]
		);

		$this->add_control(
			'jsp_posts_per_page',
			[
				'label'       => __( 'Posts Per Page', 'the7mk2' ),
				'description' => __( 'Leave empty to use value from the WP Reading settings.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_pagination',
					'post_type!'   => 'current_query',
				],
			]
		);

		// JS load more.
		$this->add_control(
			'jsm_posts_total',
			[
				'label'       => __( 'Total Number Of Posts', 'the7mk2' ),
				'description' => __( 'Leave empty to display all posts.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_more',
					'post_type!'   => 'current_query',
				],
			]
		);

		$this->add_control(
			'jsm_posts_per_page',
			[
				'label'       => __( 'Posts Per Page', 'the7mk2' ),
				'description' => __( 'Leave empty to use value from the WP Reading settings.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_more',
					'post_type!'   => 'current_query',
				],
			]
		);

		// JS infinite scroll.
		$this->add_control(
			'jsl_posts_total',
			[
				'label'       => __( 'Total Number Of Posts', 'the7mk2' ),
				'description' => __( 'Leave empty to display all posts.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_lazy_loading',
					'post_type!'   => 'current_query',
				],
			]
		);

		$this->add_control(
			'jsl_posts_per_page',
			[
				'label'       => __( 'Posts Per Page', 'the7mk2' ),
				'description' => __( 'Leave empty to use value from the WP Reading settings.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'loading_mode' => 'js_lazy_loading',
					'post_type!'   => 'current_query',
				],
			]
		);

		// Posts offset.
		$this->add_control(
			'posts_offset',
			[
				'label'       => __( 'Posts Offset', 'the7mk2' ),
				'description' => __(
					'Offset for posts query (i.e. 2 means, posts will be displayed starting from the third post).',
					'the7mk2'
				),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'condition'   => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'show_all_pages',
			[
				'label'        => __( 'Show All Pages In Paginator', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => '',
				'conditions'   => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'loading_mode',
							'operator' => 'in',
							'value'    => [ 'standard', 'js_pagination' ],
						],
						[
							'name'     => 'post_type',
							'operator' => '==',
							'value'    => 'current_query',
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_query_content_controls() {
		$this->start_controls_section(
			'query_section',
			[
				'label' => __( 'Query', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'   => __( 'Source', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT2,
				'default' => 'post',
				'options' => the7_elementor_elements_widget_post_types(),
				'classes' => 'select2-medium-width',
			]
		);

		$this->add_control(
			'taxonomy',
			[
				'label'     => __( 'Select Taxonomy', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'category',
				'options'   => [],
				'classes'   => 'select2-medium-width',
				'condition' => [
					'post_type!' => [ '', 'current_query' ],
				],
			]
		);

		$this->add_control(
			'terms',
			[
				'label'     => __( 'Select Terms', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => '',
				'multiple'  => true,
				'options'   => [],
				'classes'   => 'select2-medium-width',
				'condition' => [
					'taxonomy!'  => '',
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'     => __( 'Order', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'desc',
				'options'   => [
					'asc'  => __( 'Ascending', 'the7mk2' ),
					'desc' => __( 'Descending', 'the7mk2' ),
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'     => __( 'Order By', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'          => __( 'Date', 'the7mk2' ),
					'title'         => __( 'Name', 'the7mk2' ),
					'ID'            => __( 'ID', 'the7mk2' ),
					'modified'      => __( 'Modified', 'the7mk2' ),
					'comment_count' => __( 'Comment count', 'the7mk2' ),
					'menu_order'    => __( 'Menu order', 'the7mk2' ),
					'rand'          => __( 'Rand', 'the7mk2' ),
				],
				'condition' => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_content_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_details_icon',
			[
				'label'        => __( 'Hover Icon', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
			]
		);

		$this->add_control(
			'project_link_icon',
			[
				'label'     => __( 'Choose Icon', 'the7mk2' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'icomoon-the7-font-the7-plus-02',
					'library' => 'the7-cons',
				],
				'condition' => [
					'show_details_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'show_post_title',
			[
				'label'        => __( 'Title', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => __( 'Title HTML Tag', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
				],
				'default'   => 'h4',
				'condition' => [
					'show_post_title' => 'y',
				],
			]
		);

		$this->add_control(
			'post_content',
			[
				'label'        => __( 'Excerpt', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'show_excerpt',
				'default'      => 'show_excerpt',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'excerpt_words_limit',
			[
				'label'       => __( 'Maximum Number Of Words', 'the7mk2' ),
				'description' => __( 'Leave empty to show the entire excerpt.', 'the7mk2' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'condition'   => [
					'post_content' => 'show_excerpt',
				],
			]
		);

		$this->add_control(
			'post_terms',
			[
				'label'        => __( 'Category', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_terms_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_terms' => 'y',
				],
			]
		);

		$this->add_control(
			'post_author',
			[
				'label'        => __( 'Author', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_author_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_author' => 'y',
				],
			]
		);

		$this->add_control(
			'post_date',
			[
				'label'        => __( 'Date', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_date_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_date' => 'y',
				],
			]
		);

		$this->add_control(
			'post_comments',
			[
				'label'        => __( 'Comments count', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'post_comments_link',
			[
				'label'        => __( 'Link', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'the7mk2' ),
				'label_off'    => __( 'No', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'post_comments' => 'y',
				],
			]
		);

		$this->add_control(
			'show_read_more_button',
			[
				'label'        => __( 'Read More', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'read_more_button_text',
			[
				'label'     => __( 'Button Text', 'the7mk2' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Read more', 'the7mk2' ),
				'condition' => [
					'show_read_more_button' => 'y',
				],
			]
		);

		$this->add_control(
			'show_read_more_button_icon',
			[
				'label'        => __( 'Icon', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'y',
				'default'      => 'y',
				'condition'    => [
					'show_read_more_button' => 'y',
				],
			]
		);

		$this->add_control(
			'read_more_button_icon',
			[
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'dt-icon-the7-arrow-552',
					'library' => 'the7-icons',
				],
				'condition' => [
					'show_read_more_button'      => 'y',
					'show_read_more_button_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'read_more_button_icon_position',
			[
				'label'     => __( 'Icon Position', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => [
					'after'  => __( 'After', 'the7mk2' ),
					'before' => __( 'Before', 'the7mk2' ),
				],
				'condition' => [
					'show_read_more_button'      => 'y',
					'show_read_more_button_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'read_more_button_icon_spacing',
			[
				'label'      => __( 'Icon Spacing', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .post-details i:first-child' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .post-details i:last-child'  => 'margin-left: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_read_more_button'      => 'y',
					'show_read_more_button_icon' => 'y',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_layout_content_controls() {
		$this->start_controls_section(
			'layout_section',
			[
				'label' => __( 'Layout', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'        => __( 'Masonry', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'masonry',
				'default'      => 'masonry',
			]
		);

		$this->add_control(
			'loading_effect',
			[
				'label'   => __( 'Loading Effect', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'             => __( 'None', 'the7mk2' ),
					'fade_in'          => __( 'Fade in', 'the7mk2' ),
					'move_up'          => __( 'Move up', 'the7mk2' ),
					'scale_up'         => __( 'Scale up', 'the7mk2' ),
					'fall_perspective' => __( 'Fall perspective', 'the7mk2' ),
					'fly'              => __( 'Fly', 'the7mk2' ),
					'flip'             => __( 'Flip', 'the7mk2' ),
					'helix'            => __( 'Helix', 'the7mk2' ),
					'scale'            => __( 'Scale', 'the7mk2' ),
				],
			]
		);

		$this->add_control(
			'responsiveness',
			[
				'label'     => __( 'Responsiveness Mode', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'browser_width_based',
				'options'   => [
					'browser_width_based' => __( 'Browser width based', 'the7mk2' ),
					'post_width_based'    => __( 'Post width based', 'the7mk2' ),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'widget_columns_wide_desktop',
			[
				'label'       => __( 'Columns On A Wide Desktop', 'the7mk2' ),
				'description' => sprintf(
					__(
						// translators: %s: elementor content width
						'Apply when browser width is bigger than %s ("Content Width" Elementor setting).',
						'the7mk2'
					),
					the7_elementor_get_content_width_string()
				),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 4,
				'condition'   => [
					'responsiveness' => 'browser_width_based',
				],
			]
		);

		$this->add_responsive_control(
			'widget_columns',
			[
				'label'          => __( 'Columns', 'the7mk2' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'condition'      => [
					'responsiveness' => 'browser_width_based',
				],
			]
		);

		$this->add_control(
			'pwb_column_min_width',
			[
				'label'      => __( 'Column Minimum Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => 300,
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'condition'  => [
					'responsiveness' => 'post_width_based',
				],
				'separator'  => 'before',
			]
		);

		$this->add_control(
			'pwb_columns',
			[
				'label'     => __( 'Desired Columns Number', 'the7mk2' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3,
				'min'       => 1,
				'max'       => 12,
				'condition' => [
					'responsiveness' => 'post_width_based',
					'layout'         => 'masonry',
				],
			]
		);

		$this->add_control(
			'gap_between_posts',
			[
				'label'       => __( 'Gap Between Columns', 'the7mk2' ),
				'description' => __(
					'Please note that this setting affects post paddings. So, for example: a value 10px will give you 20px gaps between posts)',
					'the7mk2'
				),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'unit' => 'px',
					'size' => 15,
				],
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
			]
		);

		$this->add_control(
			'all_posts_the_same_width',
			[
				'label'        => __( 'Make All Posts The Same Width', 'the7mk2' ),
				'description'  => __( 'Post wide/normal width can be chosen in single post options.', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'y',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	protected function add_skin_style_controls() {
		$this->start_controls_section(
			'skins_style_section',
			[
				'label' => __( 'Skin', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'post_layout',
			[
				'label'   => __( 'Choose Skin', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'classic'           => __( 'Classic', 'the7mk2' ),
					'bottom_overlap'    => __( 'Overlapping content area', 'the7mk2' ),
					'gradient_overlap'  => __( 'Blurred content area', 'the7mk2' ),
					'gradient_overlay'  => __( 'Simple overlay on hover', 'the7mk2' ),
					'gradient_rollover' => __( 'Blurred bottom overlay on hover', 'the7mk2' ),
				],
			]
		);

		$this->add_control(
			'classic_image_visibility',
			[
				'label'        => __( 'Image Visibility', 'the7mk2' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'the7mk2' ),
				'label_off'    => __( 'Hide', 'the7mk2' ),
				'return_value' => 'show',
				'default'      => 'show',
				'condition'    => [
					'post_layout' => 'classic',
				],
			]
		);

		$this->add_responsive_control(
			'classic_image_max_width',
			[
				'label'      => __( 'Max Image Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '',
				],
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 2000,
						'step' => 1,
					],
					'%'  => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .classic-layout-list .post-thumbnail' => 'max-width: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'post_layout'              => 'classic',
					'classic_image_visibility' => 'show',
				],
			]
		);

		$this->add_responsive_control(
			'bo_content_overlap',
			[
				'label'      => __( 'Content Box Overlap', 'the7mk2' ) . ' (px)',
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .bottom-overlap-layout-list article:not(.no-img) .post-entry-content' => 'margin-top: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bottom-overlap-layout-list article:not(.no-img) .project-links-container' => 'height: calc(100% - {{SIZE}}{{UNIT}});',
				],
				'condition'  => [
					'post_layout' => 'bottom_overlap',
				],
			]
		);

		$this->add_control(
			'go_animation',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'              => __( 'Fade', 'the7mk2' ),
					'direction_aware'   => __( 'Direction aware', 'the7mk2' ),
					'redirection_aware' => __( 'Reverse direction aware', 'the7mk2' ),
					'scale_in'          => __( 'Scale in', 'the7mk2' ),
				],
				'condition' => [
					'post_layout' => 'gradient_overlay',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_box_style_controls() {
		$this->start_controls_section(
			'box_section',
			[
				'label'     => __( 'Box', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'box_border_width',
			[
				'label'      => __( 'Border Width', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article' => 'border-style: solid; border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => __( 'Padding', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} article' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'box_style_tabs' );

		$this->start_controls_tab(
			'classic_style_normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .the7-elementor-widget:not(.class-1) article',
			]
		);

		$this->add_control(
			'box_background_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'box_border_color',
			[
				'label'     => __( 'Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'classic_style_hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow_hover',
				'selector' => '{{WRAPPER}} .the7-elementor-widget:not(.class-1) article:hover',
			]
		);

		$this->add_control(
			'box_background_color_hover',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'box_border_color_hover',
			[
				'label'     => __( 'Border Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_hover_icon_style_controls() {
		$this->start_controls_section(
			'icon_style_section',
			[
				'label'     => __( 'Hover Icon', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_details_icon' => 'y',
				],
			]
		);

		$this->add_control(
			'project_icon_size',
			[
				'label'      => __( 'Icon Size', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a > span:before' => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'project_icon_bg_size',
			[
				'label'      => __( 'Background Size', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a > span:before' => 'line-height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .project-links-container a'               => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_width',
			[
				'label'      => __( 'Border Width', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a:before' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .project-links-container a:after'  => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'project_icon_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'project_icon_margin',
			[
				'label'      => __( 'Icon Margin', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .project-links-container a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'icon_style_tabs' );

		$this->start_controls_tab(
			'icons_colors',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'project_icon_color',
			[
				'label'     => __( 'Icon Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a:not(:hover) > span' => 'color: {{VALUE}};',
					'{{WRAPPER}} .project-links-container a > span'             => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_color',
			[
				'label'       => __( 'Icon Border Color', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .project-links-container a:before' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .project-links-container a:after'  => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_bg_color',
			[
				'label'     => __( 'Icon Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a:before' => 'background: {{VALUE}};',
					'{{WRAPPER}} .project-links-container a:after'  => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icons_hover_colors',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'project_icon_color_hover',
			[
				'label'       => __( 'Icon Color', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .project-links-container a:hover > span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'project_icon_border_color_hover',
			[
				'label'       => __( 'Icon Border Color', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .project-links-container a:after'  => 'border-color: {{VALUE}}; transition: opacity 0.150s linear;',
					'{{WRAPPER}} .project-links-container a:before' => 'transition: opacity 0.150s linear;',
				],
			]
		);

		$this->add_control(
			'project_icon_bg_color_hover',
			[
				'label'     => __( 'Icon Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .project-links-container a:after'  => 'background: {{VALUE}}; box-shadow: none; transition: opacity 0.150s linear;',
					'{{WRAPPER}} .project-links-container a:before' => 'transition: opacity 0.150s linear;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_image_style_controls() {
		$this->start_controls_section(
			'section_design_image',
			[
				'label'     => __( 'Image', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'classic_image_visibility!' => '',
				],
			]
		);

		$this->add_control(
			'item_ratio',
			[
				'label'       => __( 'Image Ratio', 'the7mk2' ),
				'description' => __( 'Lieve empty to use original proportions', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => '',
				],
				'range'       => [
					'px' => [
						'min'  => 0.1,
						'max'  => 2,
						'step' => 0.01,
					],
				],
			]
		);

		$this->add_control(
			'img_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail'         => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail > a'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .post-thumbnail-wrap .post-thumbnail > a img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_scale_animation_on_hover',
			[
				'label'   => __( 'Scale Animation On Hover', 'the7mk2' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'quick_scale',
				'options' => [
					'disabled'    => __( 'Disabled', 'the7mk2' ),
					'quick_scale' => __( 'Quick scale', 'the7mk2' ),
					'slow_scale'  => __( 'Slow scale', 'the7mk2' ),
				],
			]
		);

		$this->start_controls_tabs( 'thumbnail_effects_tabs' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_background',
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => __( 'Background Overlay', 'the7mk2' ),
					],
				],
				'selector'       => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail > .post-thumbnail-rollover:after,
				{{WRAPPER}} .description-on-hover article .post-thumbnail > .post-thumbnail-rollover:after
				',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'img_shadow',
				'selector'  => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail,
				{{WRAPPER}} .description-on-hover article .post-thumbnail
				',
				'condition' => [
					'post_layout!' => [ 'gradient_rollover', 'gradient_overlay' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'thumbnail_filters',
				'selector' => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap .post-thumbnail img,
				{{WRAPPER}} .description-on-hover article .post-thumbnail img
				',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_hover_background',
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'label' => __( 'Background Overlay', 'the7mk2' ),
					],
				],
				'selector'       => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail > .post-thumbnail-rollover:after,
				{{WRAPPER}} .gradient-overlap-layout-list article:hover .post-thumbnail > .post-thumbnail-rollover:after,
				{{WRAPPER}} .description-on-hover article:hover .post-thumbnail > .post-thumbnail-rollover:after
				',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'img_hover_shadow',
				'selector'  => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail,
				{{WRAPPER}} .description-on-hover article:hover .post-thumbnail
				',
				'condition' => [
					'post_layout!' => [ 'gradient_rollover', 'gradient_overlay' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'thumbnail_hover_filters',
				'selector' => '
				{{WRAPPER}} .description-under-image .post-thumbnail-wrap:hover .post-thumbnail img,
				{{WRAPPER}} .description-on-hover article:hover .post-thumbnail img
				',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function add_content_style_controls() {
		$this->start_controls_section(
			'content_style_section',
			[
				'label' => __( 'Content', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'custom_content_bg_color',
			[
				'label'   => __( 'Background Color', 'the7mk2' ),
				'type'    => Controls_Manager::COLOR,
				'alpha'   => true,
				'default' => '',
			]
		);

		$this->add_responsive_control(
			'bo_content_width',
			[
				'label'      => __( 'Content Width', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => '%',
					'size' => '',
				],
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .description-under-image .post-entry-content'               => 'width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .description-on-hover .post-entry-content .post-entry-body' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'post_content_padding',
			[
				'label'      => __( 'Content Padding', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} article .post-entry-content'                       => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .content-rollover-layout-list .post-entry-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'post_content_box_alignment',
			[
				'label'                => __( 'Horizontal Position', 'the7mk2' ),
				'type'                 => Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'left',
				'options'              => [
					'left'   => [
						'title' => __( 'Left', 'the7mk2' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'the7mk2' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'the7mk2' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .description-under-image .post-entry-content'                       => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .description-on-hover .post-entry-content .post-entry-body'         => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .description-on-hover .post-entry-content .project-links-container' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'post_content_alignment',
			[
				'label'     => __( 'Text Alignment', 'the7mk2' ),
				'type'      => Controls_Manager::CHOOSE,
				'toggle'    => false,
				'default'   => 'left',
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
					'{{WRAPPER}} .post-entry-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_post_title_style_controls() {
		$this->start_controls_section(
			'post_title_style_section',
			[
				'label'     => __( 'Post Title', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_post_title' => 'y',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_title',
				'label'          => __( 'Typography', 'the7mk2' ),
				'selector'       => '{{WRAPPER}} .entry-title',
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
			]
		);

		$this->start_controls_tabs( 'post_title_style_tabs' );

		$this->start_controls_tab(
		    'post_title_normal_style',
		    [
		        'label' => __( 'Normal', 'the7mk2' ),
		    ]
		);

		$this->add_control(
			'custom_title_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'#page {{WRAPPER}} article:not(.keep-custom-css) .entry-title a'       => 'color: {{VALUE}}',
					'#page {{WRAPPER}} article:not(.keep-custom-css) .entry-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
		    'post_title_hover_style',
		    [
		        'label' => __( 'Hover', 'the7mk2' ),
		    ]
		);

		$this->add_control(
			'post_title_color_hover',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'#page {{WRAPPER}} article:not(.keep-custom-css) .entry-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'post_title_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .entry-title'                                                    => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .content-rollover-layout-list.meta-info-off .post-entry-wrapper' => 'bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_post_meta_style_controls() {
		$this->start_controls_section(
			'post_meta_style_section',
			[
				'label'      => __( 'Meta Information', 'the7mk2' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'post_date',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_terms',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_author',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'post_comments',
							'operator' => '!==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'post_meta_separator',
			[
				'label'       => __( 'Separator Between', 'the7mk2' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '•',
				'placeholder' => '',
				'selectors'   => [
					'{{WRAPPER}} .entry-meta .meta-item:not(:first-child):before' => 'content: "{{VALUE}}";',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_meta',
				'label'          => __( 'Typography', 'the7mk2' ),
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
				'selector'       => '{{WRAPPER}} .entry-meta, {{WRAPPER}} .entry-meta > span',
			]
		);

		$this->add_control(
			'post_meta_font_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .entry-meta > a, {{WRAPPER}} .entry-meta > span'             => 'color: {{VALUE}}',
					'{{WRAPPER}} .entry-meta > a:after, {{WRAPPER}} .entry-meta > span:after' => 'background: {{VALUE}}; -webkit-box-shadow: none; box-shadow: none;',
				],
			]
		);

		$this->add_control(
			'post_meta_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .entry-meta'                                       => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .content-rollover-layout-list .post-entry-wrapper' => 'bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_text_style_controls() {
		$this->start_controls_section(
			'post_text_style_section',
			[
				'label'     => __( 'Text', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'post_content' => 'show_excerpt',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'post_content',
				'label'          => __( 'Typography', 'the7mk2' ),
				'fields_options' => [
					'font_family' => [
						'default' => '',
					],
					'font_size'   => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
					'font_weight' => [
						'default' => '',
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => '',
						],
					],
				],
				'selector'       => '{{WRAPPER}} .entry-excerpt *',
				'condition'      => [
					'post_content!' => 'off',
				],
			]
		);

		$this->add_control(
			'post_content_color',
			[
				'label'     => __( 'Font Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .entry-excerpt' => 'color: {{VALUE}}',
				],
				'condition' => [
					'post_content!' => 'off',
				],
			]
		);

		$this->add_control(
			'post_content_bottom_margin',
			[
				'label'      => __( 'Spacing', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .entry-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'post_content!' => 'off',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_button_style_controls() {
		$this->start_controls_section(
			'button_style_section',
			[
				'label' => __( 'Button', 'the7mk2' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_read_more_button' => 'y',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'button_typography',
				'scheme'    => Schemes\Typography::TYPOGRAPHY_4,
				'selector'  => '{{WRAPPER}} .post-details',
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
			'button_text_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .post-details' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => [
					'type'  => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} article .post-entry-content .post-details.dt-btn'       => 'background: {{VALUE}};',
					'{{WRAPPER}} article .post-entry-content .post-details.dt-btn:hover' => 'background: {{VALUE}};',
					'{{WRAPPER}} article .post-entry-content .post-details.dt-btn:focus' => 'background: {{VALUE}};',
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
			'button_hover_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-details:hover, {{WRAPPER}} .post-details:focus'         => 'color: {{VALUE}};',
					'{{WRAPPER}} .post-details:hover svg, {{WRAPPER}} .post-details:focus svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label'     => __( 'Background Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} article .post-entry-content .post-details.details-type-btn.dt-btn:hover' => 'background: {{VALUE}};',
					'{{WRAPPER}} article .post-entry-content .post-details.details-type-btn.dt-btn:focus' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => __( 'Border Color', 'elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-entry-content .post-details:hover' => 'border-color: {{VALUE}} !important;',
					'{{WRAPPER}} .post-entry-content .post-details:focus' => 'border-color: {{VALUE}} !important;',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'button_border',
				'selector'  => '{{WRAPPER}} .post-details:not(.class-1):not(.class-2):not(.class-3), {{WRAPPER}} .post-details:not(.class-1):not(.class-2):not(.class-3):hover',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .post-details' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_text_padding',
			[
				'label'      => __( 'Text Padding', 'the7mk2' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .post-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function add_filter_bar_style_controls() {
		$this->start_controls_section(
			'filter_bar_style_section',
			[
				'label'      => __( 'Filter Bar', 'the7mk2' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'condition'  => [
					'post_type!' => 'current_query',
				],
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'show_categories_filter',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'show_orderby_filter',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'show_order_filter',
							'operator' => '!==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'filter_position',
			[
				'label'                => __( 'Align', 'the7mk2' ),
				'type'                 => Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'center',
				'options'              => [
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
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .filter'                    => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .filter .filter-categories' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .filter .filter-extras'     => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_style',
			[
				'label'          => __( 'Pointer', 'the7mk2' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => 'default',
				'options'        => [
					'default'     => __( 'Default', 'the7mk2' ),
					'none'        => __( 'None', 'the7mk2' ),
					'underline'   => __( 'Underline', 'the7mk2' ),
					'overline'    => __( 'Overline', 'the7mk2' ),
					'double-line' => __( 'Double Line', 'the7mk2' ),
					'framed'      => __( 'Framed', 'the7mk2' ),
					'background'  => __( 'Background', 'the7mk2' ),
					'text'        => __( 'Text', 'the7mk2' ),
				],
				'style_transfer' => true,
				'condition'      => [
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_control(
			'animation_line',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'     => 'Fade',
					'slide'    => 'Slide',
					'grow'     => 'Grow',
					'drop-in'  => 'Drop In',
					'drop-out' => 'Drop Out',
					'none'     => 'None',
				],
				'condition' => [
					'filter_style'           => [ 'underline', 'overline', 'double-line' ],
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_control(
			'animation_framed',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'    => 'Fade',
					'grow'    => 'Grow',
					'shrink'  => 'Shrink',
					'draw'    => 'Draw',
					'corners' => 'Corners',
					'none'    => 'None',
				],
				'condition' => [
					'filter_style'           => 'framed',
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_control(
			'animation_background',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'                   => 'Fade',
					'grow'                   => 'Grow',
					'shrink'                 => 'Shrink',
					'sweep-left'             => 'Sweep Left',
					'sweep-right'            => 'Sweep Right',
					'sweep-up'               => 'Sweep Up',
					'sweep-down'             => 'Sweep Down',
					'shutter-in-vertical'    => 'Shutter In Vertical',
					'shutter-out-vertical'   => 'Shutter Out Vertical',
					'shutter-in-horizontal'  => 'Shutter In Horizontal',
					'shutter-out-horizontal' => 'Shutter Out Horizontal',
					'none'                   => 'None',
				],
				'condition' => [
					'filter_style'           => 'background',
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_control(
			'animation_text',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grow',
				'options'   => [
					'grow'   => 'Grow',
					'shrink' => 'Shrink',
					'sink'   => 'Sink',
					'float'  => 'Float',
					'skew'   => 'Skew',
					'rotate' => 'Rotate',
					'none'   => 'None',
				],
				'condition' => [
					'filter_style'           => 'text',
					'show_categories_filter' => 'y',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'filter_typography',
				'label'    => __( 'Typography', 'the7mk2' ),
				'selector' => '{{WRAPPER}} .filter a',
			]
		);

		$this->add_control(
			'filter_underline_height',
			[
				'label'      => __( 'Pointer Height', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-border-width: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'filter_style!' => [ 'background', 'none', 'text', 'default' ],
				],
			]
		);

		$this->start_controls_tabs( 'filter_elemenets_style' );

		$this->start_controls_tab(
			'filter_normal_style',
			[
				'label' => __( 'Normal', 'the7mk2' ),
			]
		);

		$this->add_control(
			'navigation_font_color',
			[
				'label'       => __( 'Text Color', 'the7mk2' ),
				'description' => __( 'Leave empty to use headers color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-normal: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_hover_style',
			[
				'label' => __( 'Hover', 'the7mk2' ),
			]
		);

		$this->add_control(
			'filter_hover_text_color',
			[
				'label'       => __( 'Text Color', 'the7mk2' ),
				'description' => __( 'Leave empty to use headers color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-hover: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'filter_hover_pointer_color',
			[
				'label'       => __( 'Pointer Color', 'the7mk2' ),
				'description' => __( 'Leave empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-color-hover: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_active_style',
			[
				'label' => __( 'Active', 'the7mk2' ),
			]
		);

		$this->add_control(
			'filter_active_text_color',
			[
				'label'       => __( 'Text Color', 'the7mk2' ),
				'description' => __( 'Leave empty to use headers color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-title-color-active: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'filter_active_pointer_color',
			[
				'label'       => __( 'Pointer Color', 'the7mk2' ),
				'description' => __( 'Leave empty to use accent color.', 'the7mk2' ),
				'type'        => Controls_Manager::COLOR,
				'alpha'       => true,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-color-active: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'filter_bg_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .filter.filter-decorations *' => '--filter-pointer-bg-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'condition'  => [
					'filter_style' => 'background',
				],
			]
		);

		$this->add_control(
			'filter_element_padding',
			[
				'label'      => __( 'Padding', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .filter .filter-categories a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-by'           => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-sorting'      => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'filter_element_margin',
			[
				'label'      => __( 'Margin', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .filter .filter-categories a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-by'           => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					'{{WRAPPER}} .filter .filter-sorting'      => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'gap_below_category_filter',
			[
				'label'       => __( 'Spacing', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .filter' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'condition'   => [
					'post_type!' => 'current_query',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function add_pagination_style_controls() {
		$this->start_controls_section(
			'pagination_style_tab',
			[
				'label'     => __( 'Pagination', 'the7mk2' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'loading_mode' => [ 'standard', 'js_pagination', 'js_more' ],
				],
			]
		);

		$this->add_control(
			'pagination_position',
			[
				'label'                => __( 'Align', 'the7mk2' ),
				'type'                 => Controls_Manager::CHOOSE,
				'toggle'               => false,
				'default'              => 'center',
				'options'              => [
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
				'selectors_dictionary' => [
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				],
				'selectors'            => [
					'{{WRAPPER}} .paginator' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pagination_style',
			[
				'label'          => __( 'Pointer', 'the7mk2' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => 'underline',
				'options'        => [
					'none'        => __( 'None', 'the7mk2' ),
					'underline'   => __( 'Underline', 'the7mk2' ),
					'overline'    => __( 'Overline', 'the7mk2' ),
					'double-line' => __( 'Double Line', 'the7mk2' ),
					'framed'      => __( 'Framed', 'the7mk2' ),
					'background'  => __( 'Background', 'the7mk2' ),
					'text'        => __( 'Text', 'the7mk2' ),
				],
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'pagination_animation_line',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'     => 'Fade',
					'slide'    => 'Slide',
					'grow'     => 'Grow',
					'drop-in'  => 'Drop In',
					'drop-out' => 'Drop Out',
					'none'     => 'None',
				],
				'condition' => [
					'pagination_style' => [ 'underline', 'overline', 'double-line' ],
					'loading_mode!'    => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_animation_framed',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'    => 'Fade',
					'grow'    => 'Grow',
					'shrink'  => 'Shrink',
					'draw'    => 'Draw',
					'corners' => 'Corners',
					'none'    => 'None',
				],
				'condition' => [
					'pagination_style' => 'framed',
					'loading_mode!'    => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_animation_background',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade',
				'options'   => [
					'fade'                   => 'Fade',
					'grow'                   => 'Grow',
					'shrink'                 => 'Shrink',
					'sweep-left'             => 'Sweep Left',
					'sweep-right'            => 'Sweep Right',
					'sweep-up'               => 'Sweep Up',
					'sweep-down'             => 'Sweep Down',
					'shutter-in-vertical'    => 'Shutter In Vertical',
					'shutter-out-vertical'   => 'Shutter Out Vertical',
					'shutter-in-horizontal'  => 'Shutter In Horizontal',
					'shutter-out-horizontal' => 'Shutter Out Horizontal',
					'none'                   => 'None',
				],
				'condition' => [
					'pagination_style' => 'background',
					'loading_mode!'    => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_animation_text',
			[
				'label'     => __( 'Animation', 'the7mk2' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grow',
				'options'   => [
					'grow'   => 'Grow',
					'shrink' => 'Shrink',
					'sink'   => 'Sink',
					'float'  => 'Float',
					'skew'   => 'Skew',
					'rotate' => 'Rotate',
					'none'   => 'None',
				],
				'condition' => [
					'pagination_style' => 'text',
					'loading_mode!'    => 'js_more',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'label'    => __( 'Typography', 'the7mk2' ),
				'selector' => '{{WRAPPER}} .paginator a, {{WRAPPER}} .paginator .button-load-more',
				'exclude'  => [
					'text_transform',
					'text_decoration',
				],
			]
		);

		$this->add_control(
			'pagination_underline_height',
			[
				'label'      => __( 'Pointer Height', 'the7mk2' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .paginator' => '--filter-pointer-border-width: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'pagination_style!' => [ 'background', 'none' ],
				],
			]
		);

		$this->start_controls_tabs( 'pagination_elements_style' );

		$this->start_controls_tab(
		    'pagination_normal_style',
		    [
		        'label' => __( 'Normal', 'the7mk2' ),
		    ]
		);

		$this->add_control(
			'pagination_text_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator' => '--filter-title-color-normal: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pagination_pointer_normal_color',
			[
				'label'     => __( 'Pointer', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator' => '--filter-pointer-bg-color-normal: {{VALUE}};',
				],
				'condition' => [
					'loading_mode' => 'js_more',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
		    'pagination_hover_style',
		    [
		        'label' => __( 'Hover', 'the7mk2' ),
		    ]
		);

		$this->add_control(
			'pagination_text_hover_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator a' => '--filter-title-color-hover: {{VALUE}}',
				],
			]
		);

		$this->add_control(
		    'pagination_pointer_hover_color',
		    [
		        'label'     => __( 'Pointer', 'the7mk2' ),
		        'type'      => Controls_Manager::COLOR,
		        'alpha'     => true,
		        'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator a' => '--filter-pointer-bg-color-hover: {{VALUE}};',
				],
		    ]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_active_style',
			[
				'label'     => __( 'Active', 'the7mk2' ),
				'condition' => [
					'loading_mode!' => 'js_more',
				],
			]
		);

		$this->add_control(
			'pagination_text_active_color',
			[
				'label'     => __( 'Text Color', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator a' => '--filter-title-color-active: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pagination_pointer_active_color',
			[
				'label'     => __( 'Pointer', 'the7mk2' ),
				'type'      => Controls_Manager::COLOR,
				'alpha'     => true,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .paginator a' => '--filter-pointer-bg-color-active: {{VALUE}};',
					'{{WRAPPER}} .paginator a.button-load-more' => '--filter-pointer-bg-color-active: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'pagination_bg_border_radius',
			[
				'label'      => __( 'Border Radius', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .paginator' => '--filter-pointer-bg-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'condition'  => [
					'pagination_style' => 'background',
				],
			]
		);

		$this->add_control(
			'pagination_element_padding',
			[
				'label'      => __( 'Padding', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .paginator a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'pagination_element_margin',
			[
				'label'      => __( 'Margin', 'the7mk2' ),
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
				'selectors'  => [
					'{{WRAPPER}} .paginator a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'gap_before_pagination',
			[
				'label'       => __( 'Spacing', 'the7mk2' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'unit' => 'px',
					'size' => '',
				],
				'size_units'  => [ 'px' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'selectors'   => [
					'{{WRAPPER}} .paginator' => 'margin-top: {{SIZE}}{{UNIT}}',
				],
				'condition'   => [
					'loading_mode' => [ 'standard', 'js_pagination', 'js_more' ],
				],
			]
		);

		$this->end_controls_section();
	}
}
