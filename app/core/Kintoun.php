<?php
if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
    header('Location: http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI']);
    exit;
}
session_start();
	header('Access-Control-Allow-Origin: *');
	if($_SERVER['REMOTE_ADDR'] != '::1'){
		define('WEBROOT', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URl'].'/');
	}else{
		define('WEBROOT', str_replace('app/core/Kintoun.php', '', $_SERVER['SCRIPT_NAME']));
	}

	define('ROOT', str_replace('app/core/Kintoun.php', '', $_SERVER['SCRIPT_FILENAME']));

	require_once(ROOT.'vendor/autoload.php');
	require_once(ROOT.'app/core/Routing.php');
	require_once(ROOT.'app/core/Controller.php');
	require_once(ROOT.'src/ressources/layout/layoutController.php');

	$configFile = spyc_load_file(ROOT.'app/config/Config.yml');
	$config     = $configFile['configuration'];
	if($config['development']){ini_set('display_errors', 1);}
	$dsn = 'mysql:host='.$config['database_host'].';dbname='.$config['database_name'];
	try{
	    $bdd = new PDO($dsn, $config['database_user'], $config['database_password']);
	    $bdd->exec('SET NAMES utf8');
	    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOException $e){ echo 'Échec lors de la connexion : ' . $e->getMessage(); }

	spl_autoload_register(function($class){
	    require_once(ROOT.'libs/'.$class.'.php');
	});

	$Security = new Security($configFile);
	$Security->newToken();

	$_SESSION['ROLE']  = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'];
	$_SESSION['local'] = $config['local'];
	$_SESSION['lang']  = (!isset($_SESSION['lang']))? $config['local'] : $_SESSION['lang'];

	$link = '/'.trim($_SERVER['PATH_INFO'], '/');

	$info = array(
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
			"Ressources"       => 	WEBROOT."src/ressources/",
			"lang"             => 	$_SESSION['lang'],
			"Template"         =>	$config['template'],
			"Output"	       =>	"",
			"Parametres"	   =>	"",
			"GET"			   =>	$_GET,
		),
	);

	$setError  = new Error($bdd,$info, $configFile);

	Routing::start($link,$setError);
	$urlParams = Routing::$params;

	$info["Info"]['lang']       = $_SESSION['lang'];
	$info["Session"]['lang']    = $_SESSION['lang'];
	$info["Info"]['Output']     = $urlParams['output'];
	$info["Info"]['Parametres'] = $urlParams['parametres'];
	$info["Info"] += array(
			"RouteName"         =>	 $urlParams['routeName'],
			"Project"           =>	 $urlParams['project'],
			"Controller"        =>	 $urlParams['controller'],
			"ControllerFolder"  =>	 $urlParams['controller'].'Controller',
			"Action"            =>	 $urlParams['action'],
			"ActionComplete"    =>	 $urlParams['action'].'Action',
	);
	$layout      		        = new layoutController($bdd,$info,$configFile);
	$info["Layout"]			    = $layout->layout();

	require(ROOT.'src/project/'.$info["Info"]['Project'].'/controller/'.$info["Info"]['ControllerFolder'].'.php');

	$controllerFolder = new $info["Info"]['ControllerFolder']($bdd, $info, $configFile);

	if(method_exists($controllerFolder, $info["Info"]['ActionComplete']) && is_array($urlParams['parametres'])){
		call_user_func_array(array($controllerFolder, $info["Info"]['ActionComplete']), $urlParams['parametres']);
	}else{
		$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}

	if(!file_exists(ROOT.'src/project/'.$info["Info"]['Project'])){
		$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}