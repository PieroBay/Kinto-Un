<?php
	namespace KintoUn;

	use KintoUn\libs\Security;
	use KintoUn\libs\ErrorRender;
	use KintoUn\libs\Debug;

	use KintoUn\core\Controller;
	use KintoUn\core\Routing;
	use KintoUn\core\Model;

	use KintoUnSkeleton\src\ressources\layout\LayoutController;

	class App{

		private $configFile;
		private $ifFolder;  
		private $config; 	
		private $link; 		
		private $bdd;
		private $Info;
		private $urlParams;


		function __construct(){
			define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));
			header('Access-Control-Allow-Origin: *');
			header("Access-Control-Allow-Headers: Content-Type");
			$this->configFile = spyc_load_file(ROOT.'config/Config.yml');
			if($this->configFile["configuration"]['development']){ini_set('display_errors', 1);}
			$this->ifFolder   = ($this->configFile["configuration"]["folder"])?trim($this->configFile["configuration"]["folder"],"/")."/": "";
			$this->config 	  = $this->configFile['configuration'];
			$path = (isset($_SERVER['PATH_INFO']))?$_SERVER['PATH_INFO']:$_SERVER['REQUEST_URI'];
			$this->link 	  = '/'.trim($path, '/');
		}

		/**
		 * Define var
		 *
		 * @return void
		 */
		private function defineRoot(){
			define('APP', __DIR__."/");

			if($_SERVER['REMOTE_ADDR'] != '::1'){
				define('WEBROOT', 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://'.$_SERVER['HTTP_HOST'].'/'.$this->ifFolder);
			}else{
				define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
			}			
			define('WEBROOTAPP', str_replace('index.php', 'vendor/pierobay/kintoun/app/', $_SERVER['SCRIPT_NAME']));
		}
		
		/**
		 * Connection to db
		 *
		 * @return void
		 */
		private function dbConnect(){
			$dsn = 'mysql:host='.$this->config['database_host'].';dbname='.$this->config['database_name'];
			try{
			    $this->bdd = new \PDO($dsn, $this->config['database_user'], $this->config['database_password']);
			    $this->bdd->exec('SET NAMES utf8');
			    $this->bdd->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){ echo 'Échec lors de la connexion : ' . $e->getMessage(); }			
		}

		/**
		 * Set info to var
		 *
		 * @param int $step
		 * @return void
		 */
		private function setInfo($step){
			if($step == 1){
				$_SESSION['ROLE']  = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'];
				$_SESSION['local'] = $this->config['local'];
				$_SESSION['lang']  = (!isset($_SESSION['lang']))? $this->config['local'] : $_SESSION['lang'];			

				$this->info = array(
						"Session" =>	array(
						"ROLE"    => $_SESSION['ROLE'],
						"local"   => $_SESSION['local'],
						"lang"    => $_SESSION['lang'],
						"token"   => $_SESSION['KU_TOKEN'],
						"all"     => $_SESSION,
					),
					"Info"	=>	array(
						"Root"             => 	ROOT,
						"Webroot"          => 	WEBROOT,
						"WebrootApp"       => 	WEBROOTAPP,
						"APP"			   => 	APP,
						"Ressources"       => 	WEBROOT."src/ressources/",
						"lang"             => 	$_SESSION['lang'],
						"Template"         =>	$this->config['template'],
						"Output"	       =>	"",
						"Parametres"	   =>	"",
						"GET"			   =>	$_GET,
					),
				);
			}else{
				$this->info["Info"]['lang']       = $_SESSION['lang'];
				$this->info["Session"]['lang']    = $_SESSION['lang'];
				$this->info["Info"]['Output']     = $this->urlParams['output'];
				$this->info["Info"]['Parametres'] = $this->urlParams['parametres'];
				$this->info["Info"] += array(
						"RouteName"         =>	 $this->urlParams['routeName'],
						"Project"           =>	 $this->urlParams['project'],
						"Controller"        =>	 $this->urlParams['controller'],
						"ControllerFolder"  =>	 $this->urlParams['controller'].'Controller',
						"Action"            =>	 $this->urlParams['action'],
						"ActionComplete"    =>	 $this->urlParams['action'].'Action',
				);
			}
		}

		/**
		 * Set rooting
		 *
		 * @return void
		 */
		private function setRooting(){
			$setError  = new ErrorRender($this->bdd,$this->info, $this->configFile);
			Routing::start($this->link,$setError);
		}

		/**
		 * Include correct Controller
		 *
		 * @return void
		 */
		private function includeController(){
			require(ROOT.'src/project/'.$this->info["Info"]['Project'].'/controller/'.$this->info["Info"]['ControllerFolder'].'.php');

			$controllerFolder = new $this->info["Info"]['ControllerFolder']($this->bdd, $this->info, $this->configFile);

			if(method_exists($controllerFolder, $this->info["Info"]['ActionComplete']) && is_array($this->urlParams['parametres'])){
				call_user_func_array(array($controllerFolder, $this->info["Info"]['ActionComplete']), $this->urlParams['parametres']);
			}else{
				$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
			}

			if(!file_exists(ROOT.'src/project/'.$this->info["Info"]['Project'])){
				$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
			}			
		}

		/**
		 * Execution Script
		 *
		 * @return void
		 */
		public function run(){
			$this->defineRoot();
			$this->dbConnect();

			$Security = new Security($this->configFile);
			$Security->newToken();

			$this->setInfo(1);
			$this->setRooting();
			$this->urlParams = Routing::$params;
			$this->setInfo(2);
			
			$this->layout      		    = new LayoutController($this->bdd,$this->info,$this->configFile);
			$this->info["Layout"]		= $this->layout->layout();

			$this->includeController();
		}
	}