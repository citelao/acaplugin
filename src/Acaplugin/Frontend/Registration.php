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
		add_action( 'cmb2_after_init', array( $this, 'submit_form' ) );
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
			'description' => 'We will send callback notifications to this email. Groups may also use this email to contact you during auditions.',
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
			'description' => 'Write your full dorm and room number (or address if off-campus).',
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
			$output .= '<p class="alert alert--warning">' . sprintf( __( 'There was an error in the submission: %s', 'wds-post-submit' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</p>';
		}
		// If the post was submitted successfully, notify the user.
		if ( isset( $_GET['registered'] ) ) {
			// TODO this link is hardcoded
			$output .= '<p class="alert">Thank you for registering! <a href="/register">Start a new registration</a></p>';
			return $output;
		}

		$output .= cmb2_get_metabox_form( $cmb,
			'fake-oject-id', 
			array( 'save_button' => __( 'Register', 'wds-post-submit' ) ) 
		);
		return $output;
	}

	public function submit_form() {
		// If no form submission, bail
		if ( empty( $_POST ) || ! isset( $_POST['submit-cmb'], $_POST['object_id'] ) ) {
			return false;
		}

		$cmb = cmb2_get_metabox( $this->form_id, 'fake-object-id' );

		$post_data = array();
		// Get our shortcode attributes and set them as our initial post_data args
		if ( isset( $_POST['atts'] ) ) {
			foreach ( (array) $_POST['atts'] as $key => $value ) {
				$post_data[ $key ] = sanitize_text_field( $value );
			}
			unset( $_POST['atts'] );
		}

		// Check security nonce
		if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'security_fail', __( 'Security check failed.' ) ) );
		}

		// // Check title submitted
		// if ( empty( $_POST['submitted_post_title'] ) ) {
		// 	return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'New post requires a title.' ) ) );
		// }

		// // And that the title is not the default title
		// if ( $cmb->get_field( 'submitted_post_title' )->default() == $_POST['submitted_post_title'] ) {
		// 	return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'Please enter a new title.' ) ) );
		// }
	// /**
	//  * Fetch sanitized values
	//  */
	// $sanitized_values = $cmb->get_sanitized_values( $_POST );
	// // Set our post data arguments
	// $post_data['post_title']   = $sanitized_values['submitted_post_title'];
	// unset( $sanitized_values['submitted_post_title'] );
	// $post_data['post_content'] = $sanitized_values['submitted_post_content'];
	// unset( $sanitized_values['submitted_post_content'] );
	// // Create the new post
	// $new_submission_id = wp_insert_post( $post_data, true );
	// // If we hit a snag, update the user
	// if ( is_wp_error( $new_submission_id ) ) {
	// 	return $cmb->prop( 'submission_error', $new_submission_id );
	// }
	// /**
	//  * Other than post_type and post_status, we want
	//  * our uploaded attachment post to have the same post-data
	//  */
	// unset( $post_data['post_type'] );
	// unset( $post_data['post_status'] );
	// // Try to upload the featured image
	// $img_id = wds_frontend_form_photo_upload( $new_submission_id, $post_data );
	// // If our photo upload was successful, set the featured image
	// if ( $img_id && ! is_wp_error( $img_id ) ) {
	// 	set_post_thumbnail( $new_submission_id, $img_id );
	// }
	// // Loop through remaining (sanitized) data, and save to post-meta
	// foreach ( $sanitized_values as $key => $value ) {
	// 	if ( is_array( $value ) ) {
	// 		$value = array_filter( $value );
	// 		if( ! empty( $value ) ) {
	// 			update_post_meta( $new_submission_id, $key, $value );
	// 		}
	// 	} else {
	// 		update_post_meta( $new_submission_id, $key, $value );
	// 	}
	// }
	// /*
	//  * Redirect back to the form page with a query variable with the new post ID.
	//  * This will help double-submissions with browser refreshes
	//  */
		wp_redirect( esc_url_raw( add_query_arg( 'registered', 'true' ) ) );
		exit;
	}
}