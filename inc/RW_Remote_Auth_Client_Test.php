<?php
/**
 * Created by PhpStorm.
 * User: Joachim
 * Date: 29.02.2016
 * Time: 18:31
 */

Class RW_Remote_Auth_Client_Test{
    public static function init(){

        return;

        $login = 'testbetrieb'.rand(5);
        $user = get_user_by('login',$login);

        echo 'remote_say_hello(  )<br>';
        var_dump(RW_Remote_Auth_Client_User::remote_say_hello(  ));
        echo '<hr>';

        echo 'remote_user_register($login )<br> set user for test';
        var_dump(RW_Remote_Auth_Client_User::remote_user_register( $login, $login.'@mailinator.com' ));
        echo '<hr>';

        echo 'remote_user_exists( $login )<br>';
        var_dump(RW_Remote_Auth_Client_User::remote_user_exists( $login ));
        echo '<hr>';

        echo 'remote_user_get_password( $login)<br>';
        var_dump(RW_Remote_Auth_Client_User::remote_user_get_password( $login));
        echo '<hr>';
        echo 'remote_user_get_data( $login)<br>';
        var_dump(RW_Remote_Auth_Client_User::remote_user_get_data( $login ));
        echo '<hr>';
        echo 'remote_change_password( $login,$user->user_pass , wp_hash_password(rand(5, 15)) )<br>';
        var_dump(RW_Remote_Auth_Client_User::remote_change_password( $login, $user->user_pass , wp_hash_password(rand(5, 15)) ));
        echo '<hr>';
        echo 'set_password_from_loginserver( $login , $user )<br>';
        var_dump(RW_Remote_Auth_Client_User::set_password_from_loginserver( $login , $user ));
        echo '<hr>';
        echo 'reset_password_on_login_server (  $user,$login )<br> set user for test';
        var_dump(RW_Remote_Auth_Client_User::reset_password_on_login_server(  $user, $login ));
        echo '<hr>';
        die();
    }
}


