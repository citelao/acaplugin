<?php

// Basically a hack on CMB2-many-to-many
class BSTypes_ManyToMany {
	private static $instance = null;

	public static function get_instance() {
		if( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	const VERSION = 0.1;

	const NONCE_STRING = 'many-to-many-field';

	private function __construct() {
		if ( ! defined( 'BS_MANY_TO_MANY_FIELD_DIR' ) ) {
			define( 'BS_MANY_TO_MANY_FIELD_DIR', dirname( __FILE__ ) . '/' );
		}

		// TODO pull things together
		// if ( ! defined( 'CMB2_ATTACHED_POSTS_FIELD_VERSION' ) ) {
		// 	define( 'CMB2_ATTACHED_POSTS_FIELD_VERSION', self::VERSION );
		// }

		add_action( 'cmb2_render_bs_many_to_many', 
			array( $this, 'render' ), 10, 5 );
		add_action( 'cmb2_sanitize_bs_many_to_many', 
			array( $this, 'sanitize' ), 10, 2 );
		add_action( 'after_setup_theme', array( $this, 'do_hook' ) );
		add_action( 'wp_ajax_bs_many_to_many', array( $this, 'on_ajax' )  );
	}

	public function do_hook() {
		// Then fire our hook.
		do_action( 'bs_many_to_many_field_load' );
	}

	public function on_ajax() {
		check_ajax_referer( self::NONCE_STRING );

		// Find correct subaction
		echo $_REQUEST['operation'];
		echo ', ';
		echo $_REQUEST['to'];
		echo ', ';
		echo $_REQUEST['from'];
		echo ', ';
		echo $_REQUEST['type'];

		$to = $_REQUEST['to'];
		$from = $_REQUEST['from'];
		$type = $_REQUEST['type'];

		// TODO check user permissions

		if( $_REQUEST['operation'] == 'add' ) {
			echo p2p_type( $type )->connect( $from, $to );
		} elseif( $_REQUEST['operation'] == 'remove' ) {
			echo p2p_type( $type )->disconnect( $from, $to );
		}

		wp_die();
	}

	public function render( $field, 
		$escaped_value, 
		$object_id, 
		$object_type, 
		$field_type ) {
		
		$this->setup_admin_scripts();

		$query_users = $field->options( 'query_users' );

		if ( ! $query_users ) {

			// Must be related to a specific Posts2Posts connection.
			if( empty( $field->options( 'connection' ) ) ) {
				return;
			}

			// Make sure that the types are sensible
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
		} else {
			// Not configured for many-to-many users connections.
			return;
			// // Setup our args
			// $args = wp_parse_args( (array) $field->options( 'query_args' ), array(
			// 	'number'  => 100,
			// ) );
			// $post_type_labels = $field_type->_text( 'users_text', esc_html__( 'Users' ) );
		}

		// Check 'filter' setting
		$filter_boxes = $field->options( 'filter_boxes' )
			? '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb' ), $post_type_labels ) . '" class="regular-text search" name="%s" /></div>'
			: '';

		if ( ! $query_users ) {
			// Get our posts
			$objects = get_posts( $args );
		} else {
			// Get our users
			$objects = new WP_User_Query( $args );
			$objects = ! $objects || empty( $objects->results ) ? false : $objects->results;
		}

		// If there are no posts found, just stop
		if ( empty( $objects ) ) {
			return;
		}

		// Check to see if we have any meta values saved yet
		$connected = get_posts( array(
			'connected_type' => $field->options( 'connection' ),
			'connected_items' => $object_id,
			'nopaging' => true,
			'suppress_filters' => false
		) );
		$attached = array_map(function($el) { return $el->ID; }, $connected);

		// Set our count class
		$count = 0;

		// Wrap our lists
		echo '<div class="many-to-many-wrap widefat" data-type="' . $field->options( 'connection' ) .'" data-fieldname="'. $field_type->_name() .'">';

		// Open our retrieved, or found posts, list
		echo '<div class="retrieved-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Available %s', 'cmb' ), $post_type_labels ) . '</h4>';

		// Set .has_thumbnail
		$has_thumbnail = $field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected = $field->options( 'hide_selected' ) ? ' hide-selected' : '';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'available-search' );
		}

		echo '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our posts as list items
		foreach ( $objects as $object ) {

			// Increase our count
			$count++;

			// Set our zebra stripes
			$class = $count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached meta
			$class .= ! empty ( $attached ) && in_array( $object->ID, $attached ) ? ' added' : '';

			$thumbnail = '';

			if ( $has_thumbnail ) {
				// Set thumbnail if the options is true
				$thumbnail = $query_users
					? get_avatar( $object->ID, 25 )
					: get_the_post_thumbnail( $object->ID, array( 50, 50 ) );
			}

			$edit_link = $query_users ? get_edit_user_link( $object->ID ) : get_edit_post_link( $object );
			$title     = $query_users ? $object->data->display_name : get_the_title( $object );

			// Build our list item
			printf(
				'<li data-id="%d" class="%s">%s<a title="' . __( 'Edit' ) . '" href="%s">%s</a><span class="dashicons dashicons-plus add-remove"></span></li>',
				$object->ID,
				$class,
				$thumbnail,
				$edit_link,
				$title
			);
		}

		// Close our retrieved, or found, posts
		echo '</ul><!-- .retrieved -->';
		echo '</div><!-- .retrieved-wrap -->';

		// Open our attached posts list
		echo '<div class="attached-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Attached %s', 'cmb' ), $post_type_labels ) . '</h4>';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'attached-search' );
		}

		echo '<ul class="attached connected', $has_thumbnail ,'">';

		// If we have any ids saved already, display them
		$ids = $this->display_attached( $field, $attached );

		$value = ! empty( $ids ) ? implode( ',', $ids ) : '';

		// Close up shop
		echo '</ul><!-- .attached -->';
		echo '</div><!-- .attached-wrap -->';

		echo $field_type->input( array(
			'type'  => 'hidden',
			'class' => 'attached-posts-ids',
			'value' => $value,
			'desc'  => '',
		) );

		echo '</div><!-- .many-to-many-wrap -->';

		// Display our description if one exists
		$field_type->_desc( true, true );
	}

	protected function display_attached( $field, $attached ) {

		// Start with nothing
		$output = '';

		// If we do, then we need to display them as items in our attached list
		if ( ! $attached ) {
			return;
		}

		$query_users = $field->options( 'query_users' );

		// Set our count to zero
		$count = 0;

		$show_thumbnails = $field->options( 'show_thumbnails' );
		// Remove any empty values
		$attached = array_filter( $attached );

		$ids = array();

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $query_users ? get_user_by( 'id', $id ) : get_post( $id );

			if ( empty( $object ) ) {
				continue;
			}

			// Increase our count
			$count++;

			// Set our zebra stripes
			$class = $count % 2 == 0 ? 'even' : 'odd';

			$thumbnail = '';

			if ( $show_thumbnails ) {
				// Set thumbnail if the options is true
				$thumbnail = $query_users
					? get_avatar( $object->ID, 25 )
					: get_the_post_thumbnail( $object->ID, array( 50, 50 ) );
			}

			$edit_link = $query_users ? get_edit_user_link( $object->ID ) : get_edit_post_link( $object );
			$title     = $query_users ? $object->data->display_name : get_the_title( $object );

			// Build our list item
			printf(
				'<li data-id="%d" class="%s">%s<a title="' . __( 'Edit' ) . '" href="%s">%s</a><span class="dashicons dashicons-minus add-remove"></span></li>',
				$id,
				$class,
				$thumbnail,
				$edit_link,
				$title
			);

			$ids[] = $id;
		}

		return $ids;
	}

	public function sanitize( $sanitized_val, $val ) {

	}

	protected function setup_admin_scripts() {
		$dir = BS_MANY_TO_MANY_FIELD_DIR;

		if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
			// Windows
			$content_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
			$content_url = str_replace( $content_dir, WP_CONTENT_URL, $dir );
			$url = str_replace( DIRECTORY_SEPARATOR, '/', $content_url );

		} else {
			$url = str_replace(
				array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
				array( WP_CONTENT_URL, WP_PLUGIN_URL ),
				$dir
			);
		}

		$url = set_url_scheme( $url );
		$url = apply_filters( 'bs_many_to_many_field_assets_url', $url );

		$requirements = array(
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-sortable',
		);

		wp_enqueue_script( 'bs-many-to-many-field', $url . 'js/many-to-many.js', $requirements, self::VERSION, true );
		wp_enqueue_style( 'bs-many-to-many-field', $url . 'css/many-to-many-admin.css', array(), self::VERSION );

		wp_localize_script( 'bs-many-to-many-field', 
			'BS_MANY_TO_MANY_L10N', 
			array(
				'nonce' => wp_create_nonce( self::NONCE_STRING )
			));
	}
}