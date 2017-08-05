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
	private $prefix = 'acac';
	private $type = 'auditionee';

	private $email_subject = 'Thank you for registering with ACAC!';
	// TODO hardcoded callback dates
	private $email_message = '<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Thank you for registering with ACAC!</title>
</head>
<body style="font-family: Helvetica, Arial; max-width: 40rem">
	<p>Hello %s,</p>
	<p>
		You are registered with ACAC for this year\'s auditions!
	</p>
	<p><strong>What\'s next?</strong></p>
	<p>
		You are completely setup for this auditions season. Attend the rest of
		your auditions, but you do not need to do any further registration on
		the ACAC website.
	</p>

	<p>
		ACAC will send you a notification email about callbacks early Monday
		morning (September 5th). If you are called back, the individual groups
		will contact you with more information—including callback times.
	</p>

	<p><strong>Registration details</strong></p>

	<p>Please verify your registration details below:</p>

	<ul>
		<li><strong>First name:</strong> %s</li>
		<li><strong>Last name:</strong> %s</li>
		<li><strong>Email:</strong> %s</li>
		<li><strong>Phone:</strong> %s</li>
		<li><strong>Residence:</strong> %s</li>
		<li><strong>Conflicts on Monday, September 5:</strong> %s</li>
		<li><strong>Conflicts on Tuesday, September 6:</strong> %s</li>
		<li><strong>Conflicts on Wednesday, September 7:</strong> %s</li>
	</ul>

	<p>
		Email acacpresident@gmail.com (or reply to this message) if there is
		an error or something changes.
	</p>

	<p>Thank you for auditioning for a cappella at Wash U!</p>

	<p>
		Best of luck, <br />
		Ben Stolovitz <br />
		ACAC Technical Chair
	</p>
