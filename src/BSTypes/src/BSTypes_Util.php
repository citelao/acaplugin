<?php

class BSTypes_Util {
	public static function get_type_id( $prefix, $name ) {
		return "{$prefix}_{$name}";
	}

	public static function get_field_id( $prefix, $type, $field ) {
		return "_{$prefix}_{$type}_{$field}";
	}

	// TODO also generate labels for post_updated_messages. Yup. We need another hook.
	public static function get_labels( /* string */ $singular, /* string */ $plural ) {
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
}