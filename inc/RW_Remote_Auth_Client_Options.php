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

        //register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_client_options_server' );

        //register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_server_options_whitelist_active' );
        //register_setting( 'rw_remote_auth_client_options', 'rw_remote_auth_server_options_whitelist' );
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
        $settings_link = '<a href="options-general.php?page=' . RW_Remote_Auth_Client::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
        array_unshift($links, $settings_link);
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
            return get_option( 'rw_remote_auth_client_options_server_endpoint_url' );
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
        add_options_page( 'Remote Auth Client',  __('Remote Auth Client', RW_Remote_Auth_Client::$textdomain ), 'manage_options',
            RW_Remote_Auth_Client::$plugin_base_name, array( 'RW_Remote_Auth_Client_Options', 'create_options' ) );
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
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        $server_endpoint_url = get_option( 'rw_remote_auth_client_options_server_endpoint_url' );
        $server_endpoint_disabled = '';
        if ( defined( 'RW_REMOTE_AUTH_SERVER_API_ENDPOINT' ) ) {
            // Endpoint is set in wp_config
            $server_endpoint_url = RW_REMOTE_AUTH_SERVER_API_ENDPOINT;
            $server_endpoint_disabled = ' disabled ';
        }
        ?>
        <div class="wrap"  id="rwremoteauthserveroptions">
            <h2><?php _e( 'Remote Auth Client Options', RW_Remote_Auth_Client::$textdomain ); ?></h2>
            <p><?php _e( 'Settings for Remote Auth Server', RW_Remote_Auth_Client::$textdomain ); ?></p>
            <form method="POST" action="options.php"><fieldset class="widefat">
                    <?php
                    settings_fields( 'rw_remote_auth_client_options' );
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
                                <label for="rw_remote_auth_client_register_redirect_url"><?php _e( 'Register hint URL', RW_Remote_Auth_Client::$textdomain ); ?></label>
                            </th>
                            <td>
                                <input id="rw_remote_auth_client_register_redirect_url" class="regular-text" type="text" value="<?php echo get_option( 'rw_remote_auth_client_register_redirect_url' ); ?>" aria-describedby="rw_remote_auth_client_register_redirect_url" name="rw_remote_auth_client_register_redirect_url" >
                                <p id="endpoint_url-description" class="description"><?php _e( 'URL for register hint page', RW_Remote_Auth_Client::$textdomain); ?></p>
                            </td>
                        </tr>
	                    <tr>
		                    <th scope="row">
			                    <label for="rw_remote_auth_client_bypass_admin"><?php _e( 'Don\'t overwrite admin password', RW_Remote_Auth_Client::$textdomain ); ?></label>
		                    </th>
		                    <td>
			                    <input type="checkbox" name="rw_remote_auth_client_bypass_admin" value="1" <?php if ( get_option( 'rw_remote_auth_client_bypass_admin' ) ) echo " checked "; ?> />
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