<?php
class Session{

	private $flash;

	public function __construct(){
		$this->flash = $_SESSION['flash'];
	}

	public function setFlash($type = 'error', $message){
		$_SESSION['flash'] = array(
			'type' => $type,
			'message' => $message,
		);
	}

	public function flash(){
		if(isset($_SESSION['flash'])){
			unset($_SESSION['flash']);
			return '<div class="flash flash-'.$this->flash['type'].'">'.$this->flash['message'].'</div>';
		}
	}
}