<?php
class BSType {

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
			'labels' => Type::generate_labels( $name, $plural ),

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
	}

	public function get( $post_id, $field ) {
		// if post is not this type, error
		// if field not in this type, error

		// return data
	}

}