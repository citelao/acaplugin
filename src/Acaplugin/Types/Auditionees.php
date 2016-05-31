<?php
namespace Acaplugin\Types;

class Auditionees {

	private $type;

	// Auditionee
	// - name (first, last)
	// - email
	// - date added/year
	// - phone #
	// - schedule conflicts, each day
	// - callback groups
	// - preferences
	// - final group
	// - key
	//
	// Filters:
	// - has callback/# callbacks
	// - has conflicts?
	// - preffed a certain group

	function __construct() {
		$this->type = new Type( 'auditionee', 'auditionees', array(
			'personal_info' => array(
				'title' => 'Personal Info',
				'fields' => array(
					'first_name' => array(
						'name' => 'First Name',
						'type' => 'text'
					),
					'last_name' => array(
						'name' => 'Last Name',
						'type' => 'text'
					),
					'email' => array(
						'name' => 'Email',
						'type' => 'text_email'
					),
					'telephone' => array(
						'name' => 'Telephone Number',
					    'type' => 'text',
					    'attributes' => array(
					    	'type' => 'tel'
						)
				    )
			    )
			),
			'conflicts' => array(
				'title' => 'Conflicts'
			),
			'group' => array(
				'title' => 'Group Selection',
				'fields' => array(
					'callbacks' => array(
						'name' => 'Callback Groups',
						'type' => 'title',
						'description' => 'TODO: a list of all groups calling this person back'
					),
					'preferences' => array(
						'name' => 'Group Preferences',
						'type' => 'title',
						'description' => 'TODO: an ordered list of group preferences'
					),
					'acceptance' => array(
						'name' => 'Accepted Group',
						'type' => 'title',
						'description' => 'TODO: a dropdown to select their final group :)'
					)
				)
			)
		),
		array(
			'columns' => array(
				'email' => array(
					'title' => 'Email',
					'cb' => function( $id ) {
						return get_post_meta( $id, 
							$this->type->get_meta_key( 'email' ), 
							true );
					}
				),
				'telephone' => array(
					'title' => 'Telephone',
					'cb' => function( $id ) {
						return get_post_meta( $id, 
							$this->type->get_meta_key( 'telephone' ), 
							true );
					}
				),
				'callbacks' => array(
					'title' => '# Callbacks',
					'cb' => function( $id ) {
						return 0; // TODO
					}
				),
				'pref_card' => array(
					'title' => 'Pref. Card Status',
					'cb' => function( $id ) {
						// TODO
						$called_back = false;
						$complete = false;

						if ( $called_back ) {
							return ( $complete ) ? 'Complete' : 'Incomplete';
						} else {
							return '--';
						}
					}
				),
				'group' => array(
					'title' => 'Accepted Group',
					'cb' => function( $id ) {
						return '--'; // TODO
					}
				),
			),
			'description' => 'Anyone who tries out for a group',
			'icon' => 'dashicons-smiley',
			'title_column_title' => 'Name',
			'title_column_cb' => function( $id ) { 
				$last = get_post_meta( $id, $this->type->get_meta_key( 'last_name' ), true );
				$first = get_post_meta( $id, $this->type->get_meta_key( 'first_name' ), true );
				if ( !$last ) {
					$last = '--';
				}
				if ( !$first ) {
					$first = '--';
				}
				return "{$last}, {$first}";
			}
		) );
	}
}