<?php
/**
 * MEC Compatibility class.
 *
 * @package The7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class The7_MEC_Compatibility
 */
class The7_MEC_Compatibility {

	/**
	 * Main function.
	 */
	public function bootstrap() {
		add_action( 'current_screen', array( $this, 'inject_settings' ), 99 );
		add_action( 'mec_save_options', array( $this, 'regenerate_css_on_mec_settings_save' ) );
		if ( self::the7_style_enabled() ) {
			add_filter( 'presscore_get_dynamic_stylesheets_list', array( $this, 'customize_custom_less' ) );
		}
		add_action( 'presscore_setup_less_vars', array( $this, 'add_less_vars' ) );
	}

	/**
	 * Force regenerate theme stylesheets after plugin settings save.
	 */
	public function regenerate_css_on_mec_settings_save() {
		presscore_set_force_regenerate_css( true );
	}

	/**
	 * Import dedicated mec less file in the bottom of custom.less.
	 *
	 * @param array $stylesheets Dynamic stylesheets list.
	 *
	 * @return array
	 */
	public function customize_custom_less( $stylesheets ) {
		if ( isset( $stylesheets['dt-custom']['imports']['dynamic_import_bottom'] ) ) {
			$stylesheets['dt-custom']['imports']['dynamic_import_bottom'][] = 'dynamic-less/event-calendar.less';
		}

		return $stylesheets;
	}

	/**
	 * Add less vars.
	 *
	 * @param The7_Less_Vars_Manager_Interface $less_vars Theme less vars manager.
	 */
	public function add_less_vars( The7_Less_Vars_Manager_Interface $less_vars ) {
		$mec_options = get_option( 'mec_options' );
		if ( is_array( $mec_options ) ) {
			if ( isset( $mec_options['styling']['mec_colorskin'] ) ) {
				$less_vars->add_hex_color( 'mec-colorskin', $mec_options['styling']['mec_colorskin'] );
			}
			if ( isset( $mec_options['styling']['color'] ) ) {
				$less_vars->add_hex_color( 'mec-color', $mec_options['styling']['color'] );
			}
		}
	}

	public function inject_settings() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'm-e-calendar_page_MEC-settings' ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', array( $this, 'the7_settings_injection_scripts' ) );
	}

	/**
	 * @return bool
	 */
	public static function the7_style_enabled() {
		if ( ! method_exists( 'MEC', 'getInstance' ) ) {
			return false;
		}

		$main = MEC::getInstance( 'app.libraries.main' );
		if ( ! method_exists( $main, 'get_options' ) ) {
			return false;
		}

		$options = $main->get_options();

		return ! isset( $options['styling']['use_the7_style'] ) || ! empty( $options['styling']['use_the7_style'] );
	}

	public function the7_settings_injection_scripts() {
		?>
		<script type="text/javascript">
            jQuery(function ($) {
                $("#mec_styling_form").append(<?php echo json_encode( $this->get_the7_settings_html() ) ?>);
            });
		</script>
		<?php
	}

	/**
	 * @return false|string
	 */
	protected function get_the7_settings_html() {
		ob_start();
		?>
		<h4 class="mec-form-subtitle">The7</h4>
		<div class="mec-form-row">

			<label class="mec-col-3" for="mec_the7_style"><?php esc_html_e( 'Use The7 style', 'the7mk2' ) ?></label>
			<div class="mec-col-8">
				<input type="hidden" name="mec[styling][use_the7_style]" value="0">
				<input value="1" type="checkbox" id="mec_the7_style" name="mec[styling][use_the7_style]" <?php checked(
					self::the7_style_enabled()
				) ?>>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}
