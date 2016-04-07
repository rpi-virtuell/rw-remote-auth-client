<?php


class RW_Remote_Auth_Client_Helper {

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
}