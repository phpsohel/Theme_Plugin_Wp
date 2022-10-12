<?php

namespace The7\Adapters\Elementor\ShortcodeAdapters;

defined( 'ABSPATH' ) || exit;

use The7\Adapters\Elementor\ShortcodeAdapters\Queries\Products_Current_Query;
use The7\Adapters\Elementor\ShortcodeAdapters\Queries\Products_Query;

class DT_Shortcode_Products_Carousel_Adapter extends \DT_Shortcode_Products_Carousel implements The7_Shortcode_Adapter_Interface {

	use Trait_Elementor_Shortcode_Adapter;

	public function __construct() {
		parent::__construct();
		$prefix = self::QUERY_CONTROL_NAME . '_';
		$default_atts = array(
			$prefix . 'order'             => 'desc',
			$prefix . 'orderby'           => 'date',
			$prefix . 'post_type'         => '',
			$prefix . 'posts_ids'         => '',
			$prefix . 'include'           => '',
			$prefix . 'include_term_ids'  => '',
			$prefix . 'include_authors'   => '',
			$prefix . 'exclude'           => '',
			$prefix . 'exclude_ids' => '',
			$prefix . 'exclude_term_ids'  => '',
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
}
