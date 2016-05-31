<?php
/**
 * Plugin Name: BSTypes (dev)
 */

// see https://github.com/WebDevStudios/CMB2/blob/master/init.php
if ( ! class_exists( 'BSTypes_Bootstrap_00', false ) ) {
	class BSTypes_Bootstrap_00 {
		const PRIORITY = 8999;

		private static $instance = null;

		public function init() {
			if( null == BSTypes_Bootstrap_00::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			if ( defined('CMB2_LOADED') ) {
				print("uhoh");
			}

			if ( ! defined( 'BSTYPES_LOADED' ) ) {
				define( 'BSTYPES_LOADED', true );
			}

			add_action( 'init', array( $this, 'bootstrap' ), self::PRIORITY);
		}

		public function bootstrap() {
			if ( ! defined( 'BSTYPES_DIR' ) ) {
				define( 'BSTYPES_DIR', trailingslashit( dirname( __FILE__ ) ) );
			}

			require_once('src/functions.php');
			require_once('src/BSType.php');
			spl_autoload_register('bstypes_autoload_class');

			// These two actions, `bstypes_init` and `bstypes_admin_init` are
			// the two earliest times you can be sure you have the BSTypes
			// public functions.
			do_action( 'bstypes_init' );
			if ( is_admin() ) {
				do_action( 'bstypes_admin_init' );
			}
		}
	}

	BSTypes_Bootstrap_00::init();
}