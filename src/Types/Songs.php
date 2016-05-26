<?php
namespace Acaplugin\Types;

class Songs {
	function __construct() {
		add_action('init', array($this, 'init'));
		add_action('enter_title_here', array($this, 'edit_title'));
	}

	public function init() {
		$labels = TypeHelpers::generate_labels('song', 'songs');
		$args = array(
			'labels' => $labels,
			'description' => 'Songs are songs that groups have or will sing',
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-format-audio',
			'query_var' => false,
			// 'capability_type' => 'auditionee'
			'supports' => array('title', 'author', 'revisions')
		);
		register_post_type('song', $args);
	}

	public function edit_title($title) {
		if(get_post_type() == 'song') {
			$title = 'Enter song title here';
		}

		return $title;
	}
}