</body>
</html>';

	private function __construct($prefix) {
		$this->prefix = $prefix;

		add_action( 'cmb2_init', array( $this, 'register_form' ) );
		add_shortcode( 'acac_registration', array( $this, 'show_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'cmb2_after_init', array( $this, 'submit_form' ) );
	}

	public function enqueue_scripts() {
		if( ! wp_script_is( 'acac-form-style', 'registered' ) ) {
			wp_register_style( 'acac-form-style', plugins_url( 'Frontend/css/style.css', dirname( __FILE__ ) ) );
		}
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
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'first_name' ),
			'name' => 'First Name',
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'last_name' ),
			'name' => 'Last Name',
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'email' ),
			'name' => 'Email',
			'description' => 'We will send callback notifications to this email. Groups may also use this email to contact you during auditions.',
			'type' => 'text_email',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'telephone' ),
			'name' => 'Telephone Number',
			'description' => 'We will only use this to contact you in an emergency.',
			'type' => 'text',
			'attributes'  => array(
				'type' => 'tel',
				'required' => 'required'
			)
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'residence' ),
			'name' => 'Residence',
			'description' => 'Write your full dorm and room number (or address if off-campus).',
			'type' => 'text',
			'attributes'  => array(
				'required' => 'required',
			)
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'auditioned_groups' ),
			'name' => 'Auditioned Groups',
			'type' => 'multicheck',
			'description' => 'What groups are you auditioning for?',
			'options_cb' => 'Acaplugin\Util::get_groups_multicheck'
		) );

		$cmb->add_field(array(
			'id' => 'conflicts_desc',
			'name' => 'Callback conflicts',
			'type' => 'title',
			'description' => 'Please write any (potential) conflicts you have on these dates. We use this to help plan your callback schedule.'
		) );

		$callback_dates = get_option( 'acac_config' )['callback_dates'];
		if( ! $callback_dates ) {
			add_action( 'admin_notices', array( $this, 'warn_no_callback_dates' ) );
			return;
		}

		foreach ( $callback_dates as $key => $date ) {
			$date = strtotime($date);

			$nice_date = date('l, F j', $date);

			$cmb->add_field(array(
				'id' => \BSTypes_Util::get_field_id(  $this->prefix, $this->type, 'conflict-' . date( 'm-d', $date ) ),
				'name' => 'Conflicts on ' . $nice_date,
				'type' => 'textarea'
			) );

			$conflicts['conflict-' . date('m-d', $date)] = array(
				'name' => 'Conflicts on ' . $nice_date,
				'type' => 'textarea'
			);
		}
	}

	public function warn_no_callback_dates() {
		$class = 'notice notice-error';
		$message = 'There are no callback dates defined! '
			. 'Fix this in the "Manage Auditions" section.';

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public function show_form() {
		$stage = get_option( 'acac_config' )['stage'];
		if( $stage != 'auditions' ) {
			return '<p class="alert">Audition registration is closed at this time. Sorry!</p>';
		}

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
			$output .= '<p class="alert">Thank you for registering! You should receive a confirmation email shortly. If you don\'t, contact <a href="mailto:acacpresident@gmail.com">acacpresident@gmail.com</a></p><p class="alert"><a href="/register">Start a new registration</a></p>';
			return $output;
		}

		$output .= cmb2_get_metabox_form( $cmb,
			'fake-oject-id', 
			array( 'save_button' => __( 'Register', 'wds-post-submit' ) ) 
		);
		return $output;
	}

	public function submit_form() {
		$stage = get_option( 'acac_config' )['stage'];
		if( $stage != 'auditions' ) {
			return;
		}

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

		// Check name submitted
		if ( empty( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'first_name')] ) || 
			empty( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'last_name')] ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You must enter a full name.' ) ) );
		}

		if ( empty( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'email')] ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You must enter an email.' ) ) );
		}

		if ( empty( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'telephone')] ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You must enter a telephone number.' ) ) );
		}

		if ( empty( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'residence')] ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You must enter an address of residence.' ) ) );
		}

		/*
		* Fetch sanitized values
		*/
		$sanitized_values = $cmb->get_sanitized_values( $_POST );

		$user_id = get_current_user_id();

		// Set our post data arguments
		$post_data['post_type'] = \BSTypes_Util::get_type_id( $this->prefix, $this->type );
		$post_data['post_status'] = 'publish';

		 // Create the new post
		$new_submission_id = wp_insert_post( $post_data, true );
		// If we hit a snag, update the user
		if ( is_wp_error( $new_submission_id ) ) {
			return $cmb->prop( 'submission_error', $new_submission_id );
		}

		// Generate key
		$key_meta = \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'key' );
		$sanitized_values[$key_meta] = md5( 'a simple salt' . $new_submission_id );

		// Loop through remaining (sanitized) data, and save to post-meta
		foreach ( $sanitized_values as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = array_filter( $value );
				if( ! empty( $value ) ) {
					update_post_meta( $new_submission_id, $key, $value );
				}
			} else {
				update_post_meta( $new_submission_id, $key, $value );
			}
		}

		wp_update_post( array( 
			'ID' => $new_submission_id
		) );

		// Send confirmation email:
		$this->send_confirmation($sanitized_values);

		/*
		 * Redirect back to the form page with a query variable with the new post ID.
		 * This will help double-submissions with browser refreshes
		 */
		wp_redirect( esc_url_raw( add_query_arg( 'registered', 'true' ) ) );
		exit;
	}

	// TODO this should be on ALL registrations
	private function send_confirmation($values) {
		$to = $values[ \BSTypes_Util::get_field_id( 
			$this->prefix,
			$this->type,
			'email'
		) ];
		$subject = $this->email_subject;
		$message = sprintf($this->email_message,
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'first_name'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'first_name'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'last_name'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'email'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'telephone'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'residence'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'conflict-09-05'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'conflict-09-06'
			) ] ),
			sanitize_text_field( $values[ \BSTypes_Util::get_field_id( 
				$this->prefix,
				$this->type,
				'conflict-09-07'
			) ] )
		);
		wp_mail( $to, $subject, $message, array(
			'Content-type: text/html; charset=UTF-8'
		) );
	}
}