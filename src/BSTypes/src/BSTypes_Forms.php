<?php

class BSTypes_Forms {
	public function render_many_to_many( $field_args, 
		$escaped_value, 
		$object_id, 
		$object_type, 
		$field_type_object ) {
		print_r($field_args);

		$selected = split(',', $escaped_value);

		echo $field_type_object->input();
	}
}