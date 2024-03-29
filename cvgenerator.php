<?php
/**
 * Plugin Name: CV Generator
 * Description: This plugin has 2 parts: authentication with OTP and CV generator
 */


/**
 * Plugin Name: CV generator plugin
 * Description: CV generator plugin
 * Version: 0.1
 * Author: MX
 * Text Domain: cv-generator
 **/
require_once __DIR__ . '/vendor/autoload.php';
require_once "CVGeneratorAuthentication.php";
require_once "CVPostType.php";
require_once "CVSettings.php";
require_once "CVLanguages.php";
require_once "CVStripePayment.php";

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly.*/
}
const CVGEN_PLUGIN_NAME = 'cvgenerator/cvgenerator.php';
define("CVGEN_PLUGIN_DIR", dirname( __FILE__ ));
const CVGEN_UPLOAD_DIR = CVGEN_PLUGIN_DIR . '/uploads';
const CVGEN_ASSETS_DIR = CVGEN_PLUGIN_DIR . '/assets';
const CVGEN_VIDEO_DIR = CVGEN_UPLOAD_DIR . '/video';
const CVGEN_REST_PAYMENT_API_URL = [ 'cv_generator/cvpost', '/pay' ];
const CVGEN_REST_WEBHOOK_URL = [ 'cv_generator/cvpost', '/webhook-stripe' ];
define( "CVGEN_HOME_URL", home_url( '/cv-generator/' ) );

function dd( ...$args ) {
	foreach ( $args as $arg ) {
		dump( $arg );
	}
	die();
}

function cv_generator_mysql_time( $unix_timestamp ) {
	return wp_date( "Y-m-d H:i:s", $unix_timestamp );
}

class cv_generator {
	public function __construct() {
        add_action('activate_' . CVGEN_PLUGIN_NAME, [$this, 'onActivate']);
        add_action( 'plugins_loaded', function () {
			$this->settings = new CVSettings();
            $this->settings->languages->change_language_if_post_requested();
            $this->settings->languages->cv_generator_load_textdomain();
            
			$this->cv_stripe_payment = new CVStripePayment($this->settings);

			$this->cv_generator_auth = new CVGeneratorAuthentication($this->settings);
			$this->cv_post_type = new CVPostType($this->settings);
        });
	}

    function onActivate() {
        CVGeneratorAuthentication::onActivate(ABSPATH, CVGEN_PLUGIN_NAME);
        CVPostType::onActivate(ABSPATH, CVGEN_PLUGIN_NAME);
    }
}

new cv_generator();
/*
	function menu_lv_shortcode() { 
	    ob_clean();
    	ob_start();
    	
		$current_language = get_user_locale();
		global $wp_query;
		$post_id = $wp_query->post->ID;
		
		//echo $post_id;'
    	if ($current_language=='lv' && $post_id == "25066") {
    		echo "redirec-";    		

    		$url = get_permalink( "25505" );
			wp_redirect($url, '301');
			exit();
		}
	}
	add_shortcode('LV', 'menu_lv_shortcode', 100);
*/