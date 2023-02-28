<?php

class CVLanguages {
    public static array $available_languages = array(
        'lv' => 'Latvian',
        'ru_RU' => 'Russian',
        'de_DE' => 'German',
        'en_US' => 'English',
    );

    public function __construct() {
	    add_shortcode( 'cvgenerator_language_selector', [$this, 'language_selector_shortcode'] );
    }

	public static function language_field() {
		$languages = get_option( 'cvgenerator_languages', array() );
		// when adding new ones, check correct language code https://wpastra.com/docs/complete-list-wordpress-locale-codes/

        foreach ( self::$available_languages as $language_code => $language_name ) {
            $checked = in_array( $language_code, $languages ) ? 'checked' : '';
            echo "<label><input type='checkbox' name='cvgenerator_languages[]' value='$language_code' $checked> $language_name</label><br>";
        }
	}

	public static function sanitize_language_code( $languages ) {
		$sanitized = array();
		foreach ( $languages as $language ) {
			if (!preg_match( '/^[a-zA-Z_-]+$/i', $language ) || !in_array( $language, array_keys(self::$available_languages) ) ) {
                add_settings_error('cvgenerator_languages', 'Invalid language code', __('invalid_language_code', 'cv-generator'), array());
                break;
			}
            $sanitized[] = $language;
		}
		return $sanitized;
	}

	/**
	 * Returns array of language codes and names
	 *
	 * @return array
	 */
	public static function get_cv_enabled_languages() {
		$language_codes = get_option( 'cvgenerator_languages', array() );
		$available_languages = array();
		foreach ( $language_codes as $language_code ) {
			$display_name = ucfirst(locale_get_display_language( $language_code, get_user_locale() ));
			$available_languages[ $language_code ] = $display_name;
		}
		return $available_languages;
	}

	/**
	 * Load the translation files for the plugin.
	 */
	function cv_generator_load_textdomain() {
		$mo_file = dirname( __FILE__ ) . '/languages/cv-generator-' . get_user_locale() . '.mo';
		load_textdomain( 'cv-generator', $mo_file);
	}

	public function validate_language_code($code) {
		$available_language_codes = array_keys($this->get_cv_enabled_languages());
		return in_array($code, $available_language_codes);
	}

	public function set_user_language($code) {
		update_user_meta( get_current_user_id(), 'locale', $code );
	}

	public function change_language_if_post_requested() {
		// if server request is post and language code is set
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['cvgenerator_language'] ) ) {
			$code = sanitize_text_field( $_POST['cvgenerator_language'] );
			if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'])) {
				wp_die(__('Nonce incorrect!', 'cv-generator'));
			}
			if ($this->validate_language_code($code)) {
				$this->set_user_language( $code );
			}
		}
	}

	public function language_selector_shortcode() {
		ob_start();
		$language_codes = $this->get_cv_enabled_languages();
		$current_language = get_user_locale();

		?>
		<form method="post" action="" style="float: right;">
			<select name="cvgenerator_language" id="cvgenerator_language" onchange="this.form.submit()">
				<?php foreach ( $language_codes as $key => $language ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_language, $key ); ?>><?php echo esc_html( $language ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php wp_nonce_field(); ?>
		</form>
		<?php
		return ob_get_clean();
	}
}