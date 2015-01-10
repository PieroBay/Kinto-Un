<?php 
	class Controller{

		protected $bdd;
		protected $Session;
		protected $info;
		protected $sendMail;
		protected $_PUT;

		public function __construct($bdd, $info){
			$this->info = $info;
			$this->bdd = $bdd;
			$session = new Session();
			$mail = new SendMail();
			$this->sendMail = $mail;
			$this->Session = $session;
			if(isset($this->table)){
				foreach ($this->table as $v) {
					$this->loadModel($v);
				}
			}
			$this->_PUT = Request::parsePutReq($this->info["Info"]['Parametres']);
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
			$controller = ($controller == 'public') ? '' : $controller;
			$action = ($action == 'index') ? '' : $action;
			header('Location: '.WEBROOT.$controller.$action.$data);
		}

		public function render($data=array()){
			$filename = explode("Action", $this->info['Info']['Action'])[0];
			$data = array_merge($data, $this->info);
			switch (strtolower($this->info['Info']['Template'])){
			    case "twig":
					require(ROOT.'libs/template/twig/LoaderTemplate.php');
					require (ROOT.'libs/template/autoLoad.php');
					echo $twig->render('src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.html.twig', $data);
			        break;
			    case "smarty":
			        require(ROOT.'vendor/smarty/smarty/libs/Smarty.class.php');
			        $smarty = new Smarty();
			        require (ROOT.'libs/template/autoLoad.php');
					$smarty->compile_dir = ROOT.'libs/template/smarty/templates_c/';
					$smarty->config_dir = ROOT.'libs/template/smarty/configs/';
					$smarty->cache_dir = ROOT.'libs/template/smarty/cache/';
			        $smarty->display(ROOT.'src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.tpl', $data);
			        break;
			    case "php":
			    case "none":
			    	require (ROOT.'libs/template/autoLoad.php');
			    	require(ROOT.'src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.php');
			        break;
			}
		}

		public function renderJson($data=array()){
			header('Content-Type: application/json');
			exit(json_encode($data));
		}

		public function loadModel($table){
			$tableModel = $table.'Model';
			require_once(ROOT.'core/Model.php');
			$this->$table = new Model($this->bdd, $table);			
			if(file_exists(ROOT.'models/'.$tableModel.'.php')){
				require_once(ROOT.'models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table);
			}
		}
	}