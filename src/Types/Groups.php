<?php
namespace Acaplugin\Types;

class Groups {

	private $type;


	// Group
	// - name
	// - description
	// - tags?
	// - callback auditionees
	// - members
	// - songs
	function __construct() {
		$this->type = new Type( 'group', 'groups', array(
			'info' => array(
				'title' => 'Group Information'
			),
			'auditions' => array(
				'title' => 'Auditions'
			),
			'songs' => array(
				'title' => 'Songs',
				'fields' => array(
					'song_list' => array(
						'name' => 'Song List',
						'type' => 'title',
						'description' => 'TODO: just list all the songs'
					)
				)
			)
		),
		array( 
			'description' => 'Any a cappella group',
			'icon' => 'dashicons-groups',
			'supports' => array('title', 'revisions')
		) );
	}
}