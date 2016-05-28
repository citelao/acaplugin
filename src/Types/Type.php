<?php
namespace Acaplugin\Types;

class Type {

	const PREFIX = 'acac';
	const WRAPPED_PREFIX = "_{Type::PREFIX}_";

	private /* string */ $name;
	private /* Array */ $fields;
	private /* Array */ $args;

	public function __construct( /* string */ $name,  /* string */ $plural,
		$fields, 
		$args = array() ) {

		$defaults = array(
			'description' => 'A custom post type',
			'icon' => 'dashicons-dismiss',
			'labels' => Type::generate_labels($name, $plural)
		);
		$this->args = wp_parse_args( $args, $defaults );
		$this->name = $name;
		$this->fields = $fields;

		// Register type
		add_action( 'init', array( $this, 'on_init' ) );

		// Add admin forms
		add_action( 'cmb2_admin_init', array( $this, 'on_metaboxes' ) );

		// Add columns
		add_filter( 'manage_post_columns', array( $this, 'on_column_titles' ) );
		add_action( 'manage_post_custom_column', array( $this, 'on_column_content' ) );

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
			'supports' => array( 'revisions' )
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

	// http://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
	// https://www.smashingmagazine.com/2013/12/modifying-admin-post-lists-in-wordpress/
	public function on_column_titles( $columns ) {
		// Register column titles
		return $columns;
	}

	public function on_column_content( $column_name, $post_id ) {
		// Register column content.
	}

	// https://www.sitepoint.com/customized-wordpress-administration-filters/
	// https://wordpress.stackexchange.com/questions/45436/add-filter-menu-to-admin-list-of-posts-of-custom-type-to-filter-posts-by-custo
	public function on_list_filters( $query ) {
		// Add dropdowns for filtering
	}

	public function on_filter() {
		// Filter query based on $_GET
	}

	// https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
	// http://wpdreamer.com/2012/03/manage-wordpress-posts-using-bulk-edit-and-quick-edit/
	public function on_quick_edit( $column_name, $post_type ) {

	}
}