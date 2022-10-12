<?php

namespace The7\Adapters\Elementor\ShortcodeAdapters\Queries;

use The7\Adapters\Elementor\ShortcodeAdapters\Query_Interface;

class Products_Current_Query extends Query_Interface {

	public function parse_query_args() {
		if ( ! is_page( wc_get_page_id( 'shop' ) ) ) {
			$query_args = $GLOBALS['wp_query']->query_vars;
		}

		$page = get_query_var( 'paged', 1 );

		if ( 1 < $page ) {
			$query_args['paged'] = $page;
		}

		$query_args['post_type'] = 'product';

		// load only id and post types fileds
		$query_args['fields'] = [ 'ids', 'post_types' ];

		return $query_args;
	}
}