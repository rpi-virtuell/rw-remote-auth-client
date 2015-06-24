<?php

class RW_Remote_Auth_Client_User {


    public static function check_remote_user_on_register( $errors, $sanitized_user_login, $user_email ) {
        if ( self::remote_user_exists( $sanitized_user_login ) && ! username_exists( $sanitized_user_login ) ) {
            wp_create_user( $sanitized_user_login, '', $user_email );
        } else {
            self::remote_user_register( $sanitized_user_login, $user_email );
            wp_redirect( get_option( 'rw_remote_auth_client_register_redirect_url' ) );
            exit;
        }
        return $errors;
    }

    public static function check_remote_user_on_multisite_register ( $result ) {
	    if ( self::remote_user_exists( $result['user_name'] ) && ! username_exists( $result['user_name'] ) ) {
            wp_create_user( $result['user_name'], '', $result['user_email'] );
        } else {
		    self::remote_user_register( $result['user_name'], $result['user_email'] );
            wp_redirect( get_option( 'rw_remote_auth_client_register_redirect_url' ) );
		    //var_dump( get_option( 'rw_remote_auth_client_register_redirect_url' ) );
		    //echo "Hier kann ich leider keinen Redirect machen, weil schon Content ausgegeben wurde.";
		    //echo "Wenn ich hier aber nicht die Ausführung abbreche, dann wird der User auf diesem Server auch eingetragen.";
            exit;
        }
        return $result;
    }

	public static function create_mu_user_on_login_server ( $user_id ) {
		global $wpdb;
	  $user = get_user_by( 'id', $user_id );
		if ( is_object( $user)) {
			// UserObject has wrong, temporary password
			// Get correct password from signup table
			$signup = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->signups WHERE user_email = %s", $user->user_email) );
			$meta = maybe_unserialize($signup->meta);
			self::remote_user_register($user->user_login, $user->user_email, $meta['password'] );
		}
	}

	public static function create_user_on_login_server ( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		self::remote_user_register($user->user_login, $user->user_email, $user->password );
	}


    public static function remote_user_exists( $username ) {
        $request = array(   'cmd' => 'user_exists',
                            'data' => array (
                                'user_name' => $username
                            )
                        );
        $json = urlencode( json_encode( $request ) );
        $response = wp_remote_get( RW_Remote_Auth_Client_Options::get_loginserver_endpoint() . $json , array ( 'sslverify' => false ) );
        try {
            $json = json_decode( $response['body'] );
        } catch ( Exception $ex ) {
            return null;
        }
        return $json->message;
    }

    public static function remote_user_register( $sanitized_user_login, $user_email, $user_password = '' ) {
        $request = array(   'cmd' => 'user_create',
            'data' => array (
                'user_name' => $sanitized_user_login,
                'user_email' => $user_email,
	            'user_password' => urlencode($user_password)
            )
        );

        $json = rawurlencode( json_encode( $request ) );

        $response = wp_remote_get( RW_Remote_Auth_Client_Options::get_loginserver_endpoint() . $json , array ( 'sslverify' => false ) );
        try {
            $json = json_decode( $response['body'] );
        } catch ( Exception $ex ) {
            return null;
        }
        return $json->message;
    }

}
