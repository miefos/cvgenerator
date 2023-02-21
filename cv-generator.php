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
 **/
require __DIR__ . '/vendor/autoload.php';
require "CVGeneratorAuthentication.php";
require "CVPostType.php";

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if accessed directly.*/
}
const CVGEN_PLUGIN_NAME = 'cv-generator/cv-generator.php';
define("CVGEN_PLUGIN_DIR", dirname( __FILE__ ));

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
		$this->cv_generator_auth       = new CVGeneratorAuthentication();
		$this->cv_post_type = new CVPostType();
	}
}

new cv_generator();
