/**
 * @package   RW Remote Auth Client
 * @author    Joachim Happel
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/rw-remote-auth-client
 */

jQuery(document).ready(function($){
    /**
     * checken ob der vom loginserver übergebende user "reliwerk_cas_user_account" auf dieser Instanz existiert
     * ggf. Anmelden
     */



    $.ajax({
        type: 'POST',
        url: rw_rac_ajax.ajaxurl,     // the variable ajaxurl is prepared by wp core
        data: {
            action: 'rw_remote_auth_client_cas_user_status',
            user: reliwerk_cas_user_account
        },
        success: function (data, textStatus, XMLHttpRequest) {

            console.log(data);

            readData = $.parseJSON(data);
            console.log($.parseJSON(data));

            switch (readData.status ){
                case 'not-logged-in-user':

                    if($('#rpi-user-name').length){

                        $('#rpi-user-name').html(  readData.name  );
                        $('#rpi-user-avatar').html(  readData.avatar  );
                        $('#rpi-user-status').html(  'Du bist als ' + reliwerk_cas_user_account + ' am Loginserver angemeldet!'  );

                    }
                    break;
                case 'logged-in':
                    if($('#rpi-user-name').length){

                        $('#rpi-user-name').html(  readData.name  );
                        $('#rpi-user-avatar').html(  readData.avatar  );

                    }
                    break;
                case 'do-loggin':
                    $('body').html('');
                    $('body').hide();
                    $('html').css('height','100%');
                    $('body').css('height','100%');
                    $('body').css('background-color','#1B638A');
                    $('body').css('color','white');
                    $('body').html('<table height="100%" width="100%"><tr><td align="center" valign="middle">Die Anmeldung war erfolgreich ...</td></tr></table>');
                    $('body').fadeIn();
                    document.location.href='/wp-login.php';
                    break;
                default: //unknown

            }




        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        }
    });



});

