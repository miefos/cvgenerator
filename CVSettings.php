<?php

require_once "CVLanguages.php";

class CVSettings {
	function __construct() {
		$this->languages = new CVLanguages();
		add_action( 'admin_menu', array( $this, 'adminPage' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}

	function settings() {
		add_settings_section('cv_settings_section', __('Languages', 'cv-generator'), null, 'cv-settings-page');

		add_settings_field('cvgenerator_languages', __('Languages', 'cv-generator'), [ 'CVLanguages', 'language_field' ], 'cv-settings-page', 'cv_settings_section');
		register_setting('cv_settings_group', 'cvgenerator_languages', [ 'CVLanguages', 'sanitize_language_code' ]);

		add_settings_section('payments_section', __('Payments', 'cv-generator'), null, 'cv-settings-page');

		add_settings_field('cvgenerator_stripe_api_key', __('Stripe API key', 'cv-generator'), array($this, 'inputfieldHTML'), 'cv-settings-page', 'payments_section', array('theName' => 'cvgenerator_stripe_api_key'));
		register_setting('cv_settings_group', 'cvgenerator_stripe_api_key', array('sanitize_callback' => 'sanitize_text_field'));

		add_settings_field('cvgenerator_price_1', __('Price for product 1 access, EUR', 'cv-generator'), array($this, 'inputfieldHTML'), 'cv-settings-page', 'payments_section', array('theName' => 'cvgenerator_price_1'));
		register_setting('cv_settings_group', 'cvgenerator_price_1', array('sanitize_callback' => array($this, 'sanitizePrice'), 'sanitize_text_field'));

		add_settings_field('cvgenerator_product_name_1', __("Product name", 'cv-generator'), array($this, 'inputfieldHTML'), 'cv-settings-page', 'payments_section', array('theName' => 'cvgenerator_product_name_1'));
		register_setting('cv_settings_group', 'cvgenerator_product_name_1', array('sanitize_callback' => 'sanitize_text_field'));

		add_settings_field('cvgenerator_product_description_1', __("Product description", 'cv-generator'), array($this, 'textareafieldHTML'), 'cv-settings-page', 'payments_section', array('theName' => 'cvgenerator_product_description_1'));
		register_setting('cv_settings_group', 'cvgenerator_product_description_1', array('sanitize_callback' => 'sanitize_text_field'));
	}

	function sanitizePrice($input) {
		if ($input < 0) {
			add_settings_error('cvgenerator_price_1', 'cvgenerator_price_1_error', __('Price cannot be less than 0', 'cv-generator'));
			return get_option('cvgenerator_price_1');
		}
		return round($input, 2);
	}

	function inputfieldHTML($args) { ?>
		<input type="text" name="<?= $args['theName'] ?>" value="<?= esc_attr(get_option($args['theName'])) ?>" />
	<?php }

	function textareafieldHTML($args) { ?>
		<textarea name="<?= $args['theName'] ?>"><?= esc_attr(get_option($args['theName'])) ?></textarea>
	<?php }

	function adminPage() {
		add_menu_page(
			'CV Generator Settings',
			'CV Generator',
			'manage_options',
			'cvgenerator_settings',
			array( $this,'ourHTML' )
		);
	}

	function ourHTML() { ?>
		<div class="wrap">
			<h1><?=esc_html( get_admin_page_title())?></h1>
			<form action="options.php" method="POST">
				<?php
				settings_errors();
				settings_fields('cv_settings_group');
				do_settings_sections('cv-settings-page');
				do_settings_sections('payments_section');
				submit_button();
				?>
			</form>
		</div>
	<?php }

    public function get_settings() {
	    return [
            'available_languages' => CVLanguages::get_cv_enabled_languages(),
		    'payments' => [
                'price_1' => get_option('cvgenerator_price_1'),
                'product_name_1' => get_option('cvgenerator_product_name_1'),
                'product_description_1' => get_option('cvgenerator_product_description_1'),
                'stripe_api_key' => get_option('cvgenerator_stripe_api_key'),
            ]
        ];
    }
}
