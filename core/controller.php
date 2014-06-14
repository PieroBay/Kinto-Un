<?php 
	class Controller{

		var $value = array();
		protected $bdd;
		public $Session;
		protected $info;

		function __construct($bdd, $info){
			$this->info=$info;
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

		public function ROLE($typeRole='visiteur'){
			if($_SESSION['ROLE'] == $typeRole){
				return true;
			}else{
				return false;
			}
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
			$filename = explode("Action", $this->info['Info']['Action'])[0];
			require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');

			$array = array_merge($array, $this->info);
			echo $twig->render('src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.html.twig', $array);
		}

		function loadModel($table){
			$tableModel = $table.'Model';
			require_once(ROOT.'core/model.php');
			$this->$table = new Model($this->bdd, $table);			
			if(file_exists(ROOT.'models/'.$tableModel.'.php')){
				require_once(ROOT.'models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table);
			}
		}
	}

?>