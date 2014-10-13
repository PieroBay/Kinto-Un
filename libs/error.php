<?php
class Error{

	protected $data = array();
	protected $info = array();

	public function __construct($bdd,$info){
		$this->info = $info;
		require (ROOT.'core/errors/errorController.php');
		$e = new errorController($bdd,$info);
		$this->data = $e->errorAction();
	}

	public function generate($number = '404', $message){
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
				echo $twig->render('core/errors/error.html.twig',$array);		        
				break;
		    case "smarty":
		        require(ROOT.'vendor/smarty/smarty/distribution/libs/Smarty.class.php');
		        $smarty = new Smarty();
		        require (ROOT.'libs/template/autoLoad.php');
				$smarty->compile_dir = ROOT.'libs/template/smarty/templates_c/';
				$smarty->config_dir = ROOT.'libs/template/smarty/configs/';
				$smarty->cache_dir = ROOT.'libs/template/smarty/cache/';
		        $smarty->display(ROOT.'core/errors/error.tpl',$array);
		        break;
		    case "php":
		    case "none":
		    	require (ROOT.'libs/template/autoLoad.php');
		    	require(ROOT.'core/errors/error.php');
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
		require(ROOT.'libs/template/twig/LoaderTemplate.php');
		echo $twig->render('core/errors/warning.html.twig', $e);
	}
}