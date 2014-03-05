<?php 
	class Controller{

		# Views -> folder same name that the controller Without Controller
		# in the folder, file same name that action
		# controllers -> file same name that controllers with Controler at end

		var $vars = array();
		protected $bdd;

		function __construct($bdd){
			$this->bdd=$bdd;
			if(isset($this->models)){
				foreach ($this->models as $v) {
					$this->loadModel($v);
				}
			}
		}

		function send($d){
			$this->vars = array_merge($this->vars,$d); 		
			$array = $this->vars;							
			$filename = explode("Action", debug_backtrace()[1]["function"])[0]; 	# Get action name without "Action"
			$contro = explode("Controller", get_class($this))[0];					# Get controller name without "Controller"
			require(ROOT.'libs/form.php');
			require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
		}

		function loadModel($name){
			require_once(ROOT.'models/'.strtolower($name).'.php');
			$this->$name = new $name($this->bdd);
		}
	}

?>