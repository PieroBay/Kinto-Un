<?php
class Error{
	public static function generate($number = '404', $message){
		$erreur = array(
			"Error"	=>	array(
				"Number"  => $number,
				"Message" => $message,
			),
			"Info" => array(
				"Webroot"	=> WEBROOT,
			)
		);

		require(ROOT.'libs/template/twig/LoaderTemplate.php');
		echo $twig->render('core/errors/error.html.twig',$erreur);
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