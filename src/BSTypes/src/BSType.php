<?php
class BSType {

	private /* string */ $prefix;	
	private /* string */ $name;
	private /* Array */ $args;

	public function __construct(
		/* string */ $prefix,
		/* string */ $name,
		/* string */ $plural,
		$args = array() ) {

		$default_args = array(
			/* 
				Array of `id` => array(
					`title`: column title,

					`key`: meta key to display,
						OR
					`cb`: column content callback,
					`sort_cb`: column sort callback
				) for custom columns 
			*/
			'columns' => array(),

			/* Standard Wordpress custom type description */
			'description' => 'A custom post type',

			/* Array of CMB2 metaboxes, each with a `fields` array which
			is an array of CMB2 fields. */
			'fields' => array(),

			/* Standard Wordpress custom type icon */
			'icon' => 'dashicons-dismiss',

			/* Standard Wordpress custom type labels */
			'labels' => BSTypes_Util::get_labels( $name, $plural ),

			/* Standard Wordpress "supports" for custom type. */
			'supports' => array( 'revisions' ),

			/* A replacement for the "Title" column label. */
			'title_column_title' => 'Title',

			/* 
				A callback function to generate an alternative title.
				Only used if your type does not support a title.
			*/
			'title_column_cb' => function( $id ) { return 'n/a'; }
		);
		$this->args = wp_parse_args( $args, $default_args );
		$this->prefix = $prefix;
		$this->name = $name;

		// Register type
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
			// 'capability_type' => 'auditionee',
			'rewrite' => array( 'slug' => $this->name ),
			'supports' => $this->args['supports']
		);
		register_post_type( $this->get_id(), $args );

		// Add admin editing metaboxes
		add_action( 'cmb2_admin_init', array( $this, 'on_metaboxes' ) );

		if ( in_array( 'revisions', $this->args['supports'] ) ) {
			// Add revisioning of metadata.
			// https://johnblackbourn.com/post-meta-revisions-wordpress
			// add_action( 'save_post', array( $this, 'on_save_post' ), 10, 2 );
			// add_action( 'wp_restore_post_revision', array( $this, 'on_restore_revision'), 10, 2 );
			// add_filter( '_wp_post_revision_fields', array( $this, 'on_revision_fields' ) );
			// add_action( '_wp_post_revision_field_my_meta', array( $this, 'on_') );
		}

		// Columns
		add_filter( "manage_{$this->get_id()}_posts_columns", 
			array( $this, 'on_column_titles' ) );
		add_action( "manage_{$this->get_id()}_posts_custom_column", 
			array( $this, 'on_column_content' ), 
			10, 2 );
		add_filter( "manage_edit-{$this->get_id()}_sortable_columns",
			array( $this, 'on_sortable_column_titles' ) );
		add_filter( 'request', array( $this, 'on_sort_columns' ) );
		if ( ! in_array( 'title', $this->args['supports'] ) ) {
			// https://wordpress.stackexchange.com/questions/152971/replacing-the-title-in-admin-list-table
			add_action( 'admin_head-edit.php', array( $this, 'on_edit_post' ) );
		}
		// TODO extend search context: 
		// https://wordpress.stackexchange.com/questions/11758/extending-the-search-context-in-the-admin-list-post-screen

		// Add filter links
		add_action( 'restrict_manage_posts', array( $this, 'on_list_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'on_filter' ) );

		// Quick edit :)
		add_action( 'quick_edit_custom_box', array( $this, 'on_quick_edit' ) );
	}

	public function get( $post_id, $field ) {
		// if post is not this type, error
		
		$type = get_post_type($post_id);
		if ( $type != $this->get_id() ) {
			throw new InvalidArgumentException(
				"Post {$post_id} is not a(n) {$this->name} (id: {$this->get_id()}). " .
				"It is a(n) {$type}.");
		}

		return get_post_meta( 
			$post_id,
			$this->get_meta_key( $field ),
			true );
	}

	public function get_id() {
		return BSTypes_Util::get_type_id($this->prefix, $this->name);
	}

	public function get_meta_key( $field ) {
		return BSTypes_Util::get_field_id($this->prefix, $this->name, $field);
	}

	// Register metaboxes
	public function on_metaboxes() {
		foreach ( $this->args['fields'] as $box_name => $metabox_options ) {
			$default_metabox = array(
				'id' => $this->prefix . '_' . $this->name . '_metabox_' . $box_name,
				'object_types' => array($this->get_id()),
				'fields' => array()
			);
			$parsed_metabox_options = wp_parse_args( $metabox_options, $default_metabox );
			$fields = $parsed_metabox_options['fields'];
			unset( $parsed_metabox_options['fields'] );
			$cmb = new_cmb2_box( $parsed_metabox_options );

			foreach ( $fields as $field_name => $field_options ) {
				$default_field = array(
					'id' => $this->get_meta_key( $field_name ),
				);
				$parsed_field_options = wp_parse_args( $field_options, $default_field );
				$cmb->add_field( $parsed_field_options );
			}
		}
	}

	// Register column titles
	// http://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
	// https://www.smashingmagazine.com/2013/12/modifying-admin-post-lists-in-wordpress/
	public function on_column_titles( $columns ) {
		
		foreach ($this->args['columns'] as $id => $column) {
			$columns[$id] = $column['title'];
		}

		$columns['title'] = $this->args['title_column_title'];

		return $columns;
	}

	// Register column content.
	public function on_column_content( $column_name, $post_id ) {
		foreach ($this->args['columns'] as $id => $column) {
			if ( $column_name == $id ) {
				echo $column['cb']( $post_id );
			} 
		}
	}

	public function on_sortable_column_titles( $columns ) {
		
		foreach ($this->args['columns'] as $id => $column) {
			if ( isset( $column['sort_cb'] ) || isset( $column['sort'] ) ) {
				$columns[$id] = $column['title'];
			}
		}

		return $columns;
	}

	public function on_sort_columns( $vars ) {
		if ( isset( $vars['orderby'] ) ) {
			foreach ($this->args['columns'] as $id => $column) {
				if ( $id == $vars['orderby'] ) {
					if ( isset( $column['sort_cb'] ) ) {
						return $column['sort_cb']( $vars );
					}

					return $vars;
				}
			}
		}

		return $vars;
	}

	public function on_edit_post() {
		add_filter( 'the_title', array( $this, 'on_the_title' ), 10, 2 );
	}
	public function on_the_title( $title, $id ) {
		if ( get_post_type( $id ) == $this->get_id() ) {
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