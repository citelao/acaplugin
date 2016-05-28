<?php
namespace Acaplugin\Types;

class Songs {

	private $type;

	
	// Song
	// - title
	// - arranger(s)
	// - original singer(s)
	// - group
	// - date sung
	function __construct() {
		$this->type = new Type( 'song', 'songs', array(
			'metadata' => array(
				'title' => 'Song Metadata',
				'fields' => array(
					'artists' => array(
						'name' => 'Original Artist(s)',
						'type' => 'text',
						'repeatable' => true
					)
				)
			),
			'group' => array(
				'title' => 'Group Information',
				'fields' => array(
					'group' => array(
						'name' => 'Group',
						'description' => 'TODO: dropdown of groups',
						'type' => 'title'
					),
					'arrangers' => array(
						'name' => 'Arranger(s)',
						'type' => 'text',
						'repeatable' => true
					)
				)
			)
		),
		array( 
			'description' => 'Songs that a group sang, sings, or plans on singing',
			'icon' => 'dashicons-format-audio',
			'supports' => array('title', 'author', 'revisions')
		) );

		add_action( 'enter_title_here', array( $this, 'edit_title' ) );
	}

	public function edit_title( $title ) {
		if ( get_post_type() == 'song' ) {
			$title = 'Enter song title here';
		}

		return $title;
	}
}