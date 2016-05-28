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

// register types
// add metaboxes per type
// add columns per type
// add export functionality
// add audition admin features for webmaster

// Auditionee
// - name (first, last)
// - email
// - date added/year
// - phone #
// - schedule conflicts, each day
// - callback groups
// - preferences
// - final group
// - key
//
// Cols:
// - name
// - phone?
//
// Filters:
// - has callback/# callbacks
// - has conflicts?
// - preffed a certain group

// Group
// - name
// - description
// - tags?
// - callback auditionees
// - members
// - songs

// Song
// - title
// - arranger(s)
// - original singer(s)
// - group
// - date sung