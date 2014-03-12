<?php
session_start();

	define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
	define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));

	require(ROOT.'core/connect-bdd.php');
	require(ROOT.'core/model.php');
	require(ROOT.'core/controller.php');
	require(ROOT.'libs/session.php');
	require(ROOT.'libs/form.php');
	require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
	
	$_SESSION['ROLE'] = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'] ;
	$params = explode('/', $_GET['p']);

	if(!empty($params[0]) && $params[0] == "admin"){
		$controller = $params[0]."Controller"; # adminController
	}else{
		$controller = 'publicController';
	}

	$control = $controller;

	if($controller == 'publicController'){
		$action = !empty($params[0]) ? $params[0]."Action" : 'indexAction';
	}else if($controller == 'adminController'){
		$action = !empty($params[1]) ? $params[1]."Action" : 'indexAction';
	}

	require('controller/'.$controller.'.php');
	$controller = new $controller($bdd);
	if(method_exists($controller, $action)){
		if($control == 'publicController'){
			unset($params[0]);
		}else{
			unset($params[0]);
			unset($params[1]);
		}
		
		call_user_func_array(array($controller, $action), $params);
	}else{
		
		echo $twig->render('core/errors/404.html.twig');
	}	
?>