<?php

class RW_Remote_Auth_Client_User {


    /**
     * @use wp_remote_get
     * @param $json
     * @return stdClass (response data) || WP_Error
     */

	static function remote_get( $json ){

        $error = false;

        if(!is_string($json)){
            try{
                $json = rawurlencode( json_encode( $json) );
            }catch(Exception $e){
                $error = __('Error: Server can not encode server response.', RW_Remote_Auth_Client::get_textdomain());
            }
        }

        //validat the answer
        $response = wp_remote_get(RW_Remote_Auth_Client_Options::get_loginserver_endpoint() . $json );
        if ( !is_wp_error( $response ) ) {
            if(
                isset($response['headers']["content-type"]) && strpos($response['headers']["content-type"],'application/json') !==false )
            {
                try {
                    $json = json_decode($response['body']);
                    if (is_a($json, 'stdClass') && isset($json->errors) && $json->errors ) {
                        $sever_error = $json->errors;
                        if(is_a($sever_error,'stdClass')){
                            $error = $sever_error->message;
                            $data = $sever_error->data;
                            if($data->rw_remote_auth_api_key){
                                // remote auth service suspends client and sends a new api-key
                                // save the new api-key in the options
                                update_site_option('rw_remote_auth_client_api_key',$data->rw_remote_auth_api_key);
                            }
                        }else{
                            $error  = $sever_error;
                        }

                    }else{
                        return $json;
                    }

                } catch ( Exception $ex ) {
                    $error = __('Error: Can not decode response.', RW_Remote_Auth_Client::get_textdomain());
                }
            }else{
                $error =  __('Error. Wrong Content Type. Check the API Server Endpoint', RW_Remote_Auth_Client::get_textdomain()) ;
            }

        }else{
            $error =  __('Error. Check the API Server Endpoint URL in the Settingspage', RW_Remote_Auth_Client::get_textdomain()) ;
        }
        $data = isset($data)?$data:$response;
        return new WP_Error('remote_auth_response',$error, $response);
	}

    /**
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * sets special user_agent infos for connecting with remote auth server
     * deals with wp_remote_get
     *
     * @user_agend_args: (seperated by ; )
     *
     *         Clientinfos  ( Class Version )
     *         api_key      ( option )
     *         domain       ( setted domain of multisite or songlesite )
     *         IP           ( of the hosting server )
     *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * @param $args
     * @param $url
     * @return mixed
     *
     * @usefilter http_request_args
     */
	static function set_http_request_args($args, $url){

		if( strpos( urldecode($url),RW_Remote_Auth_Client_Options::get_loginserver_endpoint() ) !== false ){

			$domain = parse_url(network_site_url( ), PHP_URL_HOST);
            $key = get_site_option('rw_remote_auth_client_api_key');

			//modify user-agent with @user_agend_args
            $args['user-agent']=  'RW_Remote_Auth_Client '.RW_Remote_Auth_Client::$version	.';' . $domain .';'. $_SERVER['SERVER_ADDR'] .';'. $key;

			$args['sslverify']=  false;

		}

		return $args;
	}

	/**
	 *  Test the server connection
     *
	 * @return stdClass( $notice, $answer )
     *
	 */
	public static function remote_say_hello(  ) {

        $request = array(   'cmd' => 'say_hello',
			'data' => array (
				'question' => 'Can you here me'
			)
		);
        $response = self::remote_get( $request );
        if(is_wp_error($response) || !isset($response->data) || $response->data === false ){

            $data = new stdClass();
            $data->notice = 'error';

            if(!isset($response->data) && ! is_wp_error($response) ){     //unknown error

                $data->notice = 'error';
                $data->answer = 'Serveresponse: '.json_encode($response);

            }elseif(isset($response->data) && $response->data === false) { //whitelisting is not active

                $data->answer = 'it works';
                $data->notice = 'info';

            }else{
                $data->answer = $response->get_error_message();
                $data->data =   $response->get_error_data();
            }


            return $data;

        }
        return  $response->data;
	}

