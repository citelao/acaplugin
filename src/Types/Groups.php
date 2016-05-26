<?php
namespace Acaplugin\Types;

class Groups {
	function __construct() {
		add_action('init', array($this, 'init'));
	}

	public function init() {
		$labels = TypeHelpers::generate_labels('group', 'groups');
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
			'supports' => array('title', 'revisions')
		);
		register_post_type('group', $args);
	}
}