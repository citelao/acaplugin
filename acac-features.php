<?php
/*
Plugin Name: Acaplugin: the ACAC Featureset
Plugin URI: TODO (github)
Description: Add all content features necessary to run the ACAC site.
Version: 0.0
Author: Ben Stolovitz
Author URI: http://ben.stolovitz.com
License: Proprietary
*/

namespace Acaplugin;

if( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	die( 'In order for Acaplugin to work, you must install its dependencies. '
		. 'Please run the `make` command in ' . __DIR__ );
}

/**
 * Register types
 */
new Types\Auditionees();
new Types\Groups();
new Types\Songs();