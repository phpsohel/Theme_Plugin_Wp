<?php

namespace The7\Adapters\Elementor\ShortcodeAdapters;

defined( 'ABSPATH' ) || exit;

use The7\Adapters\Elementor\ShortcodeAdapters\Queries\Products_Current_Query;
use The7\Adapters\Elementor\ShortcodeAdapters\Queries\Products_Query;
use WP_Query;

class DT_Shortcode_Products_Masonry_Adapter extends \DT_Shortcode_ProductsMasonry implements The7_Shortcode_Adapter_Interface {

	use Trait_Elementor_Shortcode_Adapter;

	public function __construct() {
		parent::__construct();
		$prefix = self::QUERY_CONTROL_NAME . '_';
		$default_atts = array(
			$prefix . 'order'            => 'desc',
			$prefix . 'orderby'          => 'date',
			$prefix . 'post_type'        => '',
			$prefix . 'posts_ids'        => '',
			$prefix . 'include'          => '',
			$prefix . 'include_term_ids' => '',
			$prefix . 'include_authors'  => '',
			$prefix . 'exclude'          => '',
			$prefix . 'exclude_ids'      => '',
			$prefix . 'exclude_term_ids' => '',
		);

		$this->default_atts = array_merge( $this->default_atts, $default_atts );
	}

	protected function get_query_args() {
		if ( 'current_query' === $this->get_att( self::QUERY_CONTROL_NAME . '_post_type' ) ) {
			$query = new Products_Current_Query( $this->get_atts(), self::QUERY_CONTROL_NAME . '_' );
		} else {
			$query = new Products_Query( $this->get_atts(), self::QUERY_CONTROL_NAME . '_' );
		}

		return $query->parse_query_args();
	}

	protected function get_posts_filter_terms( $query ) {
		$query = new Products_Query( $this->get_atts(), self::QUERY_CONTROL_NAME . '_' );
		$query_args = $query->parse_query_args();
		$query_args['fields'] = 'ids';
		unset( $query_args['posts_per_page'] );
		unset( $query_args['paged'] );

		$tags = false;
		$product_cat = '';
		$product_exclude_cat = '';
		if ( array_key_exists( 'tax_query', $query_args ) ) {
			foreach ( $query_args['tax_query'] as $id ) {
				if ( ! is_array( $id ) ) {
					continue;
				}
				if ( ! array_key_exists( 'taxonomy', $id ) ) {
					if ( array_key_exists( 0, $id ) ) {
						if ( $id[0]['taxonomy'] === 'product_cat' ) {
							if ( array_key_exists( 'operator', $id[0] ) && $id[0]['operator'] === 'NOT IN' ) {
								$product_exclude_cat = $id[0];
							}
						}
						continue;
					} else {
						continue;
					}
				}

				if ( $id['taxonomy'] !== 'product_visibility' && $id['taxonomy'] !== 'product_cat' ) {
					$tags = true;
				}
				if ( $id['taxonomy'] === 'product_cat' ) {
					if ( array_key_exists( 'operator', $id ) && $id['operator'] === 'NOT IN' ) {
						$product_exclude_cat = $id;
					} else {
						$product_cat = $id;
					}
				}
			}
		}
		$get_terms_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		);

		// If only categories selected.
		if ( ! $tags ) {
			if ( empty( $product_cat ) && empty( $product_exclude_cat ) ) {
				// If empty - return all categories.
				return get_terms( $get_terms_args );
			} else if ( ! empty( $product_cat ) ) {
				$categories = $product_cat['terms'];
				//exclude categories
				if ( ! empty( $product_exclude_cat ) ) {
					$categories = array_diff( $product_cat['terms'], $product_exclude_cat['terms'] );
				}
				if ( ! empty( $categories ) && ! is_numeric( $categories[0] ) ) {
					$get_terms_args['slug'] = $categories;
				} else {
					$get_terms_args['include'] = $categories;
				}
			} else if ( ! empty( $product_exclude_cat ) ) {
				$get_terms_args['exclude'] = $product_exclude_cat['terms'];
			}

			return get_terms( $get_terms_args );
		}

		$posts_query = new WP_Query( $query_args );

		//return corresponded categories.
		return wp_get_object_terms( $posts_query->posts, 'product_cat', array( 'fields' => 'all_with_object_id' ) );
	}
}
