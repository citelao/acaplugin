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
		// TODO this system will make certain pre-entered data UNVIEWABLE
		// on the server if you change callback dates *during* auditions.
		// I would disable/hide the field on the admin page if I could.
		$conflicts = array(
			'conflicts_desc' => array(
				'name' => 'Callback conflicts',
				'type' => 'title',
				'description' => 'Please write any (potential) conflicts you have on these dates. We use this to help plan your callback schedule.'
			)
		);
		$callback_dates = get_option( 'acac_config' )['callback_dates'];
		foreach ( $callback_dates as $key => $date ) {
			$date = strtotime($date);

			$nice_date = date('l, F j', $date);

			$conflicts['conflict-' . date('m-d', $date)] = array(
				'name' => 'Conflicts on ' . $nice_date,
				'type' => 'textarea'
			);
		}

		$stage = get_option( 'acac_config' )['stage'];
		$groups = array();

		$groups['auditioned_groups'] = array(
			'name' => 'Auditioned Groups',
			'type' => 'multicheck',
			'description' => 'Which groups is this person auditioning for?',
			'options_cb' => 'Acaplugin\Util::get_groups_multicheck'
		);

		// Only show callbacking groups if callbacks have started or we're
		// in draft.
		if( $stage == 'callbacks' || $stage == 'draft' ) {
			$groups['callbacks'] = array(
				'name' => 'Callback Groups',
				'type' => 'title',
				'description' => 'TODO: a list of all groups calling this person back; hide if not the right stage'
			);
		} else {
			$groups['callbacks_hidden'] = array(
				'name' => 'Callback Groups',
				'type' => 'title',
				'description' => 'Callback lists are not available while first round auditions are in progress.'
			);
		}

		if( $stage == 'draft' ) {
			$groups['preferences'] = array(
				'name' => 'Group Preferences',
				'type' => 'title',
				'description' => 'TODO: an ordered list of group preferences'
			);
		} else {
			$groups['preferences'] = array(
				'name' => 'Group Preferences',
				'type' => 'title',
				'description' => "An auditionee's preferences are not available while pref cards are still being circulated."
			);
		}

		$groups['acceptance'] = array(
			'name' => 'Accepted Group',
			'type' => 'select',
			'description' => 'The final group :)',
			'options_cb' => 'Acaplugin\Util::get_groups_dropdown'
		);

		$values = [1,];

		array_map('Acaplugin\Util::get_groups_dropdown', $values);

		$this->type = bstypes()->create($prefix, 'auditionee', 'auditionees',
			array(
				'columns' => array(
					'email' => array(
						'title' => 'Email',
						'cb' => function( $id ) {
							return $this->type->get( $id, 'email' );
						}
					),
					'telephone' => array(
						'title' => 'Telephone',
						'cb' => function( $id ) {
							return $this->type->get( $id, 'telephone' );
						}
					),
					'residence' => array(
						'title' => 'Residence',
						'cb' => function( $id ) {
							return $this->type->get( $id, 'residence' );
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
							$id = $this->type->get( $id, 'acceptance' );

							if( !$id ) {
								return '--';
							}

							return get_post( $id )->post_title;
						}
					),
					'title' => array( 
						'title' => 'Name'
					)
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
							'pronoun' => array(
								'name' => 'Pronouns',
								'type' => 'radio',
								'options' => array(
									'he' => 'He / Him',
									'she' => 'She / Her',
									'they' => 'They / Them',
									'ey' => 'E(y) / Em',
									'xeh' => 'Xe / Hir',
									'xex' => 'Xe / Xir',
									'zeh' => 'Ze / Hir',
									'zex' => 'Ze / Zir'
								)
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
						    ),
						    'residence' => array(
								'name' => 'Residence',
								'description' => 'Dorm and room number (or address if off-campus)',
							    'type' => 'text'
						    ),
					    )
					),
					'conflicts' => array(
						'title' => 'Conflicts',
						'fields' => $conflicts
					),
					'group' => array(
						'title' => 'Group Selection',
						'fields' => $groups
					)
				),
				'icon' => 'dashicons-smiley',
				'title' => function( $id ) { 
					$last = $this->type->get( $id, 'last_name' );
					$first = $this->type->get( $id, 'first_name' );
					if ( !$last ) {
						$last = '--';
					}
					if ( !$first ) {
						$first = '--';
					}
					return "{$last}, {$first}";
				},
				'supports' => array('revisions')
			)
		);
	}
}