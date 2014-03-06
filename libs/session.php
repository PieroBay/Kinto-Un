<?php
class Session{

/*	public function __construct(){
		session_start();
	}*/

	public function setFlash($type = 'error', $message){
		$_SESSION['flash'] = array(
			'type' => $type,
			'message' => $message,
		);
	}

	public function flash(){
		if(isset($_SESSION['flash'])){
			echo '<div class="flash flash-'.$_SESSION['flash']['type'].'">'.$_SESSION['flash']['message'].'</div>';
			unset($_SESSION['flash']);
		}
	}
}
