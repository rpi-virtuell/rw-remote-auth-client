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

    public static function remote_user_register( $sanitized_user_login, $user_email ) {
        $request = array(   'cmd' => 'user_create',
            'data' => array (
                'user_name' => $sanitized_user_login,
                'user_email' => $user_email
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

}
