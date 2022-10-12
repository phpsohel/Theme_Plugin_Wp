<?php

/**
 * Will disable elementor kit manager (theme styles).
 *
 * @package The7
 */

namespace The7\Adapters\Elementor;

use Elementor\Plugin as Elementor;
use The7\Elementor\Modules\Kit\The7_Kit;

defined( 'ABSPATH' ) || exit;

/**
 * Class The7_Kit_Manager_Control
 */
class The7_Kit_Manager_Control {

	public function bootstrap() {

		  add_action( 'elementor/init', [ $this, 'disable_elementor_kit_manager' ] );
	}

	public function disable_elementor_kit_manager() {

		$kits_manager = Elementor::instance()->kits_manager;
		if (version_compare(ELEMENTOR_VERSION, "3.0.0", "<")) {
			remove_action( 'elementor/documents/register', [ $kits_manager, 'register_document' ] );
			remove_filter( 'elementor/editor/localize_settings', [ $kits_manager, 'localize_settings' ] );
			remove_filter( 'elementor/editor/footer', [ $kits_manager, 'render_panel_html' ] );
			remove_action( 'elementor/frontend/after_enqueue_global', [
					$kits_manager,
					'frontend_before_enqueue_styles'
				], 0 );
			remove_action( 'elementor/preview/enqueue_styles', [ $kits_manager, 'preview_enqueue_styles' ], 0 );
		}
		else {
			remove_action( 'elementor/documents/register', [ $kits_manager, 'register_document' ] );
			add_action( 'elementor/documents/register', [ $this, 'register_document' ] );
			add_filter( 'elementor/editor/localize_settings', [ $this, 'localize_settings' ], 50 );
		}
	}

	public function register_document( $documents_manager ) {
		require_once __DIR__ . '/modules/kits/class-the7-kit.php';
		$documents_manager->register_document_type( 'kit', The7_Kit::get_class_full_name() );
	}

	public function localize_settings( $settings ) {
		$settings = array_replace_recursive( $settings, [
			'i18n' => [
				'theme_style' => "",
			],
		] );

		return $settings;
	}

}