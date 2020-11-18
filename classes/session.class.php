<?php

class Session {

	public function __construct() {
		
		if( session_id() == '' ) {
      session_start();
    }
	}
	
	public function __destruct() {
		
		session_write_close();
	}
	
	public function setSessionID() {
		
		if( session_id() == '' ) {
			session_id();
		}
	}
	
	public function sessionStarted() {
		
		if( session_id() == '' ) {
				return false;
		} else {
				return true;
		}
	}

	public function exists($key){
		if(isset($_SESSION[$key]) && $_SESSION[$key] != null){
			return true;
		}else{
			return false;
		}
	}
	
	public function add_session( $key, $value ) {
		
		$_SESSION[$key] = $value;
	}
	
	public function add_bulk_sessions( array $sessions ) {
		
		foreach( $sessions as $key => $value ) {
			$_SESSION[$key] = $value;
		}
	}

	public function get_session_by_key( $key ) {
		
		return $_SESSION[$key];
	}
	
	public function get_all_sessions() {
		
		return $_SESSION;
	}
	
	public function delete_session_by_key( $key ) {
		
		unset( $_SESSION[$key] );
	}
	
	public function kill_session() {
			
		$_SESSION = array();
					
		if( ini_get( 'session.use_cookies' ) ) {
			$params = session_get_cookie_params();
			setcookie( session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']
			);
		}
		
		session_destroy();
	}
	
	public function display() {
		echo '<pre>', print_r( $_SESSION, true );
	}
}