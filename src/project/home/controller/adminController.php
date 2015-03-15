<?php 
	class adminController extends Controller{

		var $table = array();

		function indexAction(){
/*			if(!$this->ROLE('admin')){
				$this->redirectUrl('home_index'); 
			}*/

			$this->render(array(
				"message"	=>	'Hello World! C\'est l\'admin',
			));	
		}
		
	}

?>