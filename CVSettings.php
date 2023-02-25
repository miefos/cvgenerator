<?php

require_once "CVLanguages.php";

class CVSettings {
	public function __construct() {
        $this->languages = new CVLanguages();
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu_page() {
		add_menu_page(
			'CV Generator Settings',
			'CV Generator',
			'manage_options',
			'cvgenerator_settings',
			array( 'CVLanguages', 'settings_page' )
		);
	}


	public function register_settings() {
		register_setting( 'cvgenerator_settings', 'cvgenerator_languages', [ 'CVLanguage', 'sanitize_language_code' ] );
	}

    /**
     * Returns all settings
     */
    public function get_settings() {
	    return [
            'available_languages' => CVLanguages::get_available_languages(),
        ];
    }
}
