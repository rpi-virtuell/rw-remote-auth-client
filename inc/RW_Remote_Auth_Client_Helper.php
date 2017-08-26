<?php


class RW_Remote_Auth_Client_Helper {


	/**
	 * called on Hook init
	 */
    static public function init(){
	    add_shortcode( 'login-form',  array( 'RW_Remote_Auth_Client_Helper', 'login_form_shortcode'));
    }

	static public function manipulate_other_plugins() {

		// Nur wenn Option gewählt.
		// @todo Option schaltbar machen
		// @todo Password Feld
		add_filter ( 'show_password_fields', array( 'RW_Remote_Auth_Client_Helper', 'show_password_fields' ),9999,2 );

		if(isset($GLOBALS['CAS_Maestro'])){
			$CAS_Maestro = $GLOBALS['CAS_Maestro'];

			remove_action('lost_password',              array(&$CAS_Maestro, 'disable_function'));
			remove_action('retrieve_password',        	array(&$CAS_Maestro, 'disable_function'));
			remove_action('password_reset',             array(&$CAS_Maestro, 'disable_function'));
			if( defined('XMLRPC_REQUEST') ){
				add_action( 'init', function() {
					remove_filter('authenticate', 	array(&$GLOBALS['CAS_Maestro'], 'validate_login'),30);

				});
				remove_all_filters('login_url');
			}
			add_filter ('cas_maestro_change_users_capability',function($caps){
				return 'manage_options';
			});
			// more strict
			add_action( 'admin_menu', function(){
				if(!is_super_admin()){
					//remove CAS Maestro settings
					remove_menu_page( 'wpcas_settings' );
					remove_submenu_page( 'options-general.php', 'wpcas_settings' );
				}

			},9999);

		}


	}

	/**
	 * @return bool
	 */
	static public function show_password_fields($show,$profiluser) {

		return true;
	}

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	static public function http_request_args( $args ) {
		$args['sslverify'] = false;
		return ( $args );
	}

	/**
	 * Save Referrer on Loginpage
	 *
	 * @since   0.1.2
	 * @access  public
	 * @static
	 * @return  void
	 */
	static public function validate_login( ) {
		if ( ! is_user_logged_in() && ! isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && isset( $_SERVER['HTTP_REFERER'] ) )  {
			$referrer = $_SERVER['HTTP_REFERER'];
			if(strpos($referrer,'/wp-login.php') !== false ){
				$referrer = home_url();
			}
			setcookie( RW_Remote_Auth_Client::$cookie_name, $referrer, time()+ ( 5 * 60 )  );
		} elseif ( isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && is_user_logged_in() ) {
			//Cookie löschen wenn es noch existiert
			setcookie( RW_Remote_Auth_Client::$cookie_name,  null, time() - ( 60 * 60 ) );
		}
	}	
	

