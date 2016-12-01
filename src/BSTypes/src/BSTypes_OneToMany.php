<?php

// Adapted from CMB2-many-to-many
class BSTypes_OneToMany {
	private static $instance = null;

	public static function get_instance() {
		if( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	const VERSION = 0.1;

	const NONCE_STRING = 'one-to-many-field';

	private function __construct() {
		if ( ! defined( 'BS_MANY_TO_MANY_FIELD_DIR' ) ) {
			define( 'BS_MANY_TO_MANY_FIELD_DIR', dirname( __FILE__ ) . '/' );
		}

		// TODO pull things together
		// if ( ! defined( 'CMB2_ATTACHED_POSTS_FIELD_VERSION' ) ) {
		// 	define( 'CMB2_ATTACHED_POSTS_FIELD_VERSION', self::VERSION );
		// }

		add_action( 'cmb2_render_bs_one_to_many', 
			array( $this, 'render' ), 10, 5 );
		add_action( 'cmb2_sanitize_bs_one_to_many', 
			array( $this, 'sanitize' ), 10, 2 );
		add_action( 'cmb2_override_meta_save', 
			array( $this, 'save' ), 10, 4 );
		add_action( 'after_setup_theme', array( $this, 'do_hook' ) );
	}

	public function do_hook() {
		// Then fire our hook.
		do_action( 'bs_one_to_many_field_load' );
	}

	public function render( $field, 
		$escaped_value, 
		$object_id, 
		$object_type, 
		$field_type ) {

		// Make sure that the types are sensible
		if( ! $field->options( 'connection' ) ) {
			throw new InvalidArgumentException( 'Connection is required' );
		}
		$connection = p2p_type( $field->options( 'connection' ) );
		$from_types = $connection->side['from']->query_vars['post_type'];
		$to_types = $connection->side['to']->query_vars['post_type'];

		$current_type = get_post_type($object_id);
		$fetch_direction = -1;
		if( in_array( $current_type, $from_types ) ) {
			$fetch_direction = 'to';
		} else if( in_array( $current_type, $to_types ) ) {
			$fetch_direction = 'from';
		} else {
			return;
		}

		// Setup our args
		$default_args = wp_parse_args( 
			array(
				'posts_per_page' => 300,
				'orderby' => 'name',
				'order' => 'ASC',
			),
			$connection->side[$fetch_direction]->query_vars
		);
		$args = wp_parse_args( (array) $field->options( 'query_args' ), 
			$default_args);

		// loop through post types to get labels for all
		$post_type_labels = array();
		foreach ( (array) $args['post_type'] as $post_type ) {
			// Get post type object for attached post type
			$attached_post_type = get_post_type_object( $post_type );

			// continue if we don't have a label for the post type
			if ( ! $attached_post_type || ! isset( $attached_post_type->labels->name ) ) {
				continue;
			}

			$post_type_labels[] = $attached_post_type->labels->name;
			$post_type_labels = implode( '/', $post_type_labels );
		}

		// Get our posts
		$objects = get_posts( $args );

		// If no objects, render an error:
		if ( empty( $objects ) ) {
			echo 'There are no objects to have a relationship with.';
			return;
		}

		// Check to see if we already have a connection
		$connected = get_posts( array(
			'connected_type' => $field->options( 'connection' ),
			'connected_items' => $object_id,
			'nopaging' => true,
			'suppress_filters' => false
		) );
		$attached = array_map(function($el) { return $el->ID; }, $connected);

		echo sprintf('<select class="cmb2_select" name="%s">',
			$field_type->_name());
		echo sprintf('<option disabled value="" %s>--</option>',
			empty( $attached ) ? 'selected' : '');

		foreach ( $objects as $object ) {
			$selected = in_array( $object->ID, $attached ) ? ' selected' : '';
			$edit_link = get_edit_post_link( $object );
			$title = get_the_title( $object );

			printf(
				'<option value="%d" %s>%s</a></option>',
				$object->ID,
				$selected,
				$title
			);
		}
		echo '</select>';

		// Display our description if one exists
		$field_type->_desc( true, true );
	}

	public function save( $check, 
		$args,
		$field_args,
		$field ) {

		// Don't update other fields :)
		if ( $field_args['type'] != 'bs_one_to_many' ) {
			return $check;
		}

		// Don't know how you can get here without this, but ok:
		if( ! $field->options( 'connection' ) ) {
			throw new InvalidArgumentException( 'Connection is required' );
		}

		$type = $field->options( 'connection' );

		// Disconnect any previous connections
		$connected = get_posts( array(
			'connected_type' => $type,
			'connected_items' => $args['id'],
			'nopaging' => true,
			'suppress_filters' => false
		) );
		if( ! empty( $connected ) ) {
			// There can only be one
			$to = $connected[0]->ID;
			p2p_type( $type )->disconnect( $args['id'], $to );
		}

		return p2p_type( $type )->connect( $args['id'], $args['value'] );
	}

	public function sanitize( $sanitized_val, $val ) {
	}
}