<?php 
	class publicController extends Controller{

		protected $table = array();

		public function indexAction(){
		    $this->render(array(
				"message"	=>	'Hello World! C\'est la partie public',
			));	
		}
	}
?>