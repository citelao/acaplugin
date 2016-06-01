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
	public function __construct($prefix) {
		$this->type = bstypes()->create($prefix, 'group', 'groups',
			array( 
				'description' => 'Any a cappella group',
				'icon' => 'dashicons-groups',
				'columns' => array(
					'author' => array( 'title' => 'Added by' )
				),
				'fields' => array(
					'info' => array(
						'title' => 'Group Information',
						'fields' => array(
							'description' => array(
								'name' => 'Description',
								'type' => 'wysiwyg'
							)
						)
					),
					'auditions' => array(
						'title' => 'Auditions',
						'fields' => array(
							'callbacks' => array(
								'name' => 'Callbacks',
								'type' => 'multicheck',
								'options' => array("a" => "Callback is never called"),
								'options_cb' => 'ew_callback_list'
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

function ew_callback_list( $field, $fe ) {
		print($field);
		$options = array(
        'sapphire' => 'Sapphire Blue',
        'sky'      => 'Sky Blue',
        'navy'     => 'Navy Blue',
        'ruby'     => 'Ruby Red',
        'purple'   => 'Amethyst Purple',
    );
		return $options;
	}