	/**
	 * prepare user redirection
	 *
	 * @since   0.1.2
	 * @access  public
	 * @static
	 * @return  void
	 */
	static public function login_redirect( $redirect_url, $requested_redirect_to, $user ) {
		if(!is_wp_error($user)){
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			do_action( 'wp_login', $user->user_login );
		}else{
			//@TODO handle error
		}
		if (  isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] != ''  && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] != get_site_url() . '/' ) {
			$redirect_url = $_COOKIE[RW_Remote_Auth_Client::$cookie_name];
			setcookie( RW_Remote_Auth_Client::$cookie_name,  null, time() - ( 60 * 60 ) );
			return ( $redirect_url );
		}
		if (  is_user_logged_in() && function_exists( 'is_buddypress' ) && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] == get_site_url() . '/') {
			$redirect_url =    bp_get_activity_root_slug();
			setcookie( RW_Remote_Auth_Client::$cookie_name,  null, time() - ( 60 * 60 ) );
			return ( $redirect_url );
		}
		if (  isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] != '' ) {
			$redirect_url = $_COOKIE[RW_Remote_Auth_Client::$cookie_name];
			setcookie( RW_Remote_Auth_Client::$cookie_name,  null, time() - ( 60 * 60 ) );
			return ( $redirect_url );
		}
		return ( $redirect_url );
	}

	/**
	 * 
	 * @since   0.1.11
	 * @access  public
	 * @static
	 */
	static public function admin_init () {

		if (  isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] != '' ) {
			$redirect_url =    $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ];
			if ( isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && is_user_logged_in() ) {
				//delete Cookie if exists
				setcookie( RW_Remote_Auth_Client::$cookie_name,  null, time() - ( 60 * 60 ) );
			}else{
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 *
	 * @since   0.2.2
	 * @access  public
	 * @static
	 */
	static public function validate_username( $valid, $username ) {

		$errors = new WP_Error();
		if ( preg_match( '/[^a-z0-9]/', $username ) ) {
			$errors->add( 'user_name', __( 'Usernames can only contain lowercase letters (a-z) and numbers.' ) );
			$valid =  false;
		}
		return $valid;
	}

	/**
	 *
	 * @since   0.2.2
	 * @access  public
	 * @static
	 */
	static public function translate_text($translated) {
		$translated = str_ireplace("Benutzernamen können nur Buchstaben, Zahlen, \".\", \"-\" und @ enthalten", 'Benutzernamen dürfen nur kleingeschriebene Buchstaben (a-z) und Zahlen enthalten', $translated);
		return $translated;
	}

	/**
	 *
	 * @since   0.2.6
	 * @access  public
	 * @static
	 */
	static public function check_registration( $registration_url ) {

		$response = RW_Remote_Auth_Client_User::remote_say_hello();

		if ( $response->notice == 'success') {
			return $registration_url;
		} else {
			return  __( 'Registration is disabled', RW_Remote_Auth_Client::$textdomain );
		}

	}

	/**
	 *
	 * @since   0.2.6
	 * @access  public
	 * @static
	 */
	static public function wpmu_active_signup( $active_signup ) {
		$response = RW_Remote_Auth_Client_User::remote_say_hello();

		if ( $response->notice == 'success') {
			return $active_signup;
		} else {
			return 'none';
		}
	}


	/**
	 *
	 * @since   0.2.7
	 * @access  public
	 * @static
	 */
	static public  function wp_ajax_autocomplete_user() {
		if ( ! is_multisite() || ! current_user_can( 'promote_users' ) || wp_is_large_network( 'users' ) )
			wp_die( -1 );

		/** This filter is documented in wp-admin/user-new.php */
		if ( ! current_user_can( 'manage_network_users' ) && ! apply_filters( 'autocomplete_users_for_site_admins', false ) )
			wp_die( -1 );

		$return = array();

		// Check the type of request
		// Current allowed values are `add` and `search`
		if ( isset( $_REQUEST['autocomplete_type'] ) && 'search' === $_REQUEST['autocomplete_type'] ) {
			$type = $_REQUEST['autocomplete_type'];
		} else {
			$type = 'add';
		}

		// Check the desired field for value
		// Current allowed values are `user_email` and `user_login`
		if ( isset( $_REQUEST['autocomplete_field'] ) && 'user_email' === $_REQUEST['autocomplete_field'] ) {
			$field = $_REQUEST['autocomplete_field'];
		} else {
			$field = 'user_login';
		}

		// Exclude current users of this blog
		if ( isset( $_REQUEST['site_id'] ) ) {
			$id = absint( $_REQUEST['site_id'] );
		} else {
			$id = get_current_blog_id();
		}

		$include_blog_users = ( $type == 'search' ? get_users( array( 'blog_id' => $id, 'fields' => 'ID' ) ) : array() );
		$exclude_blog_users = ( $type == 'add' ? get_users( array( 'blog_id' => $id, 'fields' => 'ID' ) ) : array() );


		$request = array(   'cmd' => 'user_get_list',
		                    'data' => array (
			                    'term' => $_REQUEST['term'],
			                    'include' =>$include_blog_users,
			                    'exclude' => $exclude_blog_users,
		                    )
		);
		$response =  RW_Remote_Auth_Client_User::remote_get( $request );


		wp_die(  $response->message );
	}


    /**
     * Ajax Response
     *
     * Antwortet auf dei Ajaxanfrage, ob der angefragte
     * (am login server aktuell angemeldete und per javascript übergebene)
     * $_POST['user'] (user_login) auf dieser wp Instanz registriert ist
     *
     * @since   0.0.2
     * @access  public
     * @static
     * @return array:
     *          status: logged-in, not-logged-in-user, do-loggin, unknown user
     *          name: display name
     *
     * @use_action: wp_ajax_rw_remote_auth_client_cas_user_status
     */
    public static function get_loggedin_cas_user_status(){

        $login_name = strval( $_POST['user'] );

        $user = get_user_by('login',$login_name);
        if($user && is_a($user,'WP_User')){



            if(is_user_logged_in() && wp_get_current_user() == $user){
                $status = 'logged-in';
            }elseif(is_user_logged_in() && wp_get_current_user() != $user){
                $status = 'not-logged-in-user';
            }else{
                $status = 'do-loggin';
            }

            echo json_encode(array(
                'success' =>  true
            ,'name'=>$user->display_name
            ,'status'=>$status
            ,'avatar'=>get_avatar($user->ID)
            ));
        }else{
            echo json_encode(array(
                'success' =>  false
            ,'name'=>'anonym'
            ,'status'=> 'unknown user'

            ));
        }
        die();
    }

    public static function enqueue_js() {

	    wp_enqueue_script( 'rw_cas_accunt_script','//login.reliwerk.de/account.php',array() ,'0.0.2', true );
    	wp_enqueue_script( 'rw_remote_auth_client_ajax_script',RW_Remote_Auth_Client::$plugin_url . '/js/javascript.js' ,array() ,'0.0.2', true);
        wp_localize_script( 'rw_remote_auth_client_ajax_script', 'rw_rac_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    }


    public static function catch_login_form_data(){

	    if ( ! empty( $_POST ) && !isset($_GET['wp']) ) {

	        $redirect_to = '';
		    if(isset($_POST['log']) && isset($_POST['pwd']) ){
				$user_login     = $_POST['log'];
				$user_password  = $_POST['pwd'];
				if(isset($_POST['redirect_to'])){
					$redirect_to = $_POST['redirect_to'];
				}
				?>
				<html>
					<body style="background-color: #1B638A; color:white">
						<table height="100%" width="100%" style="font-family:Verdana, Arial, Helvetica, sans-serif">
							<tr>
								<td align="center" valign="middle">
									Du wirst angemeldet ...
									<form id="cas-login-form" action="https://login.reliwerk.de/wp-login.php" method="post">
										<input type="hidden" name="log" value="<?php echo $user_login; ?>">
										<input type="hidden" name="pwd" value="<?php echo $user_password; ?>">
										<input type="hidden" name="redirect_to" value="https://login.reliwerk.de/wp-cas/login?service=<?php echo $redirect_to; ?>">
										<input type="hidden" value="login" name="ag_type" />
										<input type="hidden" value="1" name="ag_login_accept">
										<input type="hidden" name="reauth" value="0">
									</form>
								</td>
							</tr>
						</table>
						<script>
		                    document.getElementById('cas-login-form').submit();
						</script>
					</body>
				</html>
				<?php
				die();

			}
			return;
        }
		return;

    }


	static public function login_form_shortcode() {

		if ( is_user_logged_in() )
			return '';

		$html = wp_login_form( array( 'echo' => false ) );
		$html .= '<a href ="'.wp_lostpassword_url().'">Passwort vergessen</a>';
		return $html;
	}


}