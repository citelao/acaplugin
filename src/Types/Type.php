<?php
namespace Acaplugin\Types;

class Type {

	const PREFIX = 'acac';
	const WRAPPED_PREFIX = '_' . Type::PREFIX . '_';

	private /* string */ $name;
	private /* Array */ $fields;
	private /* Array */ $args;

	public function __construct( /* string */ $name,  /* string */ $plural,
		$fields, 
		$args = array() ) {

		$defaults = array(
			'columns' => array(),
			'description' => 'A custom post type',
			'icon' => 'dashicons-dismiss',
			'labels' => Type::generate_labels( $name, $plural ),
			'supports' => array( 'revisions' ),
			'title_column_title' => 'Title',
			'title_column_cb' => function( $id ) { return 'n/a'; }
		);
		$this->args = wp_parse_args( $args, $defaults );
		$this->name = $name;
		$this->fields = $fields;

		// Register type
		add_action( 'init', array( $this, 'on_init' ) );

		// Add admin forms
		add_action( 'cmb2_admin_init', array( $this, 'on_metaboxes' ) );

		// Add columns
		add_filter( "manage_{$this->name}_posts_columns", array( $this, 'on_column_titles' ) );
		add_action( "manage_{$this->name}_posts_custom_column", array( $this, 'on_column_content' ), 10, 2 );
		if ( ! in_array( 'title', $this->args['supports'] ) ) {
			// https://wordpress.stackexchange.com/questions/152971/replacing-the-title-in-admin-list-table
			add_action( 'admin_head-edit.php', array( $this, 'on_edit_post' ) );
		}

		// Add filter links
		add_action( 'restrict_manage_posts', array( $this, 'on_list_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'on_filter' ) );

		// Quick edit :)
		add_action( 'quick_edit_custom_box', array( $this, 'on_quick_edit' ) );
		// add_action( )
	}

	// TODO also generate labels for post_updated_messages. Yup. We need another hook.
	public static function generate_labels( /* string */ $singular, /* string */ $plural ) {
		$singular = lcfirst($singular);
		$plural = lcfirst($plural);
		$usingular = ucfirst($singular);
		$uplural = ucfirst($plural);
		$labels = array(
			'name' => $uplural,
			'singular_name' => $usingular,
			'menu_name' => $uplural,
			'name_admin_bar' => $usingular,
			'add_new' => 'Add New',
			'add_new_item' => 'Add New ' . $usingular,
			'new_item' => 'New ' . $usingular,
			'edit_item' => 'Edit ' . $usingular,
			'view_item' => 'View ' . $usingular,
			'search_items' => 'Search ' . $uplural,
			'not_found' => 'No ' . $plural . ' found',
			'not_found_in_trash' => 'No ' . $plural . ' found in trash',
			'all_items' => 'All ' . $uplural,
			'archives' => $usingular . ' Archives'
		);

		return $labels;
	}

	// Register post type
	public function on_init() {
		$args = array(
			'labels' => $this->args['labels'],
			'description' => $this->args['description'],
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => $this->args['icon'],
			'query_var' => false,
			// 'capability_type' => 'auditionee'
			'supports' => $this->args['supports']
		);
		register_post_type( $this->name, $args );
	}

	// Register metaboxes
	public function on_metaboxes() {
		foreach ( $this->fields as $box_name => $metabox_options ) {
			$default_metabox = array(
				'id' => Type::PREFIX . '_' . $this->name . '_metabox_' . $box_name,
				'object_types' => array($this->name),
				'fields' => array()
			);
			$parsed_metabox_options = wp_parse_args($metabox_options, $default_metabox);
			$fields = $parsed_metabox_options['fields'];
			unset($parsed_metabox_options['fields']);
			$cmb = new_cmb2_box($parsed_metabox_options);

			foreach ($fields as $field_name => $field_options) {
				$default_field = array(
					'id' => Type::WRAPPED_PREFIX . $this->name . '_' . $field_name,
				);
				$parsed_field_options = wp_parse_args($field_options, $default_field);
				$cmb->add_field($parsed_field_options);
			}
		}
	}

	// Register column titles
	// http://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
	// https://www.smashingmagazine.com/2013/12/modifying-admin-post-lists-in-wordpress/
	public function on_column_titles( $columns ) {
		$columns['hello'] = 'Name';
		if( ! in_array( 'title', $this->args['supports'] ) ) {
			$columns['title'] = $this->args['title_column_title']; // TODO
		}
		return $columns;
	}

	// Register column content.
	public function on_column_content( $column_name, $post_id ) {
		if ( $column_name == 'hello' ) {
			echo 'Bubba';
		}

		if ( $column_name == 'title' && ! in_array( 'title', $this->args['supports'] ) ) {
			echo 'ffu';
		}
	}

	public function on_edit_post() {
		add_filter( 'the_title', array( $this, 'on_the_title' ), 100, 2 );
	}
	public function on_the_title( $title, $id ) {
		if ( get_post_type( $id ) == $this->name ) {
			return call_user_func( $this->args['title_column_cb'], $id);
		}

		return $title;
	}

	// https://www.sitepoint.com/customized-wordpress-administration-filters/
	// https://wordpress.stackexchange.com/questions/45436/add-filter-menu-to-admin-list-of-posts-of-custom-type-to-filter-posts-by-custo
	public function on_list_filters( $query ) {
		// Add dropdowns for filtering
	}

	public function on_filter() {
		// Filter query based on $_GET
	}

	// https://wordpress.stackexchange.com/questions/7291/quick-edit-screen-customization
	// https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
	// https://axelerant.com/working-examples-for-wordpress-bulk-and-quick-edit/
	// http://wpdreamer.com/2012/03/manage-wordpress-posts-using-bulk-edit-and-quick-edit/
	public function on_quick_edit( $column_name, $post_type ) {

	}
}