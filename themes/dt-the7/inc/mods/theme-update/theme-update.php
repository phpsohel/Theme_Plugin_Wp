<?php
/**
 * Theme update functions.
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Presscore_Modules_ThemeUpdateModule', false ) ) :

	class Presscore_Modules_ThemeUpdateModule {

		const PAGE_ID = 'the7-dashboard';

		public static function execute() {
			if ( ! ( defined( 'THE7_PREVENT_THEME_UPDATE' ) && THE7_PREVENT_THEME_UPDATE ) ) {
				add_filter( 'pre_set_site_transient_update_themes', array(
					__CLASS__,
					'pre_set_site_transient_update_themes'
				) );
			}

			// Backup lang files.
			add_filter( 'upgrader_pre_install', array( __CLASS__, 'backup_lang_files' ), 10, 2 );
			add_filter( 'upgrader_post_install', array( __CLASS__, 'restore_lang_files' ), 10, 3 );

			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'admin_notices', array( __CLASS__, 'registration_admin_notice' ), 1 );
			add_filter( 'pre_update_site_option_the7_purchase_code', array( __CLASS__, 'check_for_empty_code' ), 10, 2 );

			if ( ! defined( 'THE7_IGNORE_THEME_DOWNLOAD_REQUIREMENTS' ) || ! THE7_IGNORE_THEME_DOWNLOAD_REQUIREMENTS ) {
				add_filter(
					'upgrader_pre_download',
					array( __CLASS__, 'upgrader_pre_download_theme_requirements_filter' ),
					10,
					3
				);
			}

			if ( ! class_exists( 'The7_Install', false ) ) {
				include dirname( __FILE__ ) . '/class-the7-install.php';
			}

			The7_Install::init();

			if ( ! class_exists( 'The7_Registration_Warning', false ) ) {
				include dirname( __FILE__ ) . '/class-the7-registration-warning.php';
			}

			add_action( 'admin_notices', array( 'The7_Registration_Warning', 'add_admin_notices' ) );
			add_action( 'the7_after_theme_deactivation', array( 'The7_Registration_Warning', 'dismiss_admin_notices' ) );
			add_action( 'the7_after_theme_registration', array( 'The7_Registration_Warning', 'setup_registration_warning' ) );
		}

		/**
		 * Setup page hooks.
		 */
		public static function setup_hooks( $page ) {
			add_action( 'load-' . $page, array( __CLASS__, 'update_settings' ) );
		}

		public static function register_settings() {
			register_setting( 'the7_theme_registration', 'the7_purchase_code', array( __CLASS__, 'theme_activation_action' ) );
		}

		/**
		 * Theme registration action.
		 *
		 * @param $code
		 *
		 * @return string
		 */
		public static function theme_activation_action( $code ) {
			$code = trim( $code );

			if ( isset( $_POST['register_theme'] ) ) {
				$code = self::register_action( $code );
			} else if ( $_POST['deregister_theme'] ) {
				$code = self::de_register_action();
			}
			do_action( 'the7_theme_activation_action' );
			return $code;
		}

		public static function check_for_empty_code( $val = false, $old_val = false ) {
			if ( ! $val && $val === $old_val ) {
				add_settings_error( 'the7_theme_registration', 'update_errors', __( 'Purchase code is not valid.', 'the7mk2' ) , 'error inline the7-dashboard-notice' );
			}

			return $val;
		}

		protected static function register_action( $code ) {
			if ( ! $code ) {
				presscore_deactivate_theme();
				self::check_for_empty_code();
				return '';
			}

			$the7_remote_api = new The7_Remote_API( $code );

			$the7_remote_api_response = $the7_remote_api->register_purchase_code();
			if ( is_wp_error( $the7_remote_api_response ) ) {
				add_settings_error( 'the7_theme_registration', 'update_errors', $the7_remote_api_response->get_error_message() , 'error inline the7-dashboard-notice' );
				return '';
			}

			presscore_activate_theme();

			// Refresh transients.
			delete_site_transient( 'update_themes' );
			do_action( 'wp_update_themes' );

			if ( class_exists( 'Presscore_Modules_TGMPAModule' ) ) {
				Presscore_Modules_TGMPAModule::delete_plugins_list_cache();
			}

			do_action( 'the7_after_theme_registration', $the7_remote_api_response );

			return $code;
		}

		protected static function de_register_action() {
			$code = presscore_get_purchase_code();

			$the7_remote_api = new The7_Remote_API( $code );

			$the7_remote_api_response = $the7_remote_api->de_register_purchase_code();
			if ( is_wp_error( $the7_remote_api_response ) ) {
				add_settings_error( 'the7_theme_registration', 'update_errors', $the7_remote_api_response->get_error_message() , 'error inline the7-dashboard-notice' );
				return $code;
			}

			presscore_deactivate_theme();
			add_settings_error( 'the7_theme_registration', 'update_success', __( 'Purchase code successfully de-registered.', 'the7mk2' ) , 'updated inline the7-dashboard-notice' );

			if ( class_exists( 'Presscore_Modules_TGMPAModule' ) ) {
				Presscore_Modules_TGMPAModule::delete_plugins_list_cache();
			}

			return '';
		}

		public static function update_settings() {
			if ( ! isset( $_POST['option_page'] ) || 'the7_theme_registration' !== $_POST['option_page'] ) {
				return;
			}

			if ( ! isset( $_POST['action'] ) || 'update' !== $_POST['action'] ) {
				return;
			}

			if ( ! current_user_can( 'edit_theme_options' ) ) {
				return;
			}

			check_admin_referer( 'the7_theme_registration-options' );

			global $new_whitelist_options;
			$options = $new_whitelist_options['the7_theme_registration'];

			foreach ( $options as $option ) {
				$option = trim( $option );
				$value = null;
				if ( isset( $_POST[ $option ] ) ) {
					$value = $_POST[ $option ];
					if ( ! is_array( $value ) ) {
						$value = trim( $value );
					}
					$value = wp_unslash( $value );
				}

				update_site_option( $option, $value );
			}

			/**
			 * Handle settings errors.
			 */
			set_transient('settings_errors', get_settings_errors(), 30);

			$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
			wp_redirect( $goback );
			exit;
		}

		/**
		 * Adjust update_themes site transient to honor theme update from Envato server.
		 *
		 * @param $transient
		 *
		 * @return mixed
		 */
		public static function pre_set_site_transient_update_themes( $transient ) {
			if ( ! presscore_theme_is_activated() ) {
				return $transient;
			}

			$code            = presscore_get_purchase_code();
			$the7_remote_api = new The7_Remote_API( $code );

			// Check The7 version.
			$response = $the7_remote_api->check_theme_update();
			if ( is_wp_error( $response ) || ! isset( $response['version'] ) ) {
				return $transient;
			}

			$theme_template  = get_template();
			$current_version = wp_get_theme( $theme_template )->get( 'Version' );
			$new_version     = $response['version'];
			$item            = [
				'theme'        => $theme_template,
				'new_version'  => $current_version,
				'url'          => presscore_theme_update_get_changelog_url(),
				'package'      => '',
				'requires'     => isset( $response['requires'] ) ? $response['requires'] : '',
				'requires_php' => isset( $response['requires_php'] ) ? $response['requires_php'] : '',
			];

			if ( version_compare( $current_version, $new_version, '<' ) ) {
				$item['package']                        = $the7_remote_api->get_theme_download_url( $new_version );
				$item['new_version']                    = $new_version;
				$transient->response[ $theme_template ] = $item;
			} else {
				$transient->no_update[ $theme_template ] = $item;
			}

			return $transient;
		}

		/**
		 * Backup files from language dir to temporary folder in uploads.
		 */
		public static function backup_lang_files( $res = true, $hook_extra = array() ) {
			if ( is_wp_error( $res ) || ! isset( $hook_extra['theme'] ) || 'dt-the7' !== $hook_extra['theme'] ) {
				return $res;
			}

			$upload_dir = wp_get_upload_dir();
			$from = get_template_directory() . '/languages';
			$to = $upload_dir['basedir'] . '/the7-language-tmp';

			if ( wp_mkdir_p( $to ) ) {
				copy_dir( $from, $to, array( 'the7mk2.pot', 'the7mk2.mo' ) );
			}

			return $res;
		}

		/**
		 * Restore stored language files.
		 */
		public static function restore_lang_files( $res = true, $hook_extra = array(), $result = array() ) {
			/**
			 * @var $wp_filesystem WP_Filesystem_Base
			 */
			global $wp_filesystem;

			if ( is_wp_error( $res ) || ! isset( $hook_extra['theme'] ) || 'dt-the7' !== $hook_extra['theme'] ) {
				return $res;
			}

			$upload_dir = wp_get_upload_dir();
			$from = $upload_dir['basedir'] . '/the7-language-tmp';
			$to = get_template_directory() . '/languages';

			// Proceed only if both copy and destination folders exists.
			if ( $wp_filesystem->exists( $from ) && $wp_filesystem->exists( $to ) ) {
				$copy_result = copy_dir( $from, $to );

				// Remove backup.
				if ( ! is_wp_error( $copy_result ) ) {
					$wp_filesystem->delete( $from, true );
				}
			}

			return $res;
		}

		public static function registration_admin_notice() {
			if ( presscore_theme_is_activated() ) {
				return;
			}

			include( dirname( __FILE__ ) . '/views/html-notice-registration.php' );
		}

		/**
		 * @param mixed          $return
		 * @param string         $package
		 * @param Theme_Upgrader $upgrader
		 *
		 * @return bool|WP_Error
		 */
		public static function upgrader_pre_download_theme_requirements_filter( $return, $package, $upgrader ) {
			if ( $return !== false ) {
				return $return;
			}

			if ( ! is_a( $upgrader, 'Theme_Upgrader' ) ) {
				return $return;
			}

			$theme = get_template();
			$themes_updates = get_site_transient( 'update_themes' );
			if ( ! isset( $themes_updates->response[ $theme ] ) ) {
				return $return;
			}

			$the7_remote_api = new The7_Remote_API( presscore_get_purchase_code() );
			if ( strpos( $package, $the7_remote_api->get_theme_download_url() ) === false ) {
				return $return;
			}

			$validation_result = self::validate_theme_requirements( $themes_updates->response[ $theme ] );
			if ( is_wp_error( $validation_result ) ) {
				return $validation_result;
			}

			return $return;
		}

		/**
		 * @param array $the7_update_data
		 *
		 * @return bool|WP_Error
		 */
		public static function validate_theme_requirements( $the7_update_data ) {
			$the7_update_data = wp_parse_args(
				$the7_update_data,
				array(
					'requires'     => '',
					'requires_php' => '',
				)
			);

			$wp_is_compatible  = is_wp_version_compatible( $the7_update_data['requires'] );
			$php_is_compatible = is_php_version_compatible( $the7_update_data['requires_php'] );

			if ( ! $wp_is_compatible && ! $php_is_compatible ) {
				return new WP_Error(
					'the7_need_to_update_wp_and_php',
					sprintf(
					/* translators: 1: WP version, 2: PHP version */
						_x(
							'Error: Current WordPress and PHP versions do not meet minimum requirements for The7. The latest theme version requires WP %1$s and PHP %2$s.',
							'admin',
							'the7mk2'
						),
						$the7_update_data['requires'],
						$the7_update_data['requires_php']
					)
				);
			}

			if ( ! $wp_is_compatible ) {
				return new WP_Error(
					'the7_requires_wp_upadate',
					sprintf(
					/* translators: %s: WP version */
						_x(
							'Error: The minimum supported WP version for The7 is %s. Please upgrade.',
							'admin',
							'the7mk2'
						),
						$the7_update_data['requires']
					)
				);
			}

			if ( ! $php_is_compatible ) {
				return new WP_Error(
					'the7_requires_php_upadate',
					sprintf(
					/* translators: %s: PHP version */
						_x(
							'Error: The minimum supported PHP version for The7 is %s. Please upgrade.',
							'admin',
							'the7mk2'
						),
						$the7_update_data['requires_php']
					)
				);
			}

			return true;
		}
	}

	Presscore_Modules_ThemeUpdateModule::execute();

endif;

if ( ! function_exists( 'presscore_theme_update_get_changelog_url' ) ) :

	function presscore_theme_update_get_changelog_url() {
		return 'http://the7.io/changelog/';
	}

endif;
