<?php
require_once("cvgenerator.php");

class CVGeneratorAuthentication
{
    private $otp_length = 8;
    private $expire_time = "+2 hours";

    public function __construct()
    {
        global $wpdb;
	    add_shortcode( 'register_or_login', [$this, 'cv_generator_register_login_shortcode_html'] );
        add_action('activate_' . CVGEN_PLUGIN_NAME, [$this, 'onActivate']);

        $this->nonce_name = 'wp_rest';
	    $this->table_name_otp = $wpdb->prefix . 'cv_generator_otp';
        $this->api_endpoint_verify_email_and_send_otp = ['cv_generator/auth/', 'send_otp'];
        $this->api_endpoint_verify_otp = ['cv_generator/auth/', 'attempt_otp'];

        // max 6 auth related requests per 60 seconds
        $this->max_auth_attempts = 6;
        $this->throttle_time = 60;

        // register REST API endpoints
	    add_action( 'rest_api_init', function () {
		    register_rest_route( $this->api_endpoint_verify_email_and_send_otp[0], $this->api_endpoint_verify_email_and_send_otp[1], array(
			    'methods' => 'POST',
			    'callback' => [$this, 'validate_email_and_send_otp'],
		    ) );

		    register_rest_route( $this->api_endpoint_verify_otp[0], $this->api_endpoint_verify_otp[1], array(
			    'methods' => 'POST',
			    'callback' => [$this, 'validate_otp_and_login'],
		    ) );
	    } );

        // hide admin bar
        add_action('after_setup_theme', function () {
	        if (!current_user_can('administrator') && !is_admin()) {
		        show_admin_bar(false);
	        }
        });

        // change logout redirect url
	    add_action('wp_logout', function () {
            wp_safe_redirect( home_url() );
            exit;
        });
    }

    // this function returns array which is parsed into JSON and sent to frontend
    function data_to_javascript() {
	    return [
            'data' => [
                'is_logged_in' => is_user_logged_in(),
                'nonce' => wp_create_nonce($this->nonce_name),
                'nonce_name' => $this->nonce_name,

                'waiting_time_until_can_be_resent' => 30,
                'waiting_time_until_info_about_can_be_resent_is_shown' => 30,
                'api' => [
                  'verify_email_and_send_otp' => get_rest_url() . $this->api_endpoint_verify_email_and_send_otp[0] . $this->api_endpoint_verify_email_and_send_otp[1],
                  'verify_otp' =>  get_rest_url() . $this->api_endpoint_verify_otp[0] . $this->api_endpoint_verify_otp[1],
                ],
                'translations' => [
	                'email_label' => __('Email', 'cv_generator'),
	                'wait_until_can_be_resent' => __('You can resend code in ', 'cv_generator'),
	                'resend_label' => __('Resend code', 'cv_generator'),
	                'otp_label' => __('Received code', 'cv_generator'),
	                'submit_email' => __('Send OTP code', 'cv_generator'),
	                'submit_attempt_email_otp' => esc_attr__('Authenticate', 'cv_generator'),
                ]
            ]
	    ];
    }

    function validate_otp_and_login($data) {
	    if (!$this->throttle_check()) {
		    return ['status' => "fail", 'msg' => __('Too many requests. Please try again later.', 'cv_generator')];
	    }

	    if ($result = $this->nonce_or_email_invalid($data)) {
		    return $result;
	    }

	    $email = $data['email'];
	    $email = is_email(sanitize_email($email));

        $otp = $data['otp'];
        $otp = intval(substr($otp, 0, $this->otp_length));
        if (!$this->otp_valid($otp, $email)) {
	        return ['status' => 'fail', 'msg' => __('OTP code is invalid', 'cv_generator')];
        }

        return ['status' => 'ok', 'msg' => __('Authentication successful', 'cv_generator')];
    }

