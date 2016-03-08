<?php

/**
 * Class: RW_Remote_Auth_Client_BPGroup
 *
 * adds a adminpage under the users page with
 *     Headline/Menu: Add Group Member
 *     1. CODE field (base64 :    {group_id:'123',url:'http://....'} )
 *     2. List of successfull added group member
 *     3. Name of the group
 *     4. Button Add/Update
 *
 * requires the plugin rw-blog-group-server installed on the buddypress host
 *
 * send a remote json request to the buddypress host to fetch group data
 *     1. url of the buddypress group
 *     2. user_login of the blog admin
 *
 * send a remote json request to the buddypress host to fetch group data
 *     1. group_id     (group slug may be not persistent)
 *     2. user_login of the blog admin
 *
 * expect a json width a
 *    1. list of the member
 *    2. group details (name,url)
 *    or
 *      response error messages (http error code) can be
 *      - admin is not a member (403)
 *      - group not found or deleted (404)
 *      - invalid request (406)
 *      - no json (405)
 *

 * on success
 *    1. adds member of a external buddypress group to a blog
 *    2. adds the group (slug,name,id,host) to current blog options
 *    3. send a success:true request
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
     * @hash base64 returns json with:
     *      @param $url       (to external bp group)
     *      @param $group_id  (from external bp group)
     * @param $admin     (from current_user)
     *
     * save the to the blog options ( rw_remote_auth_client_bpgroups[ host_group_id : {group_id:'123',url:'http://....'} ]
     *
     * @return a stdClass with the group_details
     *
     *      group->info = stdClass
     *          info->name
     *          info->url
     *
     *      group->members = array{
     *
     *          stdClass Member:
     *              Member->name
     *              Member->url
     *
     *      )
     *
     */
    public static function get_group( $hash, $admin ){

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