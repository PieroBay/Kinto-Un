<?php 
	class publicController extends Controller{

		var $table = array('user');

		function indexAction(){
			$this->render(array(
				"message"	=>	'coucou c\'est la partie public',
			));	
		}
	}
?>