	/**
	 * @param $errors
	 * @param $sanitized_user_login
	 * @param $user_email
	 *
	 * @return mixed
	 */
    public static function check_remote_user_on_register( $errors, $sanitized_user_login, $user_email ) {
        if ( self::remote_user_exists( $sanitized_user_login ) && ! username_exists( $sanitized_user_login ) ) {
            wp_create_user( $sanitized_user_login, '', $user_email );
        } else {
            self::remote_user_register( $sanitized_user_login, $user_email );
            wp_redirect( get_site_option( 'rw_remote_auth_client_register_redirect_url' ) );
            exit;
        }
        return $errors;
    }


	/**
	 * @param $result
	 *
	 * @return mixed
	 */
    public static function check_remote_user_on_multisite_register ( $result ) {
	    if ( self::remote_user_exists( $result['user_name'] ) && ! username_exists( $result['user_name'] ) ) {
            wp_create_user( $result['user_name'], '', $result['user_email'] );
        } else {
		    self::remote_user_register( $result['user_name'], $result['user_email'] );
            wp_redirect( get_site_option( 'rw_remote_auth_client_register_redirect_url' ) );
		    //var_dump( get_site_option( 'rw_remote_auth_client_register_redirect_url' ) );
		    //echo "Hier kann ich leider keinen Redirect machen, weil schon Content ausgegeben wurde.";
		    //echo "Wenn ich hier aber nicht die Ausführung abbreche, dann wird der User auf diesem Server auch eingetragen.";
            exit;
        }
        return $result;
    }

	/**
	 * @param $user_id
     * return bool
	 */
	public static function create_mu_user_on_login_server ( $user_id ) {
		global $wpdb;
	    $user = get_user_by( 'id', $user_id );
		if ( is_object( $user)) {
			// UserObject has wrong, temporary password
			// Get correct password from signup table
			$signup = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->signups WHERE user_email = %s", $user->user_email) );
            if($signup){
                $meta = maybe_unserialize($signup->meta);
                if(is_array( $meta ) && isset( $meta['password']) ){
                    $password = $meta['password'];
                }else{
                    $password =$user->user_pass;
                }
            }else{
                // system generated password on new users via backend created
                $password =$user->user_pass;
            }
			return self::remote_user_register($user->user_login, $user->user_email, $password );
		}
        return false;
	}

	/**
	 * @param $user_id
	 */
	public static function create_user_on_login_server ( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		return self::remote_user_register($user->user_login, $user->user_email, $user->user_pass );
	}

	/**
	 * @param int $user_id
	 * @param WP_User $old_user
	 */
	public static function change_password_on_login_server ( $user_id, $old_user ) {
		$new_user = get_user_by( 'id', $user_id );
		if ( $new_user->user_pass != $old_user->user_pass ) {
			// password changed
            return self::remote_change_password( $new_user->user_login, $old_user->user_pass, $new_user->user_pass );
		}
        return false;
	}

    /**
     * @param WP_User $user
     * @param string $password
     */
    public static function reset_password_on_login_server ( $user, $password ) {

        $oldpassword = '';
        $newpassword = wp_hash_password($password);
        $oldpassword =  self::remote_user_get_password( $user->user_login ) ;


        // password changed
        if(!self::remote_change_password( $user->user_login, $oldpassword, $newpassword )){

            return false;
        }

        return true;

    }

	/**
	 * @param $username
	 *
	 * @return null
	 */
    public static function remote_user_exists( $username ) {
        $request = array(   'cmd' => 'user_exists',
                            'data' => array (
                                'user_name' => $username
                            )
                        );

        $json = urlencode( json_encode( $request ) );
        $response = self::remote_get( $json );
	    if ( !is_wp_error( $response ) ) {
	       return $response->message;
	    }
	    return false;
    }

