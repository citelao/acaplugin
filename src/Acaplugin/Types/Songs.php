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
	function __construct($prefix) {
		$this->type = bstypes()->create($prefix, 'song', 'songs',
			array(
				'columns' => array(
					'arrangers' => array(
						'title' => 'Arranger(s)',
						'cb' => function( $id ) {
							$arrangers = $this->type->get( $id, 'arrangers' );

							if ( ! $arrangers ) {
								return '--';
							}
							return join( ', ', $arrangers );
						}
					),
					'artists' => array(
						'title' => 'Artist(s)',
						'cb' => function( $id ) {
							$artists = $this->type->get( $id, 'artists' );

							if ( ! $artists ) {
								return '--';
							}
							return join( ', ', $artists );
						}
					),
					'author' => array( 'title' => 'Added by' ),
					'title' =>  array( 'title' => 'Title' )
				),
				'description' => 'Songs that a group sang, sings, or plans on singing',
				'icon' => 'dashicons-format-audio',
				'fields' => array(
					'metadata' => array(
						'title' => 'Song Metadata',
						'fields' => array(
							'artists' => array(
								'name' => 'Original Artist(s)',
								'type' => 'text',
								'repeatable' => true,
								// 'attributes' => array(
								// 	'required' => 'required'
								// )
							)
						)
					),
					'group' => array(
						'title' => 'Group Information',
						'fields' => array(
							'group' => array(
								'name' => 'Group',
								'type' => 'bs_one_to_many',
								'description' => 'Which group is singing this?',
								'options' => array(
									'connection' => 'group_songs'
								),
								'attributes' => array(
									'required' => 'required'
								)
							),
							'reserver' => array(
								'name' => 'Reserver',
								'type' => 'text',
								'description' => 'Who\'s reserving this song for the group?',
								'attributes' => array(
									'required' => 'required'
								)
							),
							'arrangers' => array(
								'name' => 'Arranger(s)',
								'type' => 'text',
								'repeatable' => true,
								// 'attributes' => array(
								// 	'required' => 'required'
								// )
							)
						)
					)
				),
				'supports' => array('title', 'revisions', 'author')
			)
		);

		add_action( 'enter_title_here', array( $this, 'edit_title' ) );
	}

	public function edit_title( $title ) {
		if ( get_post_type() == 'song' ) {
			$title = 'Enter song title here';
		}

		return $title;
	}
}