	function otp_valid($otp, $email) {
		$email = is_email(sanitize_email($email));
		$user_id = get_user_by('email', $email)->ID;
		if (!$user_id) {
			return false;
		}

        global $wpdb;

		$query = $wpdb->prepare(
			"SELECT otp.otp, otp.ID, otp.user_id
         FROM $this->table_name_otp as otp
         WHERE 
             otp.otp = %d AND 
             otp.used_at IS NULL AND
             otp.deactivated = 0 AND
             otp.expire_at > UTC_TIMESTAMP AND
             otp.user_id = %d
         ORDER BY otp.created_at DESC
         ", $otp, $user_id);

        $row = $wpdb->get_row($query);

        if (!$row || !($row->otp == $otp)) { // the second scenario should not happen in any circumstances
            return false;
        }

        $wpdb->update($this->table_name_otp, ['used_at' => cv_generator_mysql_time(time())], ['ID' => $row->ID]);

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        return true;
	}

    function throttle_check() {
        // start session if not started
        if (!session_id()) {
            session_start();
        }

	    // check if the user has exceeded the maximum number of OTP generation attempts
	    if (isset($_SESSION['auth_attempts']) && $_SESSION['auth_attempts'] >= $this->max_auth_attempts) {
		    // check if the user has waited long enough to try again
		    if (isset($_SESSION['auth_last_attempt']) && time() - $_SESSION['auth_last_attempt'] < $this->throttle_time) {
			    return false;
		    } else {
                $_SESSION['auth_attempts'] = 0;
                $_SESSION['auth_last_attempt'] = null;
                return true;
            }
	    }

	    // set throttle in session variable
	    $_SESSION['auth_attempts'] = isset($_SESSION['auth_attempts']) ? $_SESSION['auth_attempts'] + 1 : 1;
	    $_SESSION['auth_last_attempt'] = time();
        return true;
    }

    function validate_email_and_send_otp($data) {
        if (!$this->throttle_check()) {
	        return ['status' => "fail", 'msg' => __('Too many requests. Please try again later.', 'cv_generator')];
        }

        if ($result = $this->nonce_or_email_invalid($data)) {
            return $result;
        }

	    $email = $data['email'];
	    $email = is_email(sanitize_email($email));

	    if ($result = $this->send_otp($email)) {
		    return $result;
	    } else {
		    return ['status' => "fail", 'msg' => __("OTP code could not be sent. Please contact system administrator.", 'cv_generator')];
        }
    }

    function send_otp($email) {
        $email = is_email(sanitize_email($email));
        if (email_exists($email)) {
            return $this->send_otp_to_registered_user($email);
        } else {
            return $this->register_user_and_send_otp($email);
        }
    }

    function send_otp_to_registered_user($email) {
	    $email = is_email(sanitize_email($email));
	    $user_id = get_user_by('email', $email)->ID;
	    if (!$user_id) {
		    return ['status' => "fail", 'msg' => __("Email is not registered. Please contact system administrator.", 'cv_generator')];
	    }

	    if (!($otp = $this->insert_new_otp($user_id))) {
		    return ['status' => "fail", 'msg' => __("Error generating OTP code. Please contact system administrator.", 'cv_generator')];
        }

	    if ($this->sendEmail($email, __("Login attempt", 'cv_generator'), __("Someone tried to login. Your OTP code is ", 'cv_generator') . "<br /><h2 style='letter-spacing: 2px;'>" . $otp . "</h2>")) {
		    return ['status' => 'ok', 'msg' => __("Login attempt, mail sent", "cv_generator")];
	    } else {
		    return ['status' => 'fail', 'msg' => __("Error sending OTP code. Please contact system administrator.", "cv_generator")];
	    }
    }

    function register_user_and_send_otp($email) {
	    $email = is_email(sanitize_email($email));
        if (email_exists($email)) {
	        return ['status' => "fail", 'msg' => __("Email already registered. Please contact system administrator.", 'cv_generator')];
        }

	    $username = sanitize_user($this->get_random_unique_username($email));
	    $pass = wp_generate_password(24, true, true);

	    $user_id = wp_create_user($username, $pass, $email);

        if (!($otp = $this->insert_new_otp($user_id))) {
	        return ['status' => "fail", 'msg' => __("Error generating OTP code. Please contact system administrator.", 'cv_generator')];
        }

	    if (is_wp_error($user_id)) {
		    return ['status' => "fail", 'msg' => __("Error registering your user. Please contact system administrator.", 'cv_generator')];
	    } else {
		    if ($this->sendEmail($email, __("Registration successful", 'cv_generator'), __("The registration is successful. Your OTP code is ", 'cv_generator') . "<br /><h2 style='letter-spacing: 2px;'>" . $otp . "</h2>")) {
			    return ['status' => "ok", 'msg' => __("OTP code sent to your email.", 'cv_generator')];
		    } else {
			    return ['status' => "fail", 'msg' => __("Error sending OTP code to your email. Please contact system administrator.", 'cv_generator')];
		    }
        }
    }

    function nonce_or_email_invalid($data) {
	    // Validate nonce
	    if (!isset($data[$this->nonce_name]) && !wp_verify_nonce($data[$this->nonce_name], $this->nonce_name)) {
		    return ['status' => "fail", 'msg' => __('Invalid nonce', 'cv_generator')];
	    }

	    // Set email
	    if (!isset($data['email'])) {
		    return ['status' => "fail", 'msg' => __('Email not set', 'cv_generator')];
	    }

	    $email = $data['email'];
	    $email = is_email(sanitize_email($email));

	    // validate email
	    if (!$email) {
		    return ['status' => "fail", 'msg' => __('Email format invalid', 'cv_generator')];
	    }

        return false;
    }

    function insert_new_otp($user_id) {
	    global $wpdb;

        // deactivate previous user's OTP codes
	    if (get_user_by('ID', intval($user_id))) {
		    if ($wpdb->update($this->table_name_otp, ['deactivated' => 1], ['user_id' => $user_id]) === false) {
			    return false;
		    }
	    }

        // generate new code
	    $otp = $this->generate_numeric_otp();
        $data = [
	        'otp' => $otp,
	        'created_at' => cv_generator_mysql_time(time()),
	        'expire_at' => cv_generator_mysql_time(strtotime($this->expire_time)),
	        'user_id' => intval( $user_id )
        ];

	    if ($wpdb->insert($this->table_name_otp, $data) !== 1) {
		    return false;
	    }

	    return $otp;
    }

    /**
     * Function that returns HTML for login
     *
     * @return false|string
     */
    function cv_generator_register_login_shortcode_html() {
        if (!is_user_logged_in()) {
            if (!is_admin()) {
                wp_enqueue_style("cv_generator_auth_frontend_style", plugin_dir_url(__FILE__) . 'dist-vue/assets/index.css', [], '1.0');
                wp_enqueue_style("cv_generator_auth_frontend_style_primeflex", "https://unpkg.com/primeflex@^3/primeflex.css", [], '1.0');
                wp_enqueue_script( "cv_generator_auth_frontend_react", plugin_dir_url( __FILE__ ) . 'dist-vue/assets/index.js');
            }

            ob_start(); ?>

            <div class="flex justify-content-center flex-wrap" id="cv_generator" data-js="<?= esc_attr(wp_json_encode($this->data_to_javascript())) ?>"></div>

            <?php
            return ob_get_clean();
        } else {
            return "<a href='" . wp_logout_url() . "'>" . __("Logout", "cv_generator") . "</a>";
        }
    }

	/**
	 * Function to generate a secure OTP
	 */
	function generate_numeric_otp() {
		$generator = "123456789"; // allowed characters for OTP
		$result = "";
		$timestamp = time(); // get current Unix timestamp in seconds
		$unique_id = uniqid(mt_rand(), true); // generate a unique identifier

        // combine timestamp and unique identifier and hash with SHA-256
		$hash = hash('sha256', $timestamp.$unique_id); // example: a4d4b2dd3ceae3898d2da0ff21f688aa8d0f505864ad2396010aa649c1705f14

        // Convert hexadecimal string to binary
		$binhash = hex2bin($hash);

        // loop to generate OTP digits
		for ($i = 1; $i <= $this->otp_length; $i++) {
			// use random_int() function to generate a cryptographically secure random number
			$index = random_int(0, strlen($binhash) - 1);
			$result .= $generator[ord($binhash[$index]) % strlen($generator)];
		}

		return intval($result);
	}

	/**
	 * Function that generates random `username`
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	function get_random_unique_username($email, string $prefix = '' ){
		$prefix .= substr(strstr($email, '@', true), 0, 5); // from namename@user.com would return namen (the first five chars)
		do {
			$rnd_str = sprintf("%06d", mt_rand(1, 999999));
			$user_exists = username_exists( $prefix . $rnd_str );
		} while( $user_exists > 0 );
		return $prefix . $rnd_str;
	}

    // body is used in template
    function sendEmail($to_email, $subject, $body) {
        $signature = get_bloginfo('title') . "<br /> <a href='". get_bloginfo('url') . "'>" . get_bloginfo('url') . "</a>";
	    ob_start();
	    include(CVGEN_PLUGIN_DIR . '/assets/email-templates/otp_code.php');
	    $email_content = ob_get_contents();
	    ob_end_clean();
	    $headers = array('Content-Type: text/html; charset=UTF-8');
	    return wp_mail($to_email, $subject, $email_content, $headers);
    }

	/**
	 * Setup plugin
	 *
	 * @return void
	 */
	function onActivate() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// create OTP table.
		// user_id - used for existing users
		dbDelta("CREATE TABLE $this->table_name_otp (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned,
            otp int(6) NOT NULL,
            expire_at datetime NOT NULL,
            created_at datetime NOT NULL,
            used_at datetime,
            deactivated tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            FOREIGN KEY  (user_id) REFERENCES $wpdb->users(id)
        )");

		// validate that tables were created
		// if not present in db, then deactivate plugin and die with error
		$table_show_otp = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->table_name_otp ) );

		if ($wpdb->get_var($table_show_otp) !== $this->table_name_otp) {
			deactivate_plugins(CVGEN_PLUGIN_NAME);
			$referer_url = wp_get_referer();
			wp_die("There was error setting up the plugin. Plugin deactivated. <br /><a href='$referer_url'>Back</a>");
		}
	}
}