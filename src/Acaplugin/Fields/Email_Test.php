<?php
namespace Acaplugin\Fields;

class Email_Test {
	private static $instance = null;

	public static function get_instance($prefix) {
		if( is_null( self::$instance ) ) {
			self::$instance = new self($prefix);
		}

		return self::$instance;
	}

	private function __construct($prefix) {
		$this->prefix = $prefix;

		add_action( 'cmb2_render_email_test', array( $this, 'render' ), 10, 5 );
		add_action( 'cmb2_sanitize_email_test', array( $this, 'sanitize' ), 10, 2 );
	}

	public function render( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo $field_type_object->input( array( 'type' => 'email' ) );
		echo "FFFf";
	}

	function sanitize( $override_value, $value ) {
		// $value = sanitize_text_field( $value );
	}
}