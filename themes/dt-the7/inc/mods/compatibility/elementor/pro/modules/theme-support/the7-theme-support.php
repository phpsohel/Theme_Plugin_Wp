<?php

namespace The7\Adapters\Elementor\Pro\ThemeSupport;

use Elementor\Plugin;
use ElementorPro\Modules\ThemeBuilder\Documents\Footer;
use ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager;
use The7_Elementor_Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class The7_Theme_Support {

	/**
	 * @param Locations_Manager $manager
	 */
	public function register_locations( $manager ) {
		$manager->register_core_location( 'header' );
		$manager->register_core_location( 'footer' );
	}

	public function overwrite_config_base_init() {
		$header_id = The7_Elementor_Compatibility::get_document_id_for_location( 'header' );
		if ( $header_id ) {
			presscore_config_populate_header_options( $header_id );
			add_filter( 'presscore_before_main_container', [ $this, 'do_header' ], 17 );
		}

		$footer_id = The7_Elementor_Compatibility::get_document_id_for_location( 'footer' );
		if ( $footer_id ) {
			presscore_config()->set( 'template.bottom_bar.enabled', false );
			add_filter( 'presscore_replace_footer', '__return_true' );
			add_action( 'presscore_before_footer_widgets', [ $this, 'do_footer' ], 0 );
			add_action( 'presscore_footer_html_class', static function ( $output ) {
				$output[] = 'elementor-footer';

				return $output;
			} );
		}
	}

	public function do_header() {
		elementor_theme_do_location( 'header' );
	}

	public function do_footer() {
		elementor_theme_do_location( 'footer' );
	}

	/**
	 * Alter current page value with archive template id in the theme config.
	 *
	 * @param int|null $page_id
	 *
	 * @return int|null|false
	 */
	public static function config_page_id_filter( $page_id = null ) {
		if ( is_singular() ) {
			$document = Plugin::instance()->documents->get_doc_for_frontend( get_the_ID() );
			if ( $document && $document::get_property( 'support_wp_page_templates' ) ) {
				$wp_page_template = $document->get_meta( '_wp_page_template' );
				if ( $wp_page_template && 'default' !== $wp_page_template ) {
					return $page_id;
				}
			}
		}

		return The7_Elementor_Compatibility::get_applied_archive_page_id( $page_id );
	}

	public function __construct() {
		add_action( 'elementor/theme/register_locations', [ $this, 'register_locations' ] );
		add_action( 'presscore_config_base_init', [ $this, 'overwrite_config_base_init' ] );
		add_filter( 'presscore_config_post_id_filter', [ $this, 'config_page_id_filter' ], 5 );
	}
}
