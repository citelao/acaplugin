<?php
namespace Acaplugin\Frontend;

class Prefs {
	private static $instance = null;

	public static function get_instance($prefix) {
		if( is_null( self::$instance ) ) {
			self::$instance = new self($prefix);
		}

		return self::$instance;
	}

	private $form_id = 'acac-frontend-prefs-form';
	private $prefix = 'acac';
	private $type = 'auditionee';

	private function __construct($prefix) {
		$this->prefix = $prefix;

		add_action( 'cmb2_init', array( $this, 'register_form' ) );
		add_shortcode( 'acac_prefs', array( $this, 'show_form' ) );
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

		if( empty($_GET['key'] ) ) { 
			$cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'Missing auditionee key.' ) ) );
			return;
		}

		$auditionees = get_posts( array(
			'post_type' => \BSTypes_Util::get_type_id( $this->prefix, $this->type ),
			'meta_key' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'key' ),
			'meta_value' => $_GET['key']
		) );

		if( count( $auditionees ) == 0 ) {
			$cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'No auditionee found for key.' ) ) );
			return;
		}

		if( count( $auditionees ) != 1 ) {
			$cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'Duplicate key!' ) ) );
			return;
		}

		$auditionee = $auditionees[0];
		$id = $auditionee->ID;

		$submitted = get_post_meta($id,
			\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'preferences_submitted' ),
			true );

		if( $submitted == 'on' && !isset( $_GET['submitted'] ) ) {
			$cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You\'ve already submitted your pref card!' ) ) );
			return;
		}

		$cmb->add_field(array(
			'id' => 'post_id',
			'type' => 'hidden',
			'default' => $id
		) );

		$cmb->add_field(array(
			'id' => \BSTypes_Util::get_field_id( $this->prefix, $this->type, 'preferences' ),
			'name' => 'Group Preferences',
			'type' => 'custom_attached_posts',
			'description' => 'An ordered list of group preferences. The higher the group, the better. A group in the left column is not preffed.',
			'options' => array(
				'query_args' => array(
					'post_type' => 'acac_group',
					'connected_type' => 'group_callbacks',
					'connected_items' => $id,
					'nopaging' => true
				)
			)
		) );
	}

	public function show_form() {
		$stage = get_option( 'acac_config' )['stage'];
		if( $stage != 'callbacks' ) {
			return '<p class="alert">Preference cards are closed at this time. Sorry!</p>';
		}

		wp_enqueue_style( 'acac-form-style' );

		$cmb = cmb2_get_metabox( $this->form_id, 'fake-object-id' );

		$output = '';

		if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
			// If there was an error with the submission, add it to our ouput.
			$output .= '<p class="alert alert--warning">' . sprintf( __( 'There was an error: %s', 'wds-post-submit' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</p>';
		}
		// If the post was submitted successfully, notify the user.
		if ( isset( $_GET['submitted'] ) ) {
			$output .= '<p class="alert">Thank you for submitting your preference card!</p>';
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
		if( $stage != 'callbacks' ) {
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

		// Check form fields submitted
		if ( ! isset( $_POST[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'preferences')] ) ) {
			return $cmb->prop( 'submission_error', new \WP_Error( 'post_data_missing', __( 'You must submit preferences.' ) ) );
		}

		/*
		* Fetch sanitized values
		*/
		$sanitized_values = $cmb->get_sanitized_values( $_POST );

		// Grab the post ID
		$post_id = $sanitized_values['post_id'];
		unset( $sanitized_values['post_id'] );

		// Add a flag to say we've submitted our preferences
		$sanitized_values[\BSTypes_Util::get_field_id( $this->prefix, $this->type, 'preferences_submitted' )] = 'on';

		// Loop through remaining (sanitized) data, and save to post-meta
		foreach ( $sanitized_values as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = array_filter( $value );
				if( ! empty( $value ) ) {
					update_post_meta( $post_id, $key, $value );
				}
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
		/*
		 * Redirect back to the form page with a query variable with the new post ID.
		 * This will help double-submissions with browser refreshes
		 */
		wp_redirect( esc_url_raw( add_query_arg( 'submitted', 'true' ) ) );
		exit;
	}
}