<?php

/**
 * Plugin Name:      RW Remote Auth Client
 * Plugin URI:       https://github.com/rpi-virtuell/rw_remote_auth_client
 * Description:      Connect a wordpress instance to a RW Remoth Auth Server and syncronizes the userdata.
 * Author:           Frank Neumann-Staude
 * Version:          0.4.0
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
    static public $version = "0.4.0";

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
     * @var     mixed
     * @since   2.8
     * @access  public
     */
    static public $plugin_dir = NULL;

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
     */
    static public $cookie_name = 'remote_login_referrer';
    /**
     * @var     string
     * @since   0.3.0
     * @access  public
     */
    static public $usermeta_last_visit = 'rw_last_visited_page';

    /**
     * @var     string
     * @since   0.2.0
     * @access  public
     */
    static public $notice = '';

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

        // url to plugins root
        self::$plugin_url = plugins_url('/',__FILE__);

        // plugins root
        self::$plugin_dir = plugin_dir_path(__FILE__);

        // Load the textdomain
        $this->load_plugin_textdomain();

        // Add Filter & Actions

        add_action( 'admin_post_rw_remote_auth_client_network_settings',
                                                    array( 'RW_Remote_Auth_Client_Options', 'network_settings' ) );
        add_action( 'admin_init',                   array( 'RW_Remote_Auth_Client_Options', 'register_settings' ) );
        add_action( 'admin_menu',                   array( 'RW_Remote_Auth_Client_BPGroup', 'add_adminpage' ) );
        add_action( 'init',                         array( 'RW_Remote_Auth_Client', 'notice' ) );
        add_action( 'init',                         array( 'RW_Remote_Auth_Client_Helper', 'init' ) );
        add_action( 'template_redirect',            array( 'RW_Remote_Auth_Client_Helper', 'save_last_visited_page' ) );

        add_action( 'wp_redirect',                  array( 'RW_Remote_Auth_Client_User', 'add_existing_user'),10,2 );
        add_filter( 'wpmu_validate_user_signup',    array( 'RW_Remote_Auth_Client_User', 'create_new_user'),10,1 );
        add_action( 'user_profile_update_errors',   array( 'RW_Remote_Auth_Client_User', 'add_update_singlesite_user'),10,3 );

        add_action( 'admin_menu',                   array( 'RW_Remote_Auth_Client_Options', 'options_menu' ) );
        add_action( 'network_admin_menu',           array( 'RW_Remote_Auth_Client_Options', 'options_menu' ) );

        add_action( 'plugins_loaded',               array( 'RW_Remote_Auth_Client_Helper', 'manipulate_other_plugins' ), 9999 );
        if ( ! isset( $_GET[ 'wp' ] ) ) { // CAS Maestro Bypass is active
            add_action( 'wp_login',                 array( 'RW_Remote_Auth_Client_User', 'set_password_from_loginserver' ), 10, 2 );
            add_action( 'profile_update',           array( 'RW_Remote_Auth_Client_User', 'change_password_on_login_server' ),10, 2 );
            add_action( 'password_reset',           array( 'RW_Remote_Auth_Client_User', 'reset_password_on_login_server' ),10, 2 );

        }
        add_filter( 'plugin_action_links_' . self::$plugin_base_name, array( 'RW_Remote_Auth_Client_Options', 'plugin_settings_link') );
        if ( is_multisite() ) {
            add_action( 'wpmu_new_user', array( 'RW_Remote_Auth_Client_User', 'create_mu_user_on_login_server' ) );
        } else {
            add_action( 'user_register',        array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server' ),10, 1 );
        }
        //add_action( 'profile_update',        array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server' ),997, 1 );



        //hooks of the the plugin user_registration
        add_action( 'user_registration_before_register_user_action',        array( 'RW_Remote_Auth_Client_User', 'validate_ur_user' ),10, 1 );
        add_action( 'user_registration_after_register_user_action',         array( 'RW_Remote_Auth_Client_User', 'register_ur_user' ),10, 3 );
        add_filter( 'ur_get_template',                                      array( 'RW_Remote_Auth_Client_Helper','get_ur_template' ),10, 2 );


        //add_action( 'user_registration_after_save_profile_validation',        array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server' ),998, 1 );
        add_action( 'admin_notices',                array( 'RW_Remote_Auth_Client_Helper', 'check_remote_connetion' ));

        add_filter( 'http_request_args',            array( 'RW_Remote_Auth_Client_Helper', 'http_request_args' ), 9999 );
        add_filter( 'login_redirect',               array( 'RW_Remote_Auth_Client_Helper', 'login_redirect' ), 10, 3 );
        add_action( 'login_init',                   array( 'RW_Remote_Auth_Client_Helper', 'validate_login' ),1  );
        add_action( 'rw_auth_remote_check_server',  array( 'RW_Remote_Auth_Client_Installation', 'check_server') );
        add_filter( 'http_request_args',            array( 'RW_Remote_Auth_Client_User','set_http_request_args'), 999,2);
        add_filter( 'user_new_form',                array( 'RW_Remote_Auth_Client_User','user_new_form_check_remote_auth_server'));
        add_filter( 'validate_username',            array( 'RW_Remote_Auth_Client_Helper', 'validate_username' ), 10, 2 );
        add_filter( 'wpmu_active_signup',           array( 'RW_Remote_Auth_Client_Helper', 'wpmu_active_signup' ) );

        //user creation on login server after activation
        add_action( 'wpmu_activate_user',           array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server') );
        add_action( 'wpmu_new_user',                array( 'RW_Remote_Auth_Client_User', 'create_user_on_login_server') );

        add_filter( 'lostpassword_url',             array( 'RW_Remote_Auth_Client_Helper', 'lostpassword_url' ),999,2 );

        add_filter( 'register',                     array( 'RW_Remote_Auth_Client_Helper', 'check_registration' ) );
        add_filter( 'register_url',                 array( 'RW_Remote_Auth_Client_Helper', 'check_registration' ) );

        add_filter( 'login_form_register',          array( 'RW_Remote_Auth_Client_User', 'check_users_can_register' ) );

        //stay on front page if no access to backend
        add_action( 'admin_page_access_denied' ,    array( 'RW_Remote_Auth_Client_Helper', 'stay_at_frontpage'));


        //only for remotetest
        add_action( 'admin_init',                    array( 'RW_Remote_Auth_Client_Test', 'init' ) );
        add_filter('gettext', array( 'RW_Remote_Auth_Client_Helper', 'translate_text' ) );
        add_filter('ngettext', array( 'RW_Remote_Auth_Client_Helper', 'translate_text' ) );

        //allow autocomplete users in add users form for site admins
        add_filter( 'autocomplete_users_for_site_admins', '__return_true');
        //modify autocomplete behavior and get user list from remote auth server
        remove_action( 'wp_ajax_autocomplete-user', 'wp_ajax_autocomplete_user', 1);
        add_action( 'wp_ajax_autocomplete-user', array( 'RW_Remote_Auth_Client_Helper','wp_ajax_autocomplete_user'),1);

        /*check cas user via ajax*/
        add_action( 'wp_enqueue_scripts',       array( 'RW_Remote_Auth_Client_Helper','enqueue_js' ) ,10);
        add_action( 'admin_enqueue_scripts',    array( 'RW_Remote_Auth_Client_Helper','enqueue_js' ) ,9999);
        add_action( 'wp_ajax_rw_remote_auth_client_cas_user_status' ,array( 'RW_Remote_Auth_Client_Helper','get_loggedin_cas_user_status' )  );

        //because WordPress does not automatically do ajax actions for users not logged-in,we need this as workarround
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'rw_remote_auth_client_cas_user_status' ):
            do_action( 'wp_ajax_' . $_REQUEST['action'] );
            do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
        endif;


        //set usermeta flag while user switching is active
        add_action ('switch_to_user', array( 'RW_Remote_Auth_Client_Helper','switch_user' ) );
        add_action ('switch_back_user', array( 'RW_Remote_Auth_Client_Helper','revoke_switched_user' ) );
        add_action ('wp_login', array( 'RW_Remote_Auth_Client_Helper','revoke_switched_user' ) );


        /*check cas user via ajax end*/

        add_action( 'load-index.php',  array( 'RW_Remote_Auth_Client_BPGroup','refesh_member_from_bp_groups' ) );

        add_action( 'login_init',  array( 'RW_Remote_Auth_Client_Helper','catch_login_form_data' ) );

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
     * @since   0.1
     * @access  public
     * @uses    load_plugin_textdomain, plugin_basename
     * @filters rw_remote_auth_client_translationpath path to translations files
     * @return  void
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rw_remote_auth_client_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
    }

    /**
     * Get a value of the plugin header
     *
     * @since   0.1
     * @access  protected
     * @param   string $value
     * @uses    get_plugin_data, ABSPATH
     * @return  string The plugin header value
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
     * @access  public
     * @return  string textdomain
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
     * @access  public
     * @return  string Domain Path
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
     *      Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
     * @return  string
     */
    public static function get_plugin_data( $value = 'Version' ) {

        if ( ! function_exists( 'get_plugin_data' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $plugin_data  = get_plugin_data ( __FILE__ );
        $plugin_value = $plugin_data[ $value ];

        return $plugin_value;
    }
    public static function notice( ) {
        if(isset($_GET['rw_remote_auth_client_update'])){

            self::notice_admin('success',$_GET['rw_remote_auth_client_update']);

        }
        if(isset($_GET['rw_remote_auth_client_error'])){

            self::notice_admin('error',$_GET['rw_remote_auth_client_error']);

        }
    }
    /**
     * creates an admin notification on admin pages
     *
     * @since   0.2.0
     * @uses     _notice_admin
     * @access  public
     * @param label         $value string,  default = 'info'
     *        error, warning, success, info
     * @param message       $value string
     * @param $dismissible  $value bool,  default = false
     *
     */
    public static function notice_admin($label=info, $message, $dismissible=false ) {
        $notice = array(
            'label'             =>  $label
        ,   'message'           =>  $message
        ,   'is-dismissible'    =>  (bool)$dismissible

        );
        self::_notice_admin($notice);
    }

    /**
     * creates an admin notification on admin pages
     *
     * @since   0.2.0
     * @uses     _notice_admin
     * @access  private
     * @param $value array
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
     */

    static function _notice_admin($notice) {

        self::$notice = $notice;

        add_action( 'admin_notices',function(){

            $note = RW_Remote_Auth_Client::$notice;
            $note['IsDismissible'] =
                (isset($note['is-dismissible']) && $note['is-dismissible'] == true) ?
                    ' is-dismissible':'';
            ?>
            <div class="notice notice-<?php echo $note['label']?><?php echo $note['IsDismissible']?>">
                <p><?php echo __( $note['message'] ,RW_Remote_Auth_Client::get_textdomain() ); ?></p>
            </div>
            <?php
        });

    }
}


if ( class_exists( 'RW_Remote_Auth_Client' ) ) {

    add_action( 'plugins_loaded', array( 'RW_Remote_Auth_Client', 'get_instance' ) );

    require_once 'inc/RW_Remote_Auth_Client_Autoloader.php';
    RW_Remote_Auth_Client_Autoloader::register();

    register_activation_hook( __FILE__, array( 'RW_Remote_Auth_Client_Installation', 'on_activate' ) );
    register_uninstall_hook(  __FILE__, array( 'RW_Remote_Auth_Client_Installation', 'on_uninstall' ) );
    register_deactivation_hook( __FILE__, array( 'RW_Remote_Auth_Client_Installation', 'on_deactivation' ) );
}
