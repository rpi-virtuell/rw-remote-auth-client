<?php

/**
 * Plugin Name:      RW Remote Auth Client
 * Plugin URI:       https://github.com/rpi-virtuell/rw_remote_auth_client
 * Description:
 * Author:           Frank Staude
 * Version:          0.1.12
 * Licence:          GPLv3
 * Author URI:       http://staude.net
 * Text Domain:      rw_remote_auth_client
 * Domain Path:      /languages
 * GitHub Plugin URI: https://github.com/rpi-virtuell/rw-remote-auth-client
 * GitHub Branch:     master
 */

class RW_Remote_Auth_Client {
    /**
     * Plugin version
     *
     * @var     string
     * @since   0.1
     * @access  public
     */
    static public $version = "0.1.12";

    /**
     * Singleton object holder
     *
     * @var     mixed
     * @since   0.1
     * @access  private
     */
    static private $instance = NULL;

    /**
     * @var     mixed
     * @since   0.1
     * @access  public
     */
    static public $plugin_name = NULL;

    /**
     * @var     mixed
     * @since   0.1
     * @access  public
     */
    static public $textdomain = NULL;

    /**
     * @var     mixed
     * @since   0.1
     * @access  public
     */
    static public $plugin_base_name = NULL;

    /**
     * @var     mixed
     * @since   0.1
     * @access  public
     */
    static public $plugin_url = NULL;

    /**
     * @var     string
     * @since   0.1
     * @access  public
     */
    static public $plugin_filename = __FILE__;

    /**
     * @var     string
     * @since   0.1
     * @access  public
     */
    static public $plugin_version = '';

	/**
	 * @var     string
	 * @since   0.1.2
	 * @access  public
	 */	static public $cookie_name = 'remote_login_referrer';

    /**
     * Plugin constructor.
     *
     * @since   0.1
     * @access  public
     * @uses    plugin_basename
     * @action  rw_remote_auth_clinet_init
     */
    public function __construct () {
        // set the textdomain variable
        self::$textdomain = self::get_textdomain();

        // The Plugins Name
        self::$plugin_name = $this->get_plugin_header( 'Name' );

        // The Plugins Basename
        self::$plugin_base_name = plugin_basename( __FILE__ );

        // The Plugins Version
        self::$plugin_version = $this->get_plugin_header( 'Version' );

        // Load the textdomain
        $this->load_plugin_textdomain();

        // Add Filter & Actions

        add_action( 'admin_init',           array( 'RW_Remote_Auth_Client_Options', 'register_settings' ) );
        add_action( 'admin_menu',           array( 'RW_Remote_Auth_Client_Options', 'options_menu' ) );
	    add_action( 'plugins_loaded',       array( 'RW_Remote_Auth_Client_Helper', 'manipulate_other_plugins' ), 9999 );
	    if ( ! isset( $_GET[ 'wp' ] ) ) { // CAS Maestro Bypass is active
		    add_action( 'wp_login',             array( 'RW_Remote_Auth_Client_User', 'get_password_from_loginserver' ), 10, 2 );
		    add_action( 'profile_update',       array( 'RW_Remote_Auth_Client_User', 'change_password_on_login_server' ),10, 2 );
		    if ( is_multisite() ) {
			    add_action( 'wpmu_new_user', array( 'RW_Remote_Auth_Client_User', 'create_mu_user_on_login_server' ) );
		    } else {
		        add_action( 'user_register',        array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server' ),10, 1 );
		    }
	    }
        add_filter( 'plugin_action_links_' . self::$plugin_base_name, array( 'RW_Remote_Auth_Client_Options', 'plugin_settings_link') );
	    add_filter( 'http_request_args',    array( 'RW_Remote_Auth_Client_Helper', 'http_request_args' ), 9999 );
	    add_filter( 'login_redirect', array( 'RW_Remote_Auth_Client_Helper', 'login_redirect' ), 10, 3 );
        add_action( 'login_init',           array( 'RW_Remote_Auth_Client_Helper', 'validate_login' ),1  );
	    add_action( 'rw_auth_remote_check_server', array( 'RW_Remote_Auth_Client_Installation', 'check_server') );
	    add_action( 'admin_init',  array( 'RW_Remote_Auth_Client_Helper', 'admin_init' ) );
    }

    /**
     * Creates an Instance of this Class
     *
     * @since   0.1
     * @access  public
     * @return  RW_Remote_Auth_Client
     */
    public static function get_instance() {

        if ( NULL === self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * Load the localization
     *
     * @since	0.1
     * @access	public
     * @uses	load_plugin_textdomain, plugin_basename
     * @filters rw_remote_auth_client_translationpath path to translations files
     * @return	void
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rw_remote_auth_client_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
    }

    /**
     * Get a value of the plugin header
     *
     * @since   0.1
     * @access	protected
     * @param	string $value
     * @uses	get_plugin_data, ABSPATH
     * @return	string The plugin header value
     */
    protected function get_plugin_header( $value = 'TextDomain' ) {

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php');
        }

        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }

    /**
     * get the textdomain
     *
     * @since   0.1
     * @static
     * @access	public
     * @return	string textdomain
     */
    public static function get_textdomain() {
        if( is_null( self::$textdomain ) )
            self::$textdomain = self::get_plugin_data( 'TextDomain' );

        return self::$textdomain;
    }

    /**
     * get the textdomain path
     *
     * @since   0.1
     * @static
     * @access	public
     * @return	string Domain Path
     */
    public static function get_textdomain_path() {
        return self::get_plugin_data( 'DomainPath' );
    }

    /**
     * return plugin comment data
     *
     * @since   0.1
     * @uses    get_plugin_data
     * @access  public
     * @param   $value string, default = 'Version'
     *		Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
     * @return  string
     */
    public static function get_plugin_data( $value = 'Version' ) {

        if ( ! function_exists( 'get_plugin_data' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $plugin_data  = get_plugin_data ( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }
}


if ( class_exists( 'RW_Remote_Auth_Client' ) ) {

    add_action( 'plugins_loaded', array( 'RW_Remote_Auth_Client', 'get_instance' ) );

    require_once 'inc/RW_Remote_Auth_Client_Autoloader.php';
    RW_Remote_Auth_Client_Autoloader::register();

    register_activation_hook( __FILE__, array( 'RW_Remote_Auth_Client_Installation', 'on_activate' ) );
    register_uninstall_hook(  __FILE__,	array( 'RW_Remote_Auth_Client_Installation', 'on_uninstall' ) );
    register_deactivation_hook( __FILE__, array( 'RW_Remote_Auth_Client_Installation', 'on_deactivation' ) );
}
