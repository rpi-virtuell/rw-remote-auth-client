<?php

/**
 * Class: RW_Remote_Auth_Client_BPGroup
 *
 * adds a adminpage under the users page with
 *     Headline/Menu: Add Group Member
 *     1. URL Field (enter url to buddypress Group)
 *     2. List of successfull added group member
 *     3. Name of the group
 *     4. Button Add/Update
 *
 * requires the plugin rw-blog-group-server installed on the buddypress host
 *
 * send a remote json request to the buddypress host
 *     1. url of the buddypress group
 *     2. user_login of the blog admin
 *
 * exprect a json width a
 *    1. list of the member
 *    2. group details (slug,name,id,host)
 *    3. or error msg
 *
 * on success
 *    1. adds member of a external buddypress group to a blog
 *    2. adds the group (slug,name,id,host) to current blog options
 *
 *
 * displays buddypress group on the dasboard for all member
 * displays the add group member site
 *
 * Author: Joachim Happel
 * Date: 08.03.2016
 * Time: 01:09
 * @since 0.2.3
 */
class RW_Remote_Auth_Client_BPGroup
{

    /**
     * asking for a group
     *
     * @param $url
     * @param $admin
     *
     * save the to the blog options ( rw_remote_auth_client_bpgroups )
     *
     * @return a stdClass with the group_details ($host_url, group_id, group_name, guid)
     */
    public static function get_group_info( $url, $admin ){

    }

    /**
     * asking for the group member     *
     *
     * @param $host_url
     * @param $group_id
     * @param $admin
     *
     * @returns a array of group member (user_login)
     */
    public static function get_group_member ( $host_url, $group_id , $admin ){

    }

    /**
     * @param $args
     */
    protected static function  remote_get( $url, $args ){

    }

    /**
     * Add a new adminsubpage and submenu below User
     */
    public static function add_adminpage(){

    }

    /**
     * Displays the form on the adminpage to create the remote request
     */
    protected static function the_form(){

    }

    /**
     * handles the form input
     */
    public static function form_action(){

    }

    /**
     * Add an existing user from login server     *
     * @param $user_login
     * @use RW_Remote_Auth_Client_User::add_existing_user_from_auth_server
     */
    protected static function add_member_to_blog($user_login){

    }

    /**
     * Displays a loop of all member
     */
    public static function groups_loop(){

    }

    /**
     * displays a single goup with it's member
     * @param $group_id
     */
    protected static function the_group( $group_id ){



    }

    /**
     * displays a list of group members
     * @param $group_id
     *
     */
    protected static function get_the_members( $group_id ){

    }



}