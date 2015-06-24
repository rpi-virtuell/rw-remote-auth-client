<?php


class RW_Remote_Auth_Client_Helper {

	static public function manipulate_other_plugins() {

		// Nur wenn Option gewählt.
		// @todo Option schaltbar machen
		// @todo Password Feld
		add_filter ( 'show_password_fields', array( 'RW_Remote_Auth_Client_Helper', 'show_password_fields' ),9999 );
	}


	static public function show_password_fields() {
		return true;
	}

}