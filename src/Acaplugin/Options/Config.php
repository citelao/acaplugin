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

		$plugin_info = '
<p>
	This plugin does everything you need to run Wash U a cappella auditions.
	You can read a documented overview TODO here.
</p>
<p>
	Besides adding auditionees, groups, and songs to Wordpress, it also adds
	the registration and preference forms that auditionees need to choose their
	groups. They can be rendered using the <code>[acac_registration]</code> and
	<code>[acac_prefs]</code> shortcodes, respectively.
</p>
<p>
	I\'d like to think that the process (and the documentation provided) are
	enough to get you through the hell of WashU auditions, but as long as this
	message is on this website, I can be reached in an emergency. Contact
	info should be on my website at 
	<a href="http://ben.stolovitz.com/">ben.stolovitz.com</a>.
</p>
<p>
	Good luck! <br />
	— Ben
</p>
';

		$cmb->add_field( array(
			'name' => __( 'Preparing for auditions', $this->prefix ),
			'desc' => __( $plugin_info, $this->prefix ),
			'id'   => 'plugin_info',
			'type' => 'title',
			// 'attributes' => $callback_attributes
		) );

		$cmb->add_field( array(
			'name' => __( 'Auditions stage', $this->prefix ),
			'desc' => __( 'What stage are auditions?', $this->prefix ),
			'id'   => 'stage',
			'type' => 'select',
			'default' => 'closed',
			'options' => array(
				'closed' => __( 'Closed', $this->prefix ),
				'auditions' => __( 
					'First round — Auditionees can register/groups can choose callbacks/no pref cards',
					$this->prefix ),
				'callbacks' => __( 
					'Second round — No public registration/groups can view callbacks/pref cards open', 
					$this->prefix ),
				'draft' => __( 
					'Draft — No public registration/groups can view callbacks & pref cards/pref cards closed', 
					$this->prefix )
			),
		) );
		
		$cmb->add_field( array(
			'name' => __( 'Callback dates', $this->prefix ),
			'desc' => __( 'What dates are callbacks? NOTE: Changing this while auditions are open may hide data from admin page.', $this->prefix ),
			'id'   => 'callback_dates',
			'type' => 'text_date',
			'repeatable' => true,
			// 'attributes' => $callback_attributes
		) );

		$registration_description = '
<p>This lets you configure the email new auditionees receive when they register.</p>
<p>You can use some special <a href="http://www.wpbeginner.com/glossary/shortcodes/">shortcodes</a> to add personal information to the email. They are listed below.</p>
<p>Since some of them depend on the information above (callback dates, e.g.), save the form once before editing the content below.</p>
<h4>Available shortcodes:</h4>
<ul>
	<li><code>first_name</code></li>
	<li><code>last_name</code></li>
	<li><code>email</code></li>
	<li><code>telephone</code></li>
	<li><code>residence</code></li>';

	if( ! get_option( 'acac_config' ) ||
		! array_key_exists( 'callback_dates', get_option( 'acac_config' )) ||
		! get_option( 'acac_config' )['callback_dates'] ) {
		add_action( 'admin_notices', array( $this, 'warn_no_callback_dates' ) );
		return;
	}
	$callback_dates = get_option( 'acac_config' )['callback_dates'];

	foreach ( $callback_dates as $key => $date ) {
		$date = strtotime($date);
		$registration_description .= '<li><code>conflict-' . date( 'm-d', $date ) . '</code></li>';
	}

	$registration_description .= '</ul>
<h4>Testing</h4>
<p>If you want to test this new email, save the form below and do a test registration.</p>
<p>This was the most hassle-free way of setting it up. Sorry if it\'s a bit harder -- Ben</p>';

		$cmb->add_field( array(
			'name' => __( 'Registration email', $this->prefix ),
			'desc' => __( $registration_description, $this->prefix ),
			'id'   => 'registration_email_info',
			'type' => 'title',
			// 'attributes' => $callback_attributes
		) );

		$cmb->add_field( array(
			'name' => __( 'Registration email subject', $this->prefix ),
			'desc' => __( 'Subject of the email new auditionees receive', $this->prefix ),
			'id'   => 'registration_subject',
			'type' => 'text',
			// 'attributes' => $callback_attributes
		) );

		$cmb->add_field( array(
			'name' => __( 'Registration email message', $this->prefix ),
			'desc' => __( 'Message of the email new auditionees receive.', $this->prefix ),
			'id'   => 'registration_message',
			'type' => 'wysiwyg'
			// 'attributes' => $callback_attributes
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