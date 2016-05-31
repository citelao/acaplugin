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
		$this->type = bstypes()->create($prefix, 'group', 'groups',
			array( 
				'description' => 'Any a cappella group',
				'icon' => 'dashicons-groups',
				'fields' => array(
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
				'supports' => array('title', 'revisions')
			)
		);
	}
}