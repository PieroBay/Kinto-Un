<?php 
	class publicController extends Controller{

		protected $table = array();

		public function indexAction(){
			$this->render(array(
				"message"	=>	'Hello World! c\'est la partie public',
			));	
		}
	}
?>