<?php 
	class adminController extends Controller{

		var $table = array();

		function indexAction(){
/*			if(!$this->ROLE('admin')){
				$this->redirectUrl('public:index.html.twig'); 
			}*/

			$this->render(array(
				"message"	=>	'Hello World! C\'est l\'admin',
			));	
		}
		
	}

?>