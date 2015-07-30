<?php

/**
 * Class RW_Remote_Auth_Client_Installation
 *
 * Contains some helper code for plugin installation
 *
 */

class RW_Remote_Auth_Client_Installation {
    /**
     * Check some thinks on plugin activation
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    public static function on_activate() {

        // check WordPress version
        if ( ! version_compare( $GLOBALS[ 'wp_version' ], '3.0', '>=' ) ) {
            deactivate_plugins( RW_Remote_Auth_Client::$plugin_filename );
            die(
            wp_sprintf(
                '<strong>%s:</strong> ' .
                __( 'This plugin requires WordPress 3.0 or newer to work', RW_Remote_Auth_Client::get_textdomain() )
                , RW_Remote_Auth_Client::get_plugin_data( 'Name' )
            )
            );
        }


        // check php version
        if ( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
            deactivate_plugins( RW_Remote_Auth_Client::$plugin_filename );
            die(
            wp_sprintf(
                '<strong>%1s:</strong> ' .
                __( 'This plugin requires PHP 5.2 or newer to work. Your current PHP version is %1s, please update.', RW_Remote_Auth_Client::get_textdomain() )
                , RW_Remote_Auth_Client::get_plugin_data( 'Name' ), PHP_VERSION
            )
            );
        }
		// schedule check
	    wp_schedule_event( time(), 'twicedaily' , 'rw_auth_remote_check_server' );
        // Flush Rewrite Rules after activation
        flush_rewrite_rules();

    }

    /**
     * Clean up after deactivation
     *
     * Clean up after deactivation the plugin
     * Refresh rewriterules
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    public static function on_deactivation() {
	    // unschedule check
	    $timestamp = wp_next_scheduled( 'rw_auth_remote_check_server' );
	    wp_unschedule_event( $timestamp, 'rw_auth_remote_check_server' );

        flush_rewrite_rules();
    }

    /**
     * Clean up after uninstall
     *
     * Clean up after uninstall the plugin.
     * Delete options and other stuff.
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     *
     */
    public static function on_uninstall() {

    }

	/**
	 * Check the remote rw_auth server
	 *
	 * @since 0.1.10
	 * @return null
	 */
	public static function check_server() {
		$error = false;
		$request = array(   'cmd' => 'ping' );
		$json = urlencode( json_encode( $request ) );
		$response = wp_remote_get( RW_Remote_Auth_Client_Options::get_loginserver_endpoint() . $json, array ( 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {
			$error = true;
		} else {
			try {
				$json = json_decode( $response['body'] );
			} catch ( Exception $ex ) {
				$error = true;
			}
		}
		if ( $error ) {
			$admin_email = get_option( 'admin_email' );
			$mail_subject = __( 'rw_auth not responding',  RW_Remote_Auth_Client::$textdomain );
			$mail_text = sprintg ( __( 'The rw_auth on %1s ist not responding.', RW_Remote_Auth_Client::$textdomain ),  RW_Remote_Auth_Client_Options::get_loginserver_endpoint() );
			wp_mail( $admin_email, $mail_subject, $mail_text );
		}
	}
}