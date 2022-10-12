<?php

namespace The7\Elementor\Modules\Kit;

use Elementor\Core\Kits\Documents\Tabs;
use Elementor\Core\Kits\Documents\Kit;

class The7_Kit extends Kit{
	protected function _register_controls() {
		$this->register_document_controls();
		$tabs = [
			'global-colors' => new Tabs\Global_Colors( $this ),
			'global-typography' => new Tabs\Global_Typography( $this ),
			//'theme-style-typography' => new Tabs\Theme_Style_Typography( $this ),
			//'theme-style-buttons' => new Tabs\Theme_Style_Buttons( $this ),
			//'theme-style-images' => new Tabs\Theme_Style_Images( $this ),
			//'theme-style-form-fields' => new Tabs\Theme_Style_Form_Fields( $this ),
			'settings-site-identity' => new Tabs\Settings_Site_Identity( $this ),
			'settings-background' => new Tabs\Settings_Background( $this ),
			'settings-layout' => new Tabs\Settings_Layout( $this ),
			'settings-lightbox' => new Tabs\Settings_Lightbox( $this ),
			'settings-custom-css' => new Tabs\Settings_Custom_CSS( $this ),
		];
		foreach ( $tabs as $tab ) {
			$tab->register_controls();
		}
	}
}