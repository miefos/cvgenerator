<?php

class CVLanguages
{
    // when adding new ones, check correct language code https://wpastra.com/docs/complete-list-wordpress-locale-codes/
    public static array $available_languages = array(
        'lv' => 'Latvian',
        'ru_RU' => 'Russian',
        'de_DE' => 'German',
        'en_US' => 'English',
    );

    public function __construct()
    {
        add_shortcode('cv_generator_language_selector', [$this, 'language_selector_shortcode']);
    }

    public static function language_field()
    {
        $languages = get_option('cv_generator_languages', array());

        foreach (self::$available_languages as $language_code => $language_name) {
            $checked = in_array($language_code, $languages) ? 'checked' : '';
            echo "<label><input type='checkbox' name='cv_generator_languages[]' value='$language_code' $checked> $language_name</label><br>";
        }
    }

    public static function sanitize_language_code($languages)
    {
        $sanitized = array();
        foreach ($languages as $language) {
            if (!preg_match('/^[a-zA-Z_-]+$/i', $language) || !in_array($language, array_keys(self::$available_languages))) {
                add_settings_error('cv_generator_languages', 'Invalid language code', __('invalid_language_code', 'cv-generator'), array());
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
    public static function get_cv_enabled_languages()
    {
        $language_codes = get_option('cv_generator_languages', array());
        $available_languages = array();
        foreach ($language_codes as $language_code) {
            $display_name = ucfirst(locale_get_display_language($language_code, get_user_locale()));
            $available_languages[$language_code] = $display_name;
        }
        return $available_languages;
    }

    /**
     * Load the translation files for the plugin.
     */
    function cv_generator_load_textdomain()
    {
        $mo_file = dirname(__FILE__) . '/languages/cv-generator-' . $this->cv_get_user_locale() . '.mo';
        load_textdomain('cv-generator', $mo_file);
    }

    /**
     * this function returns user locale
     * if user locale is not set in meta data (e.g., when user is not logged in), it will try to use session locale
     * if session locale is not set, then it will use 'en' as default.
     * if session locale is not valid, then it will use 'en' as default.
     *
     * @return string
     */
    function cv_get_user_locale()
    {
        $locale = (get_current_user_id() ? get_user_locale() : $_COOKIE['guest_locale'] ?? null) ?? 'en_US';
        $locale = $this->validate_language_code($locale) ? $locale : 'en_US';
        return $locale;
    }

    public function validate_language_code($code)
    {
        $available_language_codes = array_keys($this->get_cv_enabled_languages());
        return in_array($code, $available_language_codes);
    }

    public function set_user_language($code)
    {
        // if user is logged in, attach language to its meta
        if (get_current_user_id()) {
            update_user_meta(get_current_user_id(), 'locale', $code);
        } else {
        // else set it in cookie 'guest_locale'
            if ($this->validate_language_code($code)) {
                setcookie('guest_locale', $code, time() + (86400 * 30), '/'); // Cookie expires in 30 days
                if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/home-lv/') {
                    // post-25505 - lv
                    // post-25066 - en_US  / RR
                    if ($code == 'lv' && $_SERVER['REQUEST_URI'] == '/') {
                        wp_redirect('https://www.cvstepup.com/home-lv/', 301);
                        exit();
                    }
                    if ($code == 'en_US' && $_SERVER['REQUEST_URI'] == '/home-lv/') {
                        wp_redirect('https://www.cvstepup.com', 301);
                        exit();
                    }
                } else {
                    header("Refresh:0");
                    exit();
                }
            }
        }
    }


    public function change_language_if_post_requested()
    {
        // if server request is post and language code is set
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cv_generator_language'])) {

            $code = sanitize_text_field($_POST['cv_generator_language']);
            if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'])) {
                wp_die(__('Nonce incorrect!', 'cv-generator'));
            }
            if ($this->validate_language_code($code)) {
                $this->set_user_language($code);
                echo $_COOKIE['guest_locale'];
            }

        }
    }

    public function language_selector_shortcode()
    {
        ob_start();
        $language_codes = $this->get_cv_enabled_languages();
        if (count($language_codes) <= 1) return false; // do not show if there is only one language
        $current_language = $this->cv_get_user_locale();

        ?>
        <form method="post" action="" style="text-align:right; display:block;">
            <select name="cv_generator_language" id="cv_generator_language" onchange="this.form.submit()">
                <?php foreach ($language_codes as $key => $language) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($current_language, $key); ?>><?php echo esc_html($language); ?></option>
                <?php endforeach; ?>
            </select>
            <?php wp_nonce_field(); ?>
        </form>
        <?php
        return ob_get_clean();
    }
}