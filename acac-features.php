<?php
/*
Plugin Name: Acaplugin
Plugin URI: https://github.com/citelao/acaplugin
Description: Add all content features necessary to run the ACAC site.
Version: 1.0
Author: Ben Stolovitz
Author URI: http://ben.stolovitz.com
License: Proprietary
*/

if( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	die( 'In order for Acaplugin to work, you must install its dependencies. '
		. 'Please run the `make` command in ' . __DIR__ . "\n");
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

	Acaplugin\Frontend\Registration::get_instance($prefix);
	Acaplugin\Frontend\Prefs::get_instance($prefix);
}

// TODO Refactor into BSTypes. This features shows me that the API *needs*
// to be `add_field()`, `add_column()` etc. This should not be exposed like
// this.
add_action( 'p2p_init', 'acaplugin_register_connection_types' );
function acaplugin_register_connection_types() {
	global $prefix;

	p2p_register_connection_type( array(
		'name' => 'group_callbacks',
		'from' => BSTypes_Util::get_type_id($prefix, 'group'),
		'to' => BSTypes_Util::get_type_id($prefix, 'auditionee'),
		// 'admin_column' => 'to',
		// 'admin_dropdown' => false,
		'admin_box' => false,
		'to_labels' => array(
			'column_title' => 'Callback Groups',
			'dropdown_title' => 'Any callback group'
		)
	) );

	// p2p_register_connection_type( array(
	// 	'name' => 'group_preferences',
	// 	'from' => BSTypes_Util::get_type_id($prefix, 'auditionee'),
	// 	'to' => BSTypes_Util::get_type_id($prefix, 'group'),
	// 	'admin_column' => 'from',
	// 	'admin_dropdown' => 'from',
	// 	'admin_box' => array(
	// 		'show' => 'any',
	// 		'context' => 'normal'
	// 	),
	// 	'from_labels' => array(
	// 		'column_title' => 'Group Preferences',
	// 		'dropdown_title' => 'Any pref-ed group'
	// 	)
	// ) );

	// TODO replace old group acceptance list with this!
	// p2p_register_connection_type( array(
	// 	'name' => 'group_members',
	// 	'from' => BSTypes_Util::get_type_id($prefix, 'group'),
	// 	'to' => BSTypes_Util::get_type_id($prefix, 'auditionee'),
	// 	'cardinality' => 'one-to-many',
	// 	'admin_column' => 'to',
	// 	'admin_dropdown' => 'to',
	// 	'to_labels' => array(
	// 		'column_title' => 'Accepted Group',
	// 		'dropdown_title' => 'Any accepted group'
	// 	)
	// ) );

	p2p_register_connection_type( array(
		'name' => 'group_songs',
		'from' => BSTypes_Util::get_type_id($prefix, 'group'),
		'to' => BSTypes_Util::get_type_id($prefix, 'song'),
		'cardinality' => 'one-to-many',
		'admin_column' => 'to',
		'admin_dropdown' => 'to',
		'admin_box' => false,
		'to_labels' => array(
			'column_title' => 'Group',
			'dropdown_title' => 'Any group'
		)
	) );
}

// add export functionality
