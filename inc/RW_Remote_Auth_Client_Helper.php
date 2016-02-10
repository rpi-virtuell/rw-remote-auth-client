<?php


class RW_Remote_Auth_Client_Helper {

	static public function manipulate_other_plugins() {

		// Nur wenn Option gewählt.
		// @todo Option schaltbar machen
		// @todo Password Feld
		add_filter ( 'show_password_fields', array( 'RW_Remote_Auth_Client_Helper', 'show_password_fields' ),9999 );
	}

	/**
	 * @return bool
	 */
	static public function show_password_fields() {
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
			setcookie( RW_Remote_Auth_Client::$cookie_name, $_SERVER['HTTP_REFERER'], time()+ ( 5 * 60 )  );
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
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login );

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
			wp_redirect( $redirect_url );
			exit;
		}
	}

}