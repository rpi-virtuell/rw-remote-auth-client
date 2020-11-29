<?php

/**
 * Class RW_Remote_Auth_Clients_Options
 *
 * Contains some helper code for plugin options
 *
 */

class RW_Remote_Auth_Client_Options {


    /**
     * Register all settings
     *
     * Register all the settings, the plugin uses.
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function register_settings() {

        register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_client_options_server_endpoint_url' );
        register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_client_register_redirect_url' );
	    register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_client_bypass_admin' );
        //Do not set manually!
	    #register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_client_api_key' );

    }
    /**
     * save all network settings
     *
     * Register all the settings, the plugin uses.
     *
     * @since   0.2.0
     * @access  public
     * @static
     * @return  void
     * @useaction  admin_post_rw_remote_auth_client_network_settings
     */
    static public function network_settings() {

        check_admin_referer('rw_remote_auth_client_network_settings');
        if(!current_user_can('manage_network_options')) wp_die('FU');

        $options = array(
            'rw_remote_auth_client_options_server_endpoint_url',
            'rw_remote_auth_client_register_redirect_url',
            'rw_remote_auth_client_bypass_admin',
            //Do not set manually!
            //'rw_remote_auth_client_api_key',
        );

        foreach($options as $option){
            if( isset( $_POST[ $option ] ) ) {
                update_site_option( $option, ( $_POST[$option ] ) );
            }else{
                delete_site_option( $option );
            }
        }

        wp_redirect(admin_url('network/settings.php?page='.RW_Remote_Auth_Client::$plugin_base_name));
        exit;

    }

