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
				'type' => 'bs_many_to_many',
				'description' => 'Add groups to call this person back',
				'options' => array(
					'connection' => 'group_callbacks',
					'query_args' => array(
						// 'post_type' => 'acac_group'
					)
				)
			);
		} else {
			$groups['callbacks_hidden'] = array(
				'name' => 'Callback Groups',
				'type' => 'title',
				'description' => 'Callback lists are not available while first round auditions are in progress.'
			);
		}

		if( $stage == 'draft' ) {
			 // VERY hacky
			global $post;
			$post_id = 0;
			if( $post ) {
				$post_id = $post->ID;
			} else {
				$post_id = $_GET['post'];
			}

			$groups['preferences'] = array(
				'name' => 'Group Preferences',
				'type' => 'custom_attached_posts',
				'description' => 'An ordered list of group preferences. The higher the group, the better. A group in the left column is not preffed.',
				'options' => array(
					'query_args' => array(
						'post_type' => 'acac_group',
						'connected_type' => 'group_callbacks',
						'connected_items' => $post_id,
						'nopaging' => true
					)
				)
			);
		} else {
			$groups['preferences'] = array(
				'name' => 'Group Preferences',
				'type' => 'title',
				'description' => "An auditionee's preferences are not available while pref cards are still being circulated."
			);
		}
		$groups['preferences_submitted'] = array(
			'name' => 'Preferences Submitted',
			'desc' => 'Checked if the auditionee has submitted their preferences.',
			'type' => 'checkbox'
		);

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
						'filter' => function() {
							return array(
								'default' => 'Any # of callbacks',
								'none' => 'TODO'
							);
						},
						'cb' => function( $id ) {
							return count(get_posts( array(
								'connected_type' => 'group_callbacks',
								'connected_items' => $id,
								'nopaging' => true,
								'suppress_filters' => false
							) ) );
						}
					),
					'pref_card' => array(
						'title' => 'Pref. Card Status',
						'filter' => function() {
							// TODO filter_cb
							return array(
								'default' => 'All pref card states',
								'none' => 'No pref card generated',
								'incomplete' => 'Uncompleted pref card',
								'complete' => 'Completed pref card',
							);
						},
						'cb' => function( $id ) {
							$callbacks = count(get_posts( array(
								'connected_type' => 'group_callbacks',
								'connected_items' => $id,
								'nopaging' => true,
								'suppress_filters' => false
							) ) );
							$called_back = ($callbacks != 0);
							$complete = $this->type->get( $id, 'preferences_submitted' );

							if ( $called_back ) {
								return ( $complete ) ? '✓' : 'Not completed';
							} else {
								return '';
							}
						}
					),
					'preferences' => array(
						'title' => 'Preferences',
						'filter' => function() {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'draft' ) {
								return array( 'default' => 'Any pref-ed group' );
							}

							return array( 'default' => 'Any pref-ed group' ) +
								\Acaplugin\Util::get_groups_multicheck( null );
						},
						'filter_cb' => function( $query, $arg ) {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'draft' ) {
								return;
							}

							if( $arg == 'default' ) {
								return;
							}

							// I know, I know, this is gross. I'm sorry.
							$query->query_vars['meta_key'] = $this->type->get_meta_key( 'preferences' );
							$query->query_vars['meta_value'] = '"' . $arg . '"';
							$query->query_vars['meta_compare'] = 'LIKE';
						},
						'cb' => function( $id ) {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'draft' ) {
								return '(hidden)';
							}

							$groups = $this->type->get( $id, 'preferences' );

							if( empty( $groups ) ) {
								return '--';
							}

							$names = array_map( array( $this, 'get_post_title' ), $groups );

							$rtn = '<ol style="margin:0"><li>';
							$rtn .= join( '</li><li>', $names );
							$rtn .= '</li></ol>';
							return $rtn;
						}
					),
					'callback_groups' => array(
						'title' => 'Callback Groups',
						'filter' => function() {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'callbacks' && $stage != 'draft' ) {
								return array( 
									'default' => 'Any callback group'
								);
							}

							return array( 
								'default' => 'Any callback group',
								'none' => 'TODO No callbacks'
							) +
								\Acaplugin\Util::get_groups_multicheck( null );
						},
						'filter_cb' => function( $query, $arg ) {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'callbacks' && $stage != 'draft' ) {
								return;
							}

							if( $arg == 'default' ) {
								return;
							}

							$query->query_vars['connected_type'] = 'group_callbacks';
							if( $arg == 'none' ) {
								$query->query_vars['connected_items'] = '0';
							} else {
								$query->query_vars['connected_items'] = (int)$arg;
							}
						},
						'cb' => function( $id ) {
							$stage = get_option( 'acac_config' )['stage'];
							if( $stage != 'callbacks' && $stage != 'draft' ) {
								return '(hidden)';
							}

							$groups = get_posts( array(
								'connected_type' => 'group_callbacks',
								'connected_items' => $id,
								'nopaging' => true,
								'suppress_filters' => false
							) );

							if( empty( $groups ) ) {
								return '--';
							}

							$names = array_map( array( $this, 'get_post_title' ), $groups );

							$rtn = '<ul style="margin:0"><li>';
							$rtn .= join( '</li><li>', $names );
							$rtn .= '</li></ul>';
							return $rtn;
						}
					),
					'group' => array(
						'title' => 'Accepted Group',
						'filter' => function() {
							return array( 
								'default' => 'Any accepted group',
								'none' => 'No accepted group'
							) +
								\Acaplugin\Util::get_groups_multicheck( null );
						},
						'filter_cb' => function( $query, $arg ) {
							if( $arg == 'default' ) {
								return;
							}

							$query->query_vars['meta_key'] = $this->type->get_meta_key( 'acceptance' );
							if( $arg == 'none' ) {
								$query->query_vars['meta_value'] = '0';
							} else {
								$query->query_vars['meta_value'] = $arg;
							}
						},
						'cb' => function( $user_id ) {
							$id = $this->type->get( $user_id, 'acceptance' );

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
								'type' => 'text',
								'attributes'  => array(
									'required' => 'required',
								)
							),
							'last_name' => array(
								'name' => 'Last Name',
								'type' => 'text',
								'attributes'  => array(
									'required' => 'required',
								)
							),
							// 'pronoun' => array(
							// 	'name' => 'Pronouns',
							// 	'type' => 'radio',
							// 	'options' => array(
							// 		'he' => 'He / Him',
							// 		'she' => 'She / Her',
							// 		'they' => 'They / Them',
							// 		'ey' => 'E(y) / Em',
							// 		'xeh' => 'Xe / Hir',
							// 		'xex' => 'Xe / Xir',
							// 		'zeh' => 'Ze / Hir',
							// 		'zex' => 'Ze / Zir'
							// 	)
							// ),
							'email' => array(
								'name' => 'Email',
								'type' => 'text_email',
								'description' => 'We will send further instructions to this email.',
								'attributes'  => array(
									'required' => 'required',
								)
							),
							'telephone' => array(
								'name' => 'Telephone Number',
								'type' => 'text',
								'attributes' => array(
									'type' => 'tel',
								)
							),
							'residence' => array(
								'name' => 'Residence',
								'description' => 'Dorm and room number (or address if off-campus)',
								'type' => 'text',
								'attributes'  => array(
									'required' => 'required',
								)
							),
							'key' => array(
								'name' => 'Key',
								'description' => 'Unique key for this auditionee\'s pref card. Please do not edit if possible.',
								'type' => 'text',
								'attributes'  => array(
									'required' => 'required',
								)
							)
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

	public function get_post_title( $post ) {
		return get_post( $post )->post_title;
	}
}