<?php
class Error{

	protected $data = array();
	protected $info = array();
	protected $bdd;
	protected $connectYml;
	protected $xml;

	public function __construct($bdd,$info,$connectYml){
		$this->info = $info;
		$this->xml = $info['Info']['Output'];
		$this->connectYml = $connectYml;
		$this->bdd = $bdd;
		require (ROOT.'app/errors/errorController.php');
	}

	public function generate($number = '404', $message){
		$e = new errorController($this->bdd,$this->info,$this->connectYml);
		$this->data = $e->errorAction();		

		$erreur = array(
			"Error"	=>	array(
				"Number"  => $number,
				"Message" => $message,
			),
			"Info" => array(
				"Webroot"	=> WEBROOT,
			)
		);
		$array = array_merge($erreur, $this->data);

		switch (strtolower($this->info['Info']['Template'])){
		    case "twig":
				require(ROOT.'libs/template/twig/LoaderTemplate.php');
				require (ROOT.'libs/template/autoLoad.php');
				echo $twig->render('app/errors/error.html.twig',$array);		        
				break;
		    case "smarty":
		        require(ROOT.'vendor/smarty/smarty/libs/Smarty.class.php');
		        $smarty = new Smarty();
		        require (ROOT.'libs/template/autoLoad.php');
				$smarty->compile_dir = ROOT.'libs/template/smarty/templates_c/';
				$smarty->config_dir = ROOT.'libs/template/smarty/configs/';
				$smarty->cache_dir = ROOT.'libs/template/smarty/cache/';
		        $smarty->display(ROOT.'app/errors/error.tpl',$array);
		        break;
		    case "php":
		    case "none":
		    	require (ROOT.'libs/template/autoLoad.php');
		    	require(ROOT.'app/errors/error.php');
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

	public static function renderError($e=array()){
		$e = self::dismount($e);
		$erreur = array(
			"Info" => array(
				"Webroot"	=> WEBROOT,
			)
		);
		$array = array_merge($erreur, $e);
		require(ROOT.'libs/template/twig/LoaderTemplate.php');
		echo $twig->render('app/errors/warning.html.twig', $array);
	}
}