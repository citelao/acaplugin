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

$prefix = 'acac';

/**
 * Register types
 */
add_action( 'bstypes_init', 'acaplugin_register_types' );
function acaplugin_register_types() {
	global $prefix;

	$auditionees = new Acaplugin\Types\Auditionees($prefix);
	$groups = new Acaplugin\Types\Groups($prefix);
	$songs = new Acaplugin\Types\Songs($prefix);

	Acaplugin\Options\Config::get_instance($prefix);
}

add_action( 'p2p_init', 'acaplugin_register_connection_types' );
function acaplugin_register_connection_types() {
	global $prefix;

    p2p_register_connection_type( array(
        'name' => 'group_callbacks',
        'from' => BSTypes_Util::get_type_id($prefix, 'group'),
        'to' => BSTypes_Util::get_type_id($prefix, 'auditionee'),
        'admin_column' => 'to',
        'admin_dropdown' => 'to',
        'to_labels' => array(
        	'column_title' => 'Callback Groups',
        	'dropdown_title' => 'Any callback group'
        )
    ) );

    p2p_register_connection_type( array(
        'name' => 'group_songs',
        'from' => BSTypes_Util::get_type_id($prefix, 'group'),
        'to' => BSTypes_Util::get_type_id($prefix, 'song')
    ) );
}

// add export functionality
