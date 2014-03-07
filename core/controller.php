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

		public function setRole($rol){
			$this->role = $rol;
		}

		public function redirectUrl($url, $data=""){
			$controllers = explode(":", $url);
			$controller = $controllers[0];
			$action = explode(".", $controllers[1])[0];
			$data = !empty($data) ? '/'.$data : $data;
			if($controller == 'public'){
				$controller = "";
			}
			header('Location: '.WEBROOT.$controller.$action.$data);
		}

		function render($d=array()){
			$this->value = array_merge($this->value,$d);
			$array = $this->value;
			$filename = explode("Action", debug_backtrace()[1]["function"])[0];
			$contro = explode("Controller", get_class($this))[0];
			require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');

			$sess = array(
				"session"	=>	array(
					"ROLE" => $_SESSION['ROLE'],
				),
			);

			$array = array_merge($array, $sess);
			echo $twig->render('pages/'.$contro.'/'.$filename.'.html.twig', $array);
		}

		function loadModel($table){
			$tableModel = $table.'Model';
			if(file_exists(ROOT.'models/'.$tableModel.'.php')){
				require_once(ROOT.'models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table);
			}else{
				require_once(ROOT.'core/model.php');
				$this->$table = new Model($this->bdd, $table);
			}
		}
	}

?>