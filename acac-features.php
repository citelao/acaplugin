<?php
/*
Plugin Name: Acaplugin
Plugin URI: https://github.com/citelao/acaplugin
Description: Add all content features necessary to run the ACAC site.
Version: 0.0
Author: Ben Stolovitz
Author URI: http://ben.stolovitz.com
License: Proprietary
*/

if( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	die( 'In order for Acaplugin to work, you must install its dependencies. '
		. 'Please run the `make` command in ' . __DIR__ );
}

/**
 * Register types
 */
add_action('bstypes_init', 'acaplugin_register_types');
function acaplugin_register_types() {
	$prefix = 'acac';
	$auditionees = new Acaplugin\Types\Auditionees($prefix);
	$groups = new Acaplugin\Types\Groups($prefix);
	$songs = new Acaplugin\Types\Songs($prefix);
}

// add export functionality
// add audition admin features for webmaster