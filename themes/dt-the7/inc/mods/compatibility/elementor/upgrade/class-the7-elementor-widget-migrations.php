<?php
/**
 * @package The7
 */

namespace The7\Adapters\Elementor\Upgrade;

defined( 'ABSPATH' ) || exit;

abstract class The7_Elementor_Widget_Migrations {

	abstract public static function get_widget_name();

	public static function run( $migration, $updater = null ) {
		if ( $updater === null ) {
			$updater = new \The7\Adapters\Elementor\Upgrade\The7_Elementor_Updater();
		}

		$changes = [
			[
				'callback'    => [ static::class, $migration ],
				'control_ids' => [],
			],
		];
		\Elementor\Core\Upgrade\Upgrade_Utils::_update_widget_settings( static::get_widget_name(), $updater, $changes );
	}

}
