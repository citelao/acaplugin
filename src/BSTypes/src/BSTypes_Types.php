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

	// public function get( $name = '' ) {
	// 	if ( ! $name ) {
	// 		return $this->types;
	// 	}

	// 	if ( array_key_exists( $name, $this->get_types() ) )  {
	// 		return $this->get_types()[$name];
	// 	}
	// }

	public function create(
		/* string */ $prefix,
		/* string */ $name,
		/* string */ $plural,
		$args = array() ) {

		// if already exists, throw error

		// create the class
		$type = new BSType( $prefix, $name, $plural, $args );
		// $this->types[]

		return $type;
	}

}