<?php
// Exposed functions

// Autoloading a la CMB2
// https://github.com/WebDevStudios/CMB2/blob/master/includes/helper-functions.php
function bstypes_dir( $path = '' ) {
	return BSTYPES_DIR . $path;
}

function bstypes_autoload_class( $class_name ) {
	if ( 0 !== strpos( $class_name, 'BSTypes' ) ) {
		return;
	}

	include_once( bstypes_dir( "src/{$class_name}.php" ) );
}

function bstypes() {
	return BSTypes_Types::get_instance();	
}