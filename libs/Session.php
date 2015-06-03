<?php
class Session{

	private $flash;

	public function setFlash($type=null, $message=null){
 	    try{
 	   		if(!isset($message) || !isset($type)) throw new Exception("Des paramètres sont manquants");
 	    }catch(Exception $e){
 	   		Error::renderError($e);
 	   		exit();
 	    }

		$_SESSION['flash'] = array(
			'type' => $type,
			'message' => $message,
		);
		$this->flash = $_SESSION['flash'];
	}

	public function flash(){
		if(isset($_SESSION['flash'])){
			echo '<div class="flash flash-'.$_SESSION['flash']['type'].'">'.$_SESSION['flash']['message'].'</div>';
			unset($_SESSION['flash']);
		}
	}
}
