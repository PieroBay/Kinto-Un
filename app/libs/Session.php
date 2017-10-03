<?php

	namespace KintoUn\libs;

/**
 * Create flash message
 */
class Session{

	private $flash;

	/**
	 * Init the flash message
	 *
	 * @param string $type
	 * @param string $message
	 * @return void
	 */
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

	/**
	 * Return the flash message
	 *
	 * @return void
	 */
	public function flash(){
		if(isset($_SESSION['flash'])){
			$e = $_SESSION['flash'];
			unset($_SESSION['flash']);
			return('<div class="flash flash-'.$e['type'].'">'.$e['message'].'</div>');
		}
	}
}
