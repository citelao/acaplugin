<?php
namespace Acaplugin;

class Types {
	static function init() {
		Types::register_auditionee();
		Types::register_group();
	}

	private static function generate_labels($singular, $plural) {
		$singular = lcfirst($singular);
		$plural = lcfirst($plural);
		$usingular = ucfirst($singular);
		$uplural = ucfirst($plural);
		$labels = array(
			'name' => $uplural,
			'singular_name' => $usingular,
			'menu_name' => $uplural,
			'name_admin_bar' => $usingular,
			'add_new' => 'Add New',
			'add_new_item' => 'Add New ' . $usingular,
			'new_item' => 'New ' . $usingular,
			'edit_item' => 'Edit ' . $usingular,
			'view_item' => 'View ' . $usingular,
			'search_item' => 'Search ' . $uplural,
			'not_found' => 'No ' . $plural . ' found',
			'not_found_in_trash' => 'No ' . $plural . ' found in trash',
			'all_items' => 'All ' . $uplural,
			'archives' => $usingular . ' Archives'
		);

		return $labels;
	}

	private static function register_auditionee() {
		$labels = Types::generate_labels('auditionee', 'auditionees');
		$args = array(
			'labels' => $labels,
			'description' => 'Auditionees are all the people who try out for groups',
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-smiley',
			'query_var' => false,
			// 'capability_type' => 'auditionee'
			'supports' => array('revisions')
		);
		register_post_type('auditionee', $args);
	}

	private static function register_group() {
		$labels = Types::generate_labels('group', 'groups');
		$args = array(
			'labels' => $labels,
			'description' => 'Groups are a cappella groups.',
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-groups',
			'query_var' => false,
			// 'capability_type' => 'auditionee'
			'supports' => array('revisions')
		);
		register_post_type('group', $args);
	}
}