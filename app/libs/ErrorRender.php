<?php

	namespace KintoUn\libs;

	use KintoUn\errors\errorController;

/**
 * Class that generate error page
 */
class ErrorRender{

	protected $data = array();
	protected $info = array();
	protected $bdd;
	protected $configYml;
	protected $xml;

	/**
	 * Constructor
	 *
	 * @param object $bdd
	 * @param array $info
	 * @param array $configYml
	 */
	public function __construct($bdd,$info,$configYml){
		$this->info = $info;
		$this->xml = $info['Info']['Output'];
		$this->configYml = $configYml;
		$this->bdd = $bdd;
	}

	/**
	 * Generate error page
	 *
	 * @param string $number
	 * @param string $message
	 * @return void
	 */
	public function generate($number = '404', $message){
		$e = new errorController($this->bdd,$this->info,$this->configYml);

		$this->data = $e->errorAction();		

		$erreur = array(
			"Error"	=>	array(
				"Number"  => $number,
				"Message" => $message,
			),
			"Info" => array(
				"Webroot"	=> WEBROOT,
				"WebrootApp"	=> WEBROOTAPP,
				"Ressources"=> WEBROOT."/src/ressources/",
				"APP"=> APP,
			)
		);
		$array = array_merge($erreur, $this->data);

		switch (strtolower($this->info['Info']['Template'])){
		    case "twig":
				require(APP.'libs/template/twig/LoaderTemplate.php');
				require (APP.'libs/template/autoLoad.php');
				echo $twig->render('error.html.twig',$array);		        
				break;
		    case "smarty":
		        require(ROOT.'vendor/smarty/smarty/libs/Smarty.class.php');
		        $smarty = new Smarty();
		        require (APP.'libs/template/autoLoad.php');
				$smarty->compile_dir = APP.'libs/template/smarty/templates_c/';
				$smarty->config_dir = APP.'libs/template/smarty/configs/';
				$smarty->cache_dir = APP.'libs/template/smarty/cache/';
		        $smarty->display(APP.'errors/error.tpl',$array);
		        break;
		    case "php":
		    case "none":
		    	require (APP.'libs/template/autoLoad.php');
		    	require(APP.'errors/error.php');
		        break;
		}
		exit();
	}

	public static function dismount($object) {
	    $reflectionClass = new ReflectionClass(get_class($object));
	    $array = array();
	    foreach ($reflectionClass->getProperties() as $property) {
	        $property->setAccessible(true);
	        $array[$property->getName()] = $property->getValue($object);
	        $property->setAccessible(false);
	    }
	    return $array;
	}

	/**
	 * Send data to view
	 *
	 * @param array $e
	 * @return void
	 */
	public static function renderError($e=array()){
		$e = self::dismount($e);
		$erreur = array(
			"Info" => array(
				"Webroot"	=> WEBROOT,
			)
		);
		$array = array_merge($erreur, $e);
		require(APP.'libs/template/twig/LoaderTemplate.php');
		echo $twig->render('errors/warning.html.twig', $array);
	}
}
