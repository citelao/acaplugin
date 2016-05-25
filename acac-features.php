<?php
/*
 Plugin Name: ACAC Featureset
 Plugin URI: TODO (github)
 Description: This internal plugin adds all the content features necessary to 
 	run the ACAC site.
 Version: 0.0
 Author: Ben Stolovitz
 Author URI: http://ben.stolovitz.com
 License: Proprietary
 */

require 'vendor/autoload.php';

// Init
add_action('init', 'aca_init');
function aca_init() {
	Acaplugin\Types::hella();
}