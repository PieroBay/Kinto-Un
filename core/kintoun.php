<?php
session_start();

	define('WEBROOT', str_replace('core/Kintoun.php', '', $_SERVER['SCRIPT_NAME']));
	define('ROOT', str_replace('core/Kintoun.php', '', $_SERVER['SCRIPT_FILENAME']));
	require_once(ROOT.'vendor/autoload.php');
	require_once(ROOT.'core/Routing.php');
	require_once(ROOT.'core/Controller.php');
	require_once(ROOT.'libs/Upload.php');

	$config = spyc_load_file(ROOT.'core/config.yml')['configuration'];

	$dsn = 'mysql:host='.$config['database_host'].';dbname='.$config['database_name'];
	try{
	    $bdd = new PDO($dsn, $config['database_user'], $config['database_password']);
	    $bdd->exec('SET NAMES utf8');
	    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOException $e){ echo 'Échec lors de la connexion : ' . $e->getMessage(); }

	spl_autoload_register(function($class){
	    require_once(ROOT.'libs/'.$class.'.php');
	});
	
	$_SESSION['ROLE'] = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'] ;

	$home = $config['default_project'];
	$_SESSION['local'] = $config['local'];
	$_SESSION['lang'] = (!isset($_SESSION['lang']))? $config['local'] : $_SESSION['lang'];

	$link = '/'.trim($_SERVER['PATH_INFO'], '/');

	$info = array(
		"Session"	=>	array(
			"ROLE" 	=> $_SESSION['ROLE'],
			"local" => $_SESSION['local'],
			"lang" => $_SESSION['lang'],
		),
		"Info"	=>	array(
			"Root"             => 	ROOT,
			"Webroot"          => 	WEBROOT,
			"lang"             => 	$_SESSION['lang'],
			"Template"         =>	$config['template'],
			"Parametres"	   =>	"",
		),
	);

	$setError = new Error($bdd,$info);
	Routing::start($link,$setError);
	$urlParams = Routing::$params;

	$info["Info"]['lang'] = $_SESSION['lang'];
	$info["Session"]['lang'] = $_SESSION['lang'];
	$info["Info"] += array(
			"RouteName"        =>	$urlParams['routeName'],
			"Project"          =>	$urlParams['project'],
			"Controller"       =>	$urlParams['controller'],
			"ControllerFolder" =>	$urlParams['controller'].'Controller',
			"Action"           =>	$urlParams['action'],
			"ActionComplete"   =>	$urlParams['action'].'Action',
	);

	require(ROOT.'src/project/'.$info["Info"]['Project'].'/controller/'.$info["Info"]['ControllerFolder'].'.php');

	$controllerFolder = new $info["Info"]['ControllerFolder']($bdd, $info);

	if(method_exists($controllerFolder, $info["Info"]['ActionComplete']) && is_array($urlParams['parametres'])){
		call_user_func_array(array($controllerFolder, $info["Info"]['ActionComplete']), $urlParams['parametres']);
	}else{
		$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}

	if(!file_exists(ROOT.'src/project/'.$info["Info"]['Project'])){
		$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}