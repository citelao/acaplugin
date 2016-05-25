<?php
/*
 Plugin Name: ACAC Featureset
 Plugin URI: TODO (github)
 Description: This internal plugin adds all the content features necessary to run the ACAC site.
 Version: 0.0
 Author: Ben Stolovitz
 Author URI: http://ben.stolovitz.com
 License: Proprietary
 */
namespace Acaplugin;
require_once('src/types.php');

// Init
add_action('init', 'Acaplugin\init');
function init() {
	Types\init();
}