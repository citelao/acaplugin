<?php
namespace Acaplugin\Frontend;

class Registration {
	private static $instance = null;

	public static function get_instance($prefix) {
		if( is_null( self::$instance ) ) {
			self::$instance = new self($prefix);
		}

		return self::$instance;
	}

	private $form_id = 'acac-frontend-registration-form';

	private function __construct() {
		add_action( 'cmb2_init', array( $this, 'register_form' ) );
		add_shortcode( 'acac_registration', array( $this, 'show_form' ) );
	}

	public function register_form() {
		$cmb = new_cmb2_box( array(
			'id' => $this->form_id,
			'object_types' => array(),
			'hookup' => false,
			'save_fields' => false
		) );

		$cmb->add_field(array(
			'id' => 'first_name',
			'name' => 'First Name',
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => 'last_name',
			'name' => 'Last Name',
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => 'email',
			'name' => 'Email',
			'description' => 'We will send further instructions to this email.',
			'type' => 'text_email',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => 'telephone',
			'name' => 'Telephone Number',
			'description' => 'We will only use this to contact you in an emergency.',
			'type' => 'text',
			'attributes'  => array(
				'type' => 'tel',
				'required' => 'required'
			)
		) );

		$cmb->add_field(array(
			'id' => 'residence',
			'name' => 'Residence',
			'description' => 'Write your full dorm and room number (or address if off-campus)',
			'type' => 'text_email',
			'attributes'  => array(
				'required' => 'required',
			)
		) );
	}

	public function show_form() {
		$cmb = cmb2_get_metabox( $this->form_id, 'fake-object-id' );
		$output = '';
		$output .= cmb2_get_metabox_form( $cmb,
			'fake-oject-id', 
			array( 'save_button' => __( 'Submit Post', 'wds-post-submit' ) ) 
		);
		return $output;
	}
}