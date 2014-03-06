<?php 
	class Controller{

		var $value = array();
		protected $bdd;
		public $Session;

		function __construct($bdd){
			$this->bdd=$bdd;
			$session = new Session();
			$this->setSession($session);
			if(isset($this->table)){
				foreach ($this->table as $v) {
					$this->loadModel($v);
				}
			}
		}

		public function setSession($session){
			$this->Session = $session;
		}


		public function redirectUrl($redi){
			$controllers = explode(":", $redi);
			$controller = $controllers[0];
			$action = explode(".", $controllers[1])[0];
			if($controller == 'public'){
				$controller = "";
			}
			header('Loction: '.WEBROOT.$controller.$action);
		}

		function render($d=array()){
			$this->value = array_merge($this->value,$d);
			$array = $this->value;
			$filename = explode("Action", debug_backtrace()[1]["function"])[0];
			$contro = explode("Controller", get_class($this))[0];
			require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
			echo $twig->render($contro.'/'.$filename.'.html.twig', $array);
		}

		function loadModel($table){
			require_once(ROOT.'core/model.php');
			$this->$table = new Model($this->bdd, $table);
		}
	}

?>