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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_register_style( 'acac-form-style', plugins_url( 'Frontend/css/style.css', dirname( __FILE__ ) ) );
	}

	public function register_form() {
		$cmb = new_cmb2_box( array(
			'id' => $this->form_id,
			'object_types' => array(),
			'hookup' => false,
			'save_fields' => false,
			'cmb_styles' => false
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
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => 'conflicts_desc',
			'name' => 'Callback conflicts',
			'type' => 'title',
			'description' => 'Please write any (potential) conflicts you have on these dates. We use this to help plan your callback schedule.'
		) );

		$callback_dates = get_option( 'acac_config' )['callback_dates'];
		foreach ( $callback_dates as $key => $date ) {
			$date = strtotime($date);

			$nice_date = date('l, F j', $date);

			$cmb->add_field(array(
				'id' => 'conflict-' . date('m-d', $date),
				'name' => 'Conflicts on ' . $nice_date,
				'type' => 'textarea'
			) );

			$conflicts['conflict-' . date('m-d', $date)] = array(
				'name' => 'Conflicts on ' . $nice_date,
				'type' => 'textarea'
			);
		}
	}

	public function show_form() {
		wp_enqueue_style( 'acac-form-style' );

		$cmb = cmb2_get_metabox( $this->form_id, 'fake-object-id' );
		$output = '';

		if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
			// If there was an error with the submission, add it to our ouput.
			$output .= '<h3>' . sprintf( __( 'There was an error in the submission: %s', 'wds-post-submit' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
		}
		// If the post was submitted successfully, notify the user.
		if ( isset( $_GET['post_submitted'] ) && ( $post = get_post( absint( $_GET['post_submitted'] ) ) ) ) {
			// Get submitter's name
			$name = get_post_meta( $post->ID, 'submitted_author_name', 1 );
			$name = $name ? ' '. $name : '';
			// Add notice of submission to our output
			$output .= '<h3>' . sprintf( __( 'Thank you%s, your new post has been submitted and is pending review by a site administrator.', 'wds-post-submit' ), esc_html( $name ) ) . '</h3>';
		}

		$output .= cmb2_get_metabox_form( $cmb,
			'fake-oject-id', 
			array( 'save_button' => __( 'Register', 'wds-post-submit' ) ) 
		);
		return $output;
	}
}