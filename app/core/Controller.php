<?php 
	class Controller{

		protected $bdd;
		protected $Session;
		protected $info;
		protected $sendMail;
		protected $_PUT;
		protected $configYml;
		protected $xml;
		protected $is_valid;

		public function __construct($bdd, $info, $configYml){
			$Security = new Security($configYml);
			$session  = new Session();
			$mail     = new SendMail();
			
			$this->xml       = $info['Info']['Output'];
			$this->info      = $info;
			$this->configYml = $configYml;
			$this->bdd       = $bdd;
			$this->is_valid  = $Security->isValid();
			$this->sendMail  = $mail;
			$this->Session   = $session;
			
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

		public function redirectUrl($routeName, $data=array()){
			$route = spyc_load_file(ROOT.'app/config/Routing.yml');
		
			foreach ($route as $key => $value) {
				$project = $route[$key]['project'];
				$linkP = $route[$key]['pattern'];
				$routeP = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');

				if(isset($routeP[$routeName])){
					$link = $routeP[$routeName];

					$patternEx = explode('/', trim($link['pattern'],'/'));
					foreach ($patternEx as $k => $v){
						
						if(!empty($v) && $v[0] == "{" && $v[1] == "_" && $v != "{_lang}"){
							$v = substr($v,1,-1);
							
							if(!array_key_exists($v, $data)){
								$data[$v] = "";
							}
						}
					}

					foreach ($data as $k => $v){
						$linkP = preg_replace('#\{'.$k.'\}#', $v, $linkP);
						$link['pattern'] = preg_replace('#\{'.$k.'\}#', $v, $link['pattern']);
					}
					if(strpos($link['pattern'], "{_lang}") !== false){
						$link['pattern'] = preg_replace('#\{_lang\}#', $_SESSION['lang'], $link['pattern']);
					}
					if(strpos($linkP, "{_lang}") !== false){
						$linkP = preg_replace('#\{_lang\}#', $_SESSION['lang'], $linkP);
					}

					$link = trim($linkP,'/').'/'.trim($link['pattern'],'/');
					$link = preg_replace('#//#', '/', $link);
					if(empty(trim($link,'/'))){
						header('Location: '.WEBROOT);
					}else{
						header('Location: '.WEBROOT.trim($link,'/').'/');
					}
					
					die();
				}
			}		

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

		public function loadModel($table){
			$tableModel = $table.'Model';
			require_once(ROOT.'app/core/Model.php');
			$this->$table = new Model($this->bdd, $table, $this->configYml);			
			if(file_exists(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php')){
				require_once(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table, $this->configYml);
			}
		}
	}