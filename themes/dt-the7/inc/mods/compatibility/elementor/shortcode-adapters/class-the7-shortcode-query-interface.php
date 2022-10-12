<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.04.2020
 * Time: 8:56
 */

namespace The7\Adapters\Elementor\ShortcodeAdapters;

abstract class Query_Interface {


	protected $atts;
	protected $query_prefix;

	function __construct( $atts, $query_prefix ) {
		$this->atts = $atts;
		$this->query_prefix = $query_prefix;
	}

	/**
	 * Return $att_name attribute value or default one if empty.
	 *
	 * @param string $att_name
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_att( $att_name, $default = null ) {
		if ( array_key_exists( $att_name, $this->atts ) && '' !== $this->atts[ $att_name ] ) {
			return $this->atts[ $att_name ];
		}

		if ( ! is_null( $default ) ) {
			return $default;
		}

		return '';
	}

	abstract function parse_query_args();
}