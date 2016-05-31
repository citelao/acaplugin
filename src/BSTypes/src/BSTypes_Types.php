<?php

class BSTypes_Types { 
	private static $instance = null;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private $types = array();

	private function __construct() {}

	public function get( $prefix, $name ) {
		$id = BSTypes_Util::get_type_id($prefix, $name);

		if ( array_key_exists( $id, $this->types ) )  {
			return $this->types[$id];
		}

		return false;
	}

	public function create(
		/* string */ $prefix,
		/* string */ $name,
		/* string */ $plural,
		$args = array() ) {

		$id = BSTypes_Util::get_type_id($prefix, $name);

		// if already exists, throw error
		if ( $this->get($prefix, $name) ) {
			throw new InvalidArgumentException(
				"Cannot recreate type {$name} (id: {$id}). Use get() instead.");
		}

		// create the class
		$type = new BSType( $prefix, $name, $plural, $args );
		$this->types[$type->get_id()] = $type;

		return $type;
	}

}