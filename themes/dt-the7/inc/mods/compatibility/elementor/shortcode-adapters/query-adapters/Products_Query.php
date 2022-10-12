<?php

namespace The7\Adapters\Elementor\ShortcodeAdapters\Queries;

use The7\Adapters\Elementor\ShortcodeAdapters\Query_Interface;
use The7\Adapters\Elementor\With_Pagination;

class Products_Query extends Query_Interface {

	use With_Pagination;

	public function parse_query_args() {
		$query_args = [
			'post_type'           => 'product',
			'ignore_sticky_posts' => true,
			'orderby'             => $this->get_att( $this->query_prefix . 'orderby' ),
			'order'               => strtoupper( $this->get_att( $this->query_prefix . 'order' ) ),
		];

		$query_args['meta_query'] = WC()->query->get_meta_query();
		$query_args['tax_query'] = [];

		// Visibility.
		$this->set_visibility_query_args( $query_args );

		//Featured.
		$this->set_featured_query_args( $query_args );

		//Sale.
		$this->set_sale_products_query_args( $query_args );

		//Best sellings
		$this->set_best_sellings_products_query_args( $query_args );

		//Top rated
		$this->set_top_rated_products_query_args( $query_args );

		// IDs.
		$this->set_ids_query_args( $query_args );

		// Categories & Tags
		$this->set_terms_query_args( $query_args );

		//Exclude.
		$this->set_exclude_query_args( $query_args );

		$loading_mode = $this->get_att( 'loading_mode', 'disabled' );
		$query_args['posts_per_page'] = intval( $this->get_posts_per_page( $loading_mode, $this->atts ) );
		if ( 'standard' == $loading_mode ) {
			$query_args['paged'] = the7_get_paged_var();
		}

		$query_args = apply_filters( 'the7_woocommerce_widget_products_query', $query_args );

		// load only id and post types fileds
		$query_args['fields'] = [ 'ids', 'post_types' ];

		return $query_args;
	}

	protected function set_visibility_query_args( &$query_args ) {
		$query_args['tax_query'] = array_merge( $query_args['tax_query'], WC()->query->get_tax_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	protected function set_featured_query_args( &$query_args ) {
		if ( 'featured' === $this->get_att( $this->query_prefix . 'post_type' ) ) {
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			$query_args['tax_query'][] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => [ $product_visibility_term_ids['featured'] ],
			];
		}
	}

	protected function set_sale_products_query_args( &$query_args ) {
		if ( 'sale' === $this->get_att( $this->query_prefix . 'post_type' ) ) {
			$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		}
	}

	protected function set_ids_query_args( &$query_args ) {

		switch ( $this->get_att( $this->query_prefix . 'post_type' ) ) {
			case 'by_id':
				$post__in = $this->get_att( $this->query_prefix . 'posts_ids' );
				break;
			case 'sale':
				$post__in = wc_get_product_ids_on_sale();
				break;
		}

		if ( ! empty( $post__in ) ) {
			$query_args['post__in'] = $post__in;
		}
	}

	private function set_terms_query_args( &$query_args ) {

		$query_type = $this->get_att( $this->query_prefix . 'post_type' );

		if ( 'by_id' === $query_type || 'current_query' === $query_type ) {
			return;
		}

		if ( empty( $this->get_att( $this->query_prefix . 'include' ) ) || empty( $this->get_att( $this->query_prefix . 'include_term_ids' ) ) || ! in_array( 'terms', $this->get_att( $this->query_prefix . 'include' ), true ) ) {
			return;
		}

		$terms = [];
		foreach ( $this->get_att( $this->query_prefix . 'include_term_ids' ) as $id ) {
			$term_data = get_term_by( 'term_taxonomy_id', $id );
			$taxonomy = $term_data->taxonomy;
			$terms[ $taxonomy ][] = $id;
		}
		$tax_query = [];
		foreach ( $terms as $taxonomy => $ids ) {
			$query = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_taxonomy_id',
				'terms'    => $ids,
			];

			$tax_query[] = $query;
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = array_merge( $query_args['tax_query'], $tax_query );
		}
	}

	protected function set_exclude_query_args( &$query_args ) {
		if ( empty( $this->get_att( $this->query_prefix . 'exclude' ) ) ) {
			return;
		}
		$post__not_in = [];
		if ( in_array( 'current_post', $this->get_att( $this->query_prefix . 'exclude' ) ) ) {
			if ( is_singular() ) {
				$post__not_in[] = get_queried_object_id();
			}
		}

		if ( in_array( 'manual_selection', $this->get_att( $this->query_prefix . 'exclude' ) ) && ! empty( $this->get_att( $this->query_prefix . 'exclude_ids' ) ) ) {
			$post__not_in = array_merge( $post__not_in, $this->get_att( $this->query_prefix . 'exclude_ids' ) );
		}

		$query_args['post__not_in'] = empty( $query_args['post__not_in'] ) ? $post__not_in : array_merge( $query_args['post__not_in'], $post__not_in );

		/**
		 * WC populates `post__in` with the ids of the products that are on sale.
		 * Since WP_Query ignores `post__not_in` once `post__in` exists, the ids are filtered manually, using `array_diff`.
		 */
		if ( 'sale' === $this->get_att( $this->query_prefix . 'post_type' ) ) {
			$query_args['post__in'] = array_diff( $query_args['post__in'], $query_args['post__not_in'] );
		}

		if ( in_array( 'terms', $this->get_att( $this->query_prefix . 'exclude' ) ) && ! empty( $this->get_att( $this->query_prefix . 'exclude_term_ids' ) ) ) {
			$terms = [];
			foreach ( $this->get_att( $this->query_prefix . 'exclude_term_ids' ) as $to_exclude ) {
				$term_data = get_term_by( 'term_taxonomy_id', $to_exclude );
				$terms[ $term_data->taxonomy ][] = $to_exclude;
			}
			$tax_query = [];
			foreach ( $terms as $taxonomy => $ids ) {
				$tax_query[] = [
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $ids,
					'operator' => 'NOT IN',
				];
			}
			if ( empty( $query_args['tax_query'] ) ) {
				$query_args['tax_query'] = $tax_query;
			} else {
				$query_args['tax_query']['relation'] = 'AND';
				$query_args['tax_query'][] = $tax_query;
			}
		}
	}

	protected function set_best_sellings_products_query_args( &$query_args ) {
		if ( 'best_selling' === $this->get_att( $this->query_prefix . 'post_type' ) ) {
			$query_args['meta_key'] = 'total_sales';
			$query_args['orderby'] = 'meta_value_num';
		}
	}

	protected function set_top_rated_products_query_args( &$query_args ) {
		if ( 'top' === $this->get_att( $this->query_prefix . 'post_type' ) ) {
			add_filter( 'posts_clauses', array( 'WC_Shortcodes', 'order_by_rating_post_clauses' ) );
			$query_args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_args['orderby']  = 'meta_value_num';
		}
	}
}