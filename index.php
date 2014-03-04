<?php


	define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
	define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));
	
	require(ROOT.'core/connect-bdd.php');
	require(ROOT.'core/model.php');
	require(ROOT.'core/controller.php');

	$params = explode('/', $_GET['p']);

	if(!empty($params[0]) && $params[0] == "admin"){
		$controller = $params[0]."Controller"; # adminController
	}else{
		$controller = 'publicController';
		$control = 'publicController';
	}

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
		echo 'error 404';
	}

	
?>