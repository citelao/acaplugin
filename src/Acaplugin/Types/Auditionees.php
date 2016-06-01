<?php
namespace Acaplugin\Types;

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

class Auditionees {

	private $type;

	function __construct($prefix) {
		$this->type = bstypes()->create($prefix, 'auditionee', 'auditionees',
			array(
				'columns' => array(
					'email' => array(
						'title' => 'Email',
						'cb' => function( $id ) {
							return $this->type->get( 'email' );
						}
					),
					'telephone' => array(
						'title' => 'Telephone',
						'cb' => function( $id ) {
							return $this->type->get( 'telephone' );
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
				'fields' => array(
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
				'icon' => 'dashicons-smiley',
				'supports' => array('revisions'),
				'title_column_title' => 'Name',
				'title_column_cb' => function( $id ) { 
					$last = $this->type->get( 'last_name' );
					$first = $this->type->get( 'first_name' );
					if ( !$last ) {
						$last = '--';
					}
					if ( !$first ) {
						$first = '--';
					}
					return "{$last}, {$first}";
				}
			)
		);
	}
}