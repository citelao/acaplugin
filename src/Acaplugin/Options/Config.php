<?php
namespace Acaplugin\Options;

// based off https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/options-and-settings-pages/theme-options-cmb.php
class Config {
	private static $instance = null;

	public static function get_instance($prefix) {
		if( is_null( self::$instance ) ) {
			self::$instance = new self($prefix);
		}

		return self::$instance;
	}

	private $prefix = '';
	private $key = 'acac_config';
	private $title = 'Manage auditions';
	private $options_page = '';
	private $metabox_id = 'acac_options_metabox';

	private function __construct($prefix) {
		$this->prefix = $prefix;

		// Register options
		add_action( 'admin_init', array( $this, 'init' ) );

		// Add the options page
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

		// Add metabox
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
	}

	public function init() {
		// Register new settings
		register_setting( $this->key, $this->key );
	}

	public function add_menu_page() {
		$this->options_page = add_menu_page($this->title, 
			$this->title,
			'manage_options', 
			$this->key, 
			array( $this, 'render_options_page' ) );

		// Include CMB CSS in the head to avoid FOUC
		add_action( "admin_print_styles-{$this->options_page}", 
			array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	public function render_options_page() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	public function add_options_page_metabox() {
		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", 
			array( $this, 'settings_notices' ), 
			10, 
			2 );

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		$cmb->add_field( array(
			'name' => __( 'Auditions stage', $this->prefix ),
			'desc' => __( 'What stage are auditions?', $this->prefix ),
			'id'   => 'stage',
			'type' => 'select',
			'default' => 'closed',
			'options' => array(
				'closed' => __( 'Closed', $this->prefix ),
				'auditionees' => __( 
					'Auditionees can register/groups can choose callbacks',
					$this->prefix ),
				'callbacks' => __( '
					No registration/groups can view callbacks', 
					$this->prefix )
			),
		) );
		
		$cmb->add_field( array(
			'name' => __( 'Callback dates', $this->prefix ),
			'desc' => __( 'What dates are callbacks?', $this->prefix ),
			'id'   => 'callback_dates',
			'type' => 'text_date',
			'repeatable' => true
		) );
	}

	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}
		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'myprefix' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}
}