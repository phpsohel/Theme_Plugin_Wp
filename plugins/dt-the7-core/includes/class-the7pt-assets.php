<?php

/**
 * Assets class.
 * @since       1.0.0
 * @package     dt_the7_core
 */
class The7PT_Assets {

	/**
	 * Setup assets.
	 */
	public static function setup() {
		if ( ! defined( 'PRESSCORE_STYLESHEETS_VERSION' ) || version_compare( PRESSCORE_STYLESHEETS_VERSION, '3.7.0' ) < 0 ) {
			return;
		}

		// Enqueue plugin styles and scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 17 );

		// Register dynamic stylesheets.
		add_filter( 'presscore_get_dynamic_stylesheets_list', array( __CLASS__, 'register_dynamic_stylesheet' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public static function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$template_uri = The7PT()->plugin_url() . 'assets';

		if ( class_exists( 'The7_Admin_Dashboard_Settings' ) ) {
			$post_types = array(
				'portfolio',
				'testimonials',
				'team',
				'logos',
				'benefits',
				'albums',
				'slideshow',
			);

			foreach ( $post_types as $post_type ) {
				if ( The7_Admin_Dashboard_Settings::get( $post_type ) ) {
					wp_enqueue_style(
						'the7-core',
						"{$template_uri}/css/post-type{$suffix}.css",
						array(),
						The7PT()->version(),
						'all'
					);
					break;
				}
			}
		}

		if (
			! class_exists( 'The7_Admin_Dashboard_Settings' )
			|| The7_Admin_Dashboard_Settings::get( 'portfolio' )
			|| The7_Admin_Dashboard_Settings::get( 'albums' )
		) {
			wp_enqueue_script( 'the7-core', "{$template_uri}/js/post-type{$suffix}.js", array( 'jquery' ), The7PT()->version(), true );
		}
	}

	/**
	 * Register dynamic stylesheets.
	 *
	 * @param array $dynamic_stylesheets
	 *
	 * @return array
	 */
	public static function register_dynamic_stylesheet( $dynamic_stylesheets ) {
		$dynamic_stylesheets['the7-elements'] = array(
			'path' => The7pt()->plugin_path() . 'assets/css/dynamic/post-type-dynamic.less',
			'src' => 'post-type-dynamic.less',
		);

		return $dynamic_stylesheets;
	}
}