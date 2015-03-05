<?php 
	class Controller{

		protected $bdd;
		protected $Session;
		protected $info;
		protected $sendMail;
		protected $_PUT;
		protected $connectYml;
		protected $xml;

		public function __construct($bdd, $info, $connectYml){
			$this->xml = $info['Info']['Output'];
			$this->info = $info;
			$this->connectYml = $connectYml;
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

		public function redirectUrl($url, $data=array()){
			$route = spyc_load_file(ROOT.'src/ressources/config/routing.yml');

			try{
				if(!isset($route[$url])) throw new Exception("Aucune route n'a été trouvé");
			}catch(Exception $e){
				Error::renderError($e);
				exit();
			}

			$pattern = $route[$url]['pattern'];

			if (strpos($pattern, "{") !== false){
				if(strpos($pattern, "{_lang}") !== false){
					$pattern = preg_replace('#\{_lang\}#', $_SESSION['lang'], $pattern);
				}

				$pattern = preg_replace_callback('#{(\w+)}#', function($m) use($data){ return $data[$m[1]]; }, $pattern);
			}
			header('Location: '.WEBROOT.trim($pattern,'/'));
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

		public function renderXml($data=array(),$unset=null,$rename=null){
			$d = array();
			foreach ($data as $k => $v) {
				$data[$k] = (array)$data[$k];

				if(isset($unset) && is_array($unset)){
					foreach ($unset as $key) {
						unset($data[$k][$key]);
					}
				}
				if(isset($rename) && is_array($rename)){
					foreach ($rename as $key => $value) {
						$data[$k][$value] = $data[$k][$key];
						unset($data[$k][$key]);
					}
				}

				$d[] = array_flip($data[$k]);
			}

			header('Content-Type: application/xml');
			$xml = new SimpleXMLElement('<items/>');
			
			foreach ($d as $key => $v) {
				$node = $xml->addChild('item');
				array_walk_recursive($v, array ($node, 'addChild'));
			}
			
			exit($xml->asXML());
		}

		public function loadModel($table){
			$tableModel = $table.'Model';
			require_once(ROOT.'core/Model.php');
			$this->$table = new Model($this->bdd, $table, $this->connectYml);			
			if(file_exists(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php')){
				require_once(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table, $this->connectYml);
			}
		}
	}