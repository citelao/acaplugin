<?php
namespace Acaplugin\Types;

function init() {
	register_auditionee();
}

function register_auditionee() {
	$args = array(
		'label' => 'Auditionee',
		'description' => 'Auditionees are all the people who try out for groups',
		'public' => false,
		'exclude_from_search' => true,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-smiley',
		// 'capability_type' => 'auditionee'
		// 'query_var'          => true,
		// 'rewrite'            => array( 'slug' => 'book'),
		
		// 'has_archive'        => true,
		// 'menu_position'      => null,
		'supports' => array('revisions')
	);
	register_post_type('auditionee', $args);
}