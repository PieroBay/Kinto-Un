<?php 
	class publicController extends Controller{

		var $table = array();

		function indexAction(){
			$this->render(array(
				"message"	=>	'Hello Word! C\'est la partie public',
			));	
		}
	}
?>