<?php 
	class publicController extends Controller{

		var $table = array();

		function indexAction(){
			$this->render(array(
				"message"	=>	'Hello World! C\'est la partie public',
			));	
		}
	}
?>