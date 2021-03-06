<?php 
	
	namespace KintoUn\core;

	use KintoUn\libs\Security;
	use KintoUn\libs\FlashMessage;
	use KintoUn\libs\SendMail;
	use KintoUn\libs\Restful;
	use KintoUn\libs\Debug;

	use KintoUn\core\Model;

	/**
	 * Controller Class
	 */
	class Controller{

		protected $bdd;
		protected $Session;
		protected $info;
		protected $sendMail;
		protected $_PUT;
		protected $configYml;
		protected $xml;
		protected $is_valid;
		protected $ROLE;

		/**
		 * Constructor
		 *
		 * @param object $bdd
		 * @param array $info
		 * @param array $configYml
		 */
		public function __construct($bdd, $info, $configYml){
			$Security = new Security($configYml);
			$session  = new FlashMessage();
			$mail     = new SendMail();

			$this->xml       = $info['Info']['Output'];
			$this->info      = $info;
			$this->configYml = $configYml;
			$this->bdd       = $bdd;

			$this->ROLE      = $_SESSION['ROLE'];
			
			$this->sendMail  = $mail;
			$this->Session   = $session;
			if(strpos(get_class($this), "LayoutController") === false){
				$this->is_valid  = $Security->isValid();
			}
			if(isset($this->table)){
				foreach ($this->table as $v) {
					$this->loadModel($v);
				}
			}

			$this->_PUT = Restful::parsePutReq($this->info["Info"]['Parametres']);
		}

		/**
		 * Check the role of users
		 *
		 * @param string $typeRole
		 * @return void
		 */
		public function ROLE($typeRole='visiteur'){
			if($this->ROLE == $typeRole){
				return true;
			}else{
				return false;
			}
		}

		/**
		 * Redirect to route
		 *
		 * @param string $routeName
		 * @param array $data [route params]
		 * @return void
		 */
		public function redirectUrl($routeName, $data=array()){
			$route = spyc_load_file(ROOT.'config/Routing.yml');
		
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
					$tri = trim($link,'/');

					if(empty($tri)){
						header('Location: '.WEBROOT);
					}else{
						header('Location: '.WEBROOT.trim($link,'/').'/');
					}
					
					die();
				}
			}		
		}

		/**
		 * Render view and send data from controller to view
		 *
		 * @param array $data
		 * @return void
		 */
		public function render($data=array()){
			$filename = explode("Action", $this->info['Info']['Action'])[0];
			$data = array_merge($data, $this->info);
			switch (strtolower($this->info['Info']['Template'])){
			    case "twig":
					require(APP.'libs/template/twig/LoaderTemplate.php');
					require (APP.'libs/template/autoLoad.php');
					echo $twig->render('src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.html.twig', json_decode(json_encode($data), true));
			        break;
			    case "smarty":
			        require(ROOT.'vendor/smarty/smarty/libs/Smarty.class.php');
			        $smarty = new Smarty();
			        require (APP.'libs/template/autoLoad.php');
					$smarty->compile_dir = APP.'libs/template/smarty/templates_c/';
					$smarty->config_dir = APP.'libs/template/smarty/configs/';
					$smarty->cache_dir = APP.'libs/template/smarty/cache/';
			        $smarty->display(ROOT.'src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.tpl', json_decode(json_encode($data), true));
			        break;
			    case "php":
			    case "none":
			    	require (APP.'libs/template/autoLoad.php');
			    	require(ROOT.'src/project/'.$this->info['Info']['Project'].'/views/'.$this->info['Info']['Controller'].'/'.$filename.'.php');
			        break;
			}
		}

		/**
		 * Load external model
		 *
		 * @param string $table
		 * @return void
		 */
		public function loadModel($table){
			$tableModel = $table.'Model';
		//	require_once(APP.'app/core/Model.php');
			$this->$table = new Model($this->bdd, $table, $this->configYml);			
			if(file_exists(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php')){
				require_once(ROOT.'src/project/'.$this->info['Info']['Project'].'/models/'.$tableModel.'.php');
				$this->$tableModel = new $tableModel($this->bdd, $table, $this->configYml);
			}
		}

		/**
		 * Load automatically model
		 *
		 * @param string $projectName
		 * @param string $tableName
		 * @return void
		 */
		public function includeModel($projectName,$tableName){
			require_once(ROOT.'src/project/'.$projectName.'/models/'.$tableName.'Model.php');
			$className = $tableName."Model";
			return new $className($this->bdd, $tableName, $this->configYml);
		}
	}