<?php
namespace Acaplugin\Types;

class Groups {

	private $type;

	// Group
	// - name
	// - callback auditionees
	// - members
	// - songs

	// In order to only let groups see their own group, I've set it so any
	// author can only view *their* authored groups. If you want a specific
	// user to be able to edit their group (ie auditionees), all you have to
	// do is make that user the *author* of that specific group.
	//
	// Then only admins and them can see it and edit it.
	public function __construct($prefix) {
		$this->type = bstypes()->create($prefix, 'group', 'groups',
			array( 
				'description' => 'Any a cappella group',
				'icon' => 'dashicons-groups',
				'columns' => array(
					'author' => array( 'title' => 'Added by' ),
					'title' => array( 'title' => 'Title' )
				),
				'fields' => array(
					// 'info' => array(
					// 	'title' => 'Group Information',
					// 	// 'fields' => array(
					// 	// 	'description' => array(
					// 	// 		'name' => 'Description',
					// 	// 		'type' => 'wysiwyg'
					// 	// 	)
					// 	// )
					// ),
					'auditions' => array(
						'title' => 'Auditions',
						'fields' => array(
							'callbacks' => array(
								'name' => 'Callbacks',
								'type' => 'custom_attached_posts',
								'options' => array(
									'filter_boxes' => true,
									'query_args' => array(
										'post_type' => 'acac_auditionee',
									)
								)
							)
						)
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
				'supports' => array('title', 'revisions', 'author')
			)
		);
	}
}
