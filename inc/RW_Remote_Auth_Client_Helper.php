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
	 * Save Referrer on Loginpage and redirct user to referrer page after login
	 *
	 * @since   0.1.2
	 * @access  public
	 * @static
	 * @return  void
	 */
	static public function validate_login( ) {
		if ( isset( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] ) && $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] != '' ) {
			$cookie = $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ];
			unset ( $_COOKIE[ RW_Remote_Auth_Client::$cookie_name ] );
			wp_redirect( $cookie );
			exit;
		} else {
			setcookie( RW_Remote_Auth_Client::$cookie_name, $_SERVER[ 'HTTP_REFERER' ] );
		}
	}

}