	/**
	 * @param $sanitized_user_login
	 * @param $user_email
	 * @param string $user_password
	 *
	 * @return bool
	 */
    public static function remote_user_register( $sanitized_user_login, $user_email, $user_password = '' ) {
	    if ( ! RW_Remote_Auth_Client_User::remote_user_exists( $sanitized_user_login ) ) {
		    $request = array(
			    'cmd'  => 'user_create',
			    'data' => array(
				    'user_name'     => $sanitized_user_login,
				    'user_email'    => $user_email,
				    'user_password' => urlencode( $user_password )
			    )
		    );

		    $json = rawurlencode( json_encode( $request ) );

		    $response = self::remote_get( $json  );
            if ( !is_wp_error( $response ) ) {

                return $response->message;
		    }
	    }

	    return false;
    }

	/**
	 *
	 * @todo  passwort änderung absichern, das es nur vom dem user kommt,stichwort cas service auth
	 *
	 * @param $user_login
	 * @param $user_old_password
	 * @param $user_new_password
	 *
	 * @return bool
	 */
	public static function remote_change_password( $user_login, $user_old_password, $user_new_password ) {
		$request = array(   'cmd' => 'user_change_password',
		                    'data' => array (
			                    'user_name' => $user_login,
			                    'user_old_password' => urlencode( $user_old_password ),
			                    'user_new_password' => urlencode( $user_new_password )
		                    )
		);
		$json = rawurlencode( json_encode( $request ) );
		$response = self::remote_get( $json );
        if ( !is_wp_error( $response ) ) {
            return $response->message;
        }

		return false;
	}

	/**
	 * @param $user_login
	 * @param $user
     * @return bool
	 */
	public static function set_password_from_loginserver( $user_login, $user ) {
		global $wpdb;
		if ( get_site_option( 'rw_remote_auth_client_bypass_admin' ) != 1 || ( get_site_option( 'rw_remote_auth_client_bypass_admin' ) == 1 &&   !current_user_can( 'Administrator' ) && !current_user_can( 'Super Admin' ) ) ) {
			$data = self::remote_user_get_password( $user->user_login , true);
            if ( $data->password == '' OR $data->email == '' ) {
                return false;
            }
            $pw_setted = $wpdb->update(
				$wpdb->users,
				array(
					'user_pass' => urldecode( $data->password ),
				),
				array(
					'ID' => $user->ID
				)
			);
			if ( $user->user_email == '' ) {
				$wpdb->update(
					$wpdb->users,
					array(
						'user_email' => urldecode( $data->email ),
					),
					array(
						'ID' => $user->ID
					)
				);
			}
            if($pw_setted){
                return true;
            }
		}
        return false;
	}


	/**
	 * @param $username
	 * @param bool
     *
     * @return string|stdClass|bool
	 */
	public static function remote_user_get_password( $username,$send_userdata = false ) {
		$request = array(   'cmd' => 'user_get_password',
		                    'data' => array (
			                    'user_name' => $username
		                    )
		);
		$json = urlencode( json_encode( $request ) );
		$response = self::remote_get( $json );
        if ( !is_wp_error( $response ) ) {
            if($send_userdata){
                return json_decode($response->data);
            }else{
                return $response->message;
            }

        }

		return false;
	}


    /**
     * @param $username
     * @param bool
     *
     * @return stdClass|bool
     */
    public static function remote_user_get_data( $username ) {
		$request = array(   'cmd' => 'user_get_details',
			'data' => array (
				'user_name' => $username
			)
		);

		$json = urlencode( json_encode( $request ) );
		$response = self::remote_get( $json );
		if ( !is_wp_error( $response ) ) {
		    return $response->data;
		}else{
           // $data = newClass();
           // $data->error = $response->get_error_message();
        }
		return false;
	}


    /**
     * hook into add new user form to ad a new post field for unigue handling with the wp_redirect filter
     *
     * @useaction user_new_form
     *
     */
    public static function user_new_form_check_remote_auth_server($form_type){
        if($form_type == 'add-existing-user'){
            echo '<input hidden name="rw_remote_auth_server_user_exists" value="add-existing-user">';


        }
    }