    /**
     * Add a settings link to the  pluginlist
     *
     * @since   0.1
     * @access  public
     * @static
     * @param   string array links under the pluginlist
     * @return  array
     */
    static public function plugin_settings_link( $links ) {
        if(is_multisite()){
            $settings_link = '<a href="network/settings.php?page=' . RW_Remote_Auth_Client::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
            if(is_super_admin()){
                array_unshift($links, $settings_link);
            }
        }else{
            $settings_link = '<a href="options-general.php?page=' . RW_Remote_Auth_Client::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * Get the API Endpoint
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  string
     */
    static public function get_loginserver_endpoint() {
        if ( defined ( 'RW_REMOTE_AUTH_SERVER_API_ENDPOINT' ) ) {
            return RW_REMOTE_AUTH_SERVER_API_ENDPOINT;
        } else {
            return get_site_option( 'rw_remote_auth_client_options_server_endpoint_url' );
        }
    }

    /**
     * Generate the options menu page
     *
     * Generate the options page under the options menu
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function options_menu() {
        if(is_multisite()){

            add_submenu_page(
                'settings.php',
                'Remote Auth Client',
                __('Remote Auth Client', RW_Remote_Auth_Client::$textdomain ),
                'manage_network_options',
                RW_Remote_Auth_Client::$plugin_base_name,
                array( 'RW_Remote_Auth_Client_Options','create_options')
            );

        }else{

            add_options_page(
                'Remote Auth Client',
                __('Remote Auth Client', RW_Remote_Auth_Client::$textdomain ),
                'manage_options',
                RW_Remote_Auth_Client::$plugin_base_name,
                array( 'RW_Remote_Auth_Client_Options', 'create_options' )
            );

        }


    }

    /**
	 * returns the login_server_endpoint_url
     * @since   0.1
     * @access  public
     *
	 * @return mixed|string
	 */

	static function get_server_endpoint_url(){
		$server_endpoint_url = get_site_option( 'rw_remote_auth_client_options_server_endpoint_url' );
		$server_endpoint_disabled = '';
		if ( defined( 'RW_REMOTE_AUTH_SERVER_API_ENDPOINT' ) ) {
			// Endpoint is set in wp_config
			$server_endpoint_url = RW_REMOTE_AUTH_SERVER_API_ENDPOINT;
		}
		return $server_endpoint_url;
	}

	/**
     * returns the Host of the login_server_endpoint_url
	 * @since   0.1
	 * @access  public
	 *
	 * @return mixed|string
	 */

	static function get_login_server(){
		$server_endpoint_url  = self::get_server_endpoint_url();
		if($server_endpoint_url){

		    $uri = parse_url($server_endpoint_url);


		    return $uri['host'];

        }else{
		    return 'konto.rpi-virtuell.de';
        }

	}


    /**
     * Generate the options page for the plugin
     *
     * @since   0.1
     * @access  public
     * @static
     *
     * @return  void
     */
    static public function create_options() {

        $servercheck = RW_Remote_Auth_Client_User::remote_say_hello();

        if(is_multisite()){
            $form_action = admin_url('admin-post.php?action=rw_remote_auth_client_network_settings');
        }else{
            $form_action = 'options.php';
        }

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

	    $server_endpoint_url = self::get_server_endpoint_url();
	    if ( defined( 'RW_REMOTE_AUTH_SERVER_API_ENDPOINT' ) ) {
		    $server_endpoint_disabled = 'disabled';
        }

        ?>
        <div class="wrap"  id="rwremoteauthserveroptions">
            <h2><?php _e( 'Remote Auth Client Options', RW_Remote_Auth_Client::$textdomain ); ?></h2>
            <p><?php _e( 'Settings for Remote Auth Server', RW_Remote_Auth_Client::$textdomain ); ?></p>
            <form method="POST" action="<?php echo $form_action; ?>"><fieldset class="widefat">

                    <div class="notice notice-<?php echo $servercheck->notice ;?>">
                        <p><strong><?php echo $servercheck->answer;?></strong></p>
                    </div>
                    <?php


                    if(is_multisite()){
                        wp_nonce_field('rw_remote_auth_client_network_settings');
                    }else{
                        settings_fields( 'rw_remote_auth_client_options' );
                    }
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rw_remote_auth_client_options_server_endpoint_url"><?php _e( 'API Server Endpoint URL', RW_Remote_Auth_Client::$textdomain ); ?></label>
                            </th>
                            <td>
                                <input id="rw_remote_auth_client_options_server_endpoint_url" class="regular-text" type="text" value="<?php echo $server_endpoint_url; ?>" aria-describedby="endpoint_url-description" name="rw_remote_auth_client_options_server_endpoint_url" <?php echo $server_endpoint_disabled; ?>>
                                <p id="endpoint_url-description" class="description"><?php _e( 'Endpoint URL for API request.', RW_Remote_Auth_Client::$textdomain); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="rw_remote_auth_client_api_key"><?php _e( 'API Key', RW_Remote_Auth_Client::$textdomain ); ?></label>
                            </th>
                            <td>
                                <input id="rw_remote_auth_client_api_key" class="regular-text" type="text" value="<?php echo get_site_option( 'rw_remote_auth_client_api_key' ); ?>" aria-describedby="rw_remote_auth_client_api_key" disabled>
                                <p id="api_key-description" class="description"><?php _e( 'Entered by auth service', RW_Remote_Auth_Client::$textdomain); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="rw_remote_auth_client_register_redirect_url"><?php _e( 'Register hint URL', RW_Remote_Auth_Client::$textdomain ); ?></label>
                            </th>
                            <td>
                                <input id="rw_remote_auth_client_register_redirect_url" class="regular-text" type="text" value="<?php echo get_site_option( 'rw_remote_auth_client_register_redirect_url' ); ?>" aria-describedby="rw_remote_auth_client_register_redirect_url" name="rw_remote_auth_client_register_redirect_url" >
                                <p id="endpoint_url-description" class="description"><?php _e( 'URL for register hint page', RW_Remote_Auth_Client::$textdomain); ?></p>
                            </td>
                        </tr>

	                    <tr>
		                    <th scope="row">
			                    <label for="rw_remote_auth_client_bypass_admin"><?php _e( 'Don\'t overwrite admin password', RW_Remote_Auth_Client::$textdomain ); ?></label>
		                    </th>
		                    <td>
			                    <input type="checkbox" name="rw_remote_auth_client_bypass_admin" value="1" <?php if ( get_site_option( 'rw_remote_auth_client_bypass_admin' ) ) echo " checked "; ?> />
			                    <p id="bypass_admin-description" class="description"><?php _e( 'Check this box to bypass password overwrite from administrators ', RW_Remote_Auth_Client::$textdomain); ?></p>
		                    </td>
	                    </tr>
                    </table>

                    <br/>
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes' )?>" />
            </form>
        </div>
    <?php
    }


}
