<?php
namespace Acaplugin\Types;

class Auditionees {
	function __construct() {
		add_action('init', array($this, 'init'));
		add_action('cmb2_admin_init', array($this, 'metabox'));
	}

	public function init() {
		$labels = TypeHelpers::generate_labels('auditionee', 'auditionees');
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

	public function metabox() {
		$cmb = new_cmb2_box(array(
			'id' => 'acac_auditionee_metabox',
			'title' => 'Personal Info',
			'object_types' => array('auditionee'),
			// 'priority' => 'high',
			// 'show_names' => true
		));

		$cmb->add_field(array(
			'name' => 'First Name',
		    'id' => '_acac_auditionee_first_name',
		    'type' => 'text'
		));

		$cmb->add_field(array(
			'name' => 'Last Name',
		    'id' => '_acac_auditionee_last_name',
		    'type' => 'text'
		));
	}
}