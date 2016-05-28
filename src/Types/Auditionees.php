<?php
namespace Acaplugin\Types;

class Auditionees {

	private $type;

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
			'description' => 'Auditionees are all the people who try out for groups',
			'icon' => 'dashicons-smiley'
		) );
	}
}