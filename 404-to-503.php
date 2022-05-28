<?php

/**
 * Plugin Name:     404 to 503
 * Plugin URI:      https://bizbudding.com
 * Description:     Converts 404 page to a 503 header error.
 * Version:         0.1.0
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main bb404_to_503 Class.
 *
 * @since 0.1.0
 */
final class bb404_to_503 {
	/**
	 * @var   bb404_to_503 The one true bb404_to_503
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main bb404_to_503 Instance.
	 *
	 * Insures that only one instance of bb404_to_503 exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    bb404_to_503::setup_constants() Setup the constants needed.
	 * @uses    bb404_to_503::includes() Include the required files.
	 * @uses    bb404_to_503::hooks() Activate, deactivate, etc.
	 * @see     bb404_to_503()
	 * @return  object | bb404_to_503 The one true bb404_to_503
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new bb404_to_503;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'bb404_T0_503_VERSION' ) ) {
			define( 'bb404_T0_503_VERSION', '0.1.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'bb404_T0_503_PLUGIN_DIR' ) ) {
			define( 'bb404_T0_503_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path.
		if ( ! defined( 'bb404_T0_503_INCLUDES_DIR' ) ) {
			define( 'bb404_T0_503_INCLUDES_DIR', bb404_T0_503_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'bb404_T0_503_PLUGIN_URL' ) ) {
			define( 'bb404_T0_503_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'bb404_T0_503_PLUGIN_FILE' ) ) {
			define( 'bb404_T0_503_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'bb404_T0_503_BASENAME' ) ) {
			define( 'bb404_T0_503_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_filter( 'status_header',     [ $this, 'maybe_change_status_header' ], 10, 4 );
		add_action( 'template_redirect', [ $this, 'maybe_add_retry_after' ] );
		add_action( 'admin_init',        [ $this, 'updater' ] );
	}

	/**
	 * Changes the default status header.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function maybe_change_status_header( $header, $code, $description, $protocol ) {
		if ( ! $this->should_change() ) {
			return $header;
		}

		$text = get_status_header_desc( 503 );
		return "{$protocol} 503 {$text}";
	}

	/**
	 * Adds the 'Retry-After' header.
	 * This tells Google how long to wait before coming back.
	 * This does not mean Google will crawl again at exactly that time,
	 * but it'll ensure Google doesn't come back around to take a look anytime before then.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function maybe_add_retry_after() {
		if ( ! $this->should_change() ) {
			return;
		}

		$weeks = 2;
		$retry = WEEK_IN_SECONDS * $weeks;

		header( "Retry-After: {$retry}" );
	}

	/**
	 * If status should be changed.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	function should_change() {
		return is_404();
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {
		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/bizbudding/404-to-503/', __FILE__, '404-to-503' );
	}
}

/**
 * The main function for that returns bb404_to_503
 *
 * The main function responsible for returning the one true bb404_to_503
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = bb404_to_503(); ?>
 *
 * @since 0.1.0
 *
 * @return object|bb404_to_503 The one true bb404_to_503 Instance.
 */
function bb404_to_503() {
	return bb404_to_503::instance();
}

// Get bb404_to_503 Running.
bb404_to_503();