    /**
     * Check by username wether user exists on client or remote auth server
     * add user to the client (multisite) if exists on remote auth server
     * invite existing or added user as member to the current blog
     *
     * returns an url to use with wp_redirect, if user exits or added
     * returns false, if user not exits anyway
     *
     * @param string $login (user_login)
     * @param string $return_url (that came from wp_redirect filter)
     * @return bool|string (redirection url or false, if user does not exits on remote server)
     * @filter rw_remote_auth_client_add_existing_user
     *
     * @see invite_to_blog
     */
    protected static function add_existing_user_from_auth_server($login,$return_url = false){
        global $wpdb;

        // check user exists on client
        if(false !== strpos($login, '@')){
            $user = get_user_by('email',$login);
        }else{
            $user = get_user_by('login',$login);
        }

        if($user){ //user exists on client

            if( array_key_exists( get_current_blog_id(), get_blogs_of_user($user->ID ) )  ) {

                //if user exists on the current blog notice admin
                $return_url = add_query_arg(array('update' => 'addexisting'), 'user-new.php') ;
            }else {
                //in multisite client user exists but is not member of the currentblog
                //send a confirm massage to the user
                self::invite_to_blog($user);

                $return_url = admin_url(add_query_arg(array('update' => 'add'), 'user-new.php'));
            }
        }else{
            // user does not exits on client
            // check user exists on remote on server
            //correct server response returns an object
            $data = self::remote_user_get_data($login);

            if (isset($data->error)) {   //object contains an error message

                $return_url ='user-new.php?rw_remote_auth_client_error=' . urlencode($data->error);

            }elseif(isset($data->exists) && $data->exists  ){ //object returns an exists flag

                //create a valid set of values als args for wp_insert_user
                $user_details = array(
                    'user_login' => $data->user_login,
                    'user_nicename' => $data->user_login,
                    'nickname' => $data->user_login,
                    'display_name' => $data->user_login,
                    'user_pass' => $data->user_password,
                    'user_email' => $data->user_email,
                    'user_registered' => date('Y-m-d H:i:s')

                );

                //insert client user with the response data from auth server
                if (!$user_id = wp_insert_user($user_details)) {

                    $return_url= 'user-new.php?rw_remote_auth_client_error=' .
                        urlencode(__('User was not added to your site', RW_Remote_Auth_Client::get_textdomain()));

                } else {

                    //user is automaticly added to the current blog, but we will ask him to confirm, so remove him noe
                    if(is_multisite()){

                        remove_user_from_blog($user_id, get_current_blog_id());
                        //send a confirm massag to the user
                        self::invite_to_blog(get_userdata($user_id));
                        $return_url = add_query_arg( array('update' => 'add'), 'user-new.php' );
                    }else{
                        if ( current_user_can( 'list_users' ) )
                            $return_url = 'users.php?update=add&id=' . $user_id;
                        else
                            $return_url = add_query_arg( array('update' => 'add'), 'user-new.php' );
                    }
                }

            }else{
                // user does not exist on auth server too
                // we need a different handling at return:
                if(!$return_url){   //question comes from Create new User
                    $return_url = false;
                }else{              //question comes from Add exiting User
                    $return_url = add_query_arg( array('update' => 'does_not_exist'), 'user-new.php' );
                }
            }
        }
        //filter contains url for redirecting or false if user was not found on remote auth server
        return apply_filters('rw_remote_auth_client_add_existing_user', $return_url );

    }

    /**
     * Admin Userpage: Create new User / Update User (singelsite)
     *
     * hooks into theuser_profile_update_errors action
     * an try to add or update an singel seite user
     *
     * @useaction user_profile_update_errors
     *
     * @see add_existing_user_from_auth_server
     * @see reset_password_on_login_server
     */
    public static function add_update_singlesite_user($errors, $update, $user ){

        if ( $errors->get_error_codes() ) return;

        if($update) { // may be a new user


            self::reset_password_on_login_server ( $user, $user->user_pass );


        }else{ // may be a new user



            if(!$redirect = self::add_existing_user_from_auth_server($user->user_email)){
                if(!$redirect = self::add_existing_user_from_auth_server($user->user_login)){
                    //no existing user found
                    return;
                }else{
                    wp_redirect($redirect) ;
                }
            }else{
                wp_redirect($redirect) ;
            }
        }
    }

    /**
     * Adminpage: Create new User / Neuen Benutzer hinzufügen (multisite)
     *
     * hooks into the wpmu_validate_user_signup filter
     * an try to add en existing user from remote server
     * if successfully added,
     *      exit the wpmu_validate_user_signup and redirect to user_new.php with message
     * else
     *      return and go and invite create a new user, if valid input
     *
	 * @useaction wpmu_validate_user_signup
     *
     * @see add_existing_user_from_auth_server
	 */
	public static function create_new_user( $result  ){

        if($result['errors'] && count( $result['errors']->errors ) > 0){
            //solve input errors first
            return $result;
        }
        //check both: username and email
        if(!$redirect = self::add_existing_user_from_auth_server($result['user_email'])){
            if(!$redirect = self::add_existing_user_from_auth_server($result['user_name'])){
                return $result;
            }else{
                wp_redirect($redirect) ;
            }
        }else{
            wp_redirect($redirect) ;
        }

    }

    /**
     * Adminpage: Add existing User / Bestehenden Benutzer hinzufügen
     *
     * hooks into the wp_redirect filter by trying to add an existing user
     * thats a bit tricky, becouse ther is no other possible to hook in "add existing user" routine
     * but we have an unique field submitted:
     * @see user_new_form_check_remote_auth_server
     *
     * @param string $url (the redirection url, wich will be used be wp_redirect)
     * @param int $status
     * @return string ( redirection url )
     *
     * @see add_existing_user_from_auth_server
     *
     * @useaction wp_redirect
     */
	public static function add_existing_user( $url, $status  ){

		if  (
				isset($_REQUEST['action'] ) 		                    &&
				'adduser' == $_REQUEST['action'] 	                    &&
				isset( $_REQUEST['email'] )			                    &&
                isset( $_REQUEST['rw_remote_auth_server_user_exists'] )
			) {

            $existing_user = sanitize_user($_REQUEST['email']);

            if($_REQUEST['rw_remote_auth_server_user_exists'] == 'add-existing-user'){

                //try to add existing user from auth server
                $url = self::add_existing_user_from_auth_server($existing_user, $url);
            }
        }
        return $url;
	}

    /**
     * ask remote Server to provide userdata from existing usernames and create them in the current multisite
     *
     * @param stdClass  $user
     *
     */
    protected static function invite_to_blog($user){

        $newuser_key = substr( md5( $user->ID ), 0, 5 );
        add_option( 'new_user_' . $newuser_key, array( 'user_id' => $user->ID, 'email' => $user->user_email, 'role' => $_REQUEST[ 'role' ] ) );

        $roles = get_editable_roles();
        $role = $roles[ $_REQUEST['role'] ];

        $role_name =  wp_specialchars_decode( translate_user_role( $role['name'] ) );
        $blogname = get_option( 'blogname' );
        $subject = sprintf( __( '[%s] Joining confirmation' ), $blogname );
        $achtivation_link = home_url( "/newbloguser/$newuser_key/" ) ;

        self::mail_added_user($user->user_email, $subject, array($blogname,home_url(),$role_name,$achtivation_link));
    }

    /**
     * copied from user-new.php Line 78 ff
     *
     * @param $new_user_email
     * @param $subject
     * @param $args
     */
    protected static function mail_added_user($new_user_email,$subject, $args){

        $message = sprintf(__( 'Hi,

You\'ve been invited to join \'%1$s\' at
%2$s with the role of %3$s.

Please click the following link to confirm the invite:
%4$s' ), $args[0],$args[1],$args[2],$args[3]);

        wp_mail($new_user_email,
            $subject,
            $message
        );
    }

}
