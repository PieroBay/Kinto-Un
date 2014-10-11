<?php
session_start();

	define('WEBROOT', str_replace('core/kintoun.php', '', $_SERVER['SCRIPT_NAME']));
	define('ROOT', str_replace('core/kintoun.php', '', $_SERVER['SCRIPT_FILENAME']));

	require(ROOT.'core/component/spyc/Spyc.php');
	$config = spyc_load_file(ROOT.'core/config.yml')['configuration'];

	$dsn = 'mysql:host='.$config['database_host'].';dbname='.$config['database_name'];
	try{
	    $bdd = new PDO($dsn, $config['database_user'], $config['database_password']);
	    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOException $e){ echo 'Échec lors de la connexion : ' . $e->getMessage(); }

	require(ROOT.'vendor/autoload.php');
	require(ROOT.'core/controller.php');
	require(ROOT.'libs/session.php');
	require(ROOT.'libs/upload.php');
	require(ROOT.'libs/error.php');
	require(ROOT.'libs/form.php');

	$_SESSION['ROLE'] = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'] ;

	$home = $config['default_project'];
	$_SESSION['local'] = $config['local'];
	$_SESSION['lang'] = (!isset($_SESSION['lang']))? $config['local'] : $_SESSION['lang'];
	$params = explode('/', $_GET['p']);
	$project = !empty($params[0]) ? $params[0] : $home;

	$para=array();
	function addParams($nbParams,$controllerName,$fichier=null){
		global $params;
		$action = !empty($params[$nbParams]) ? $params[$nbParams]."Action" : 'indexAction';
		$key = (isset($params[$nbParams+1])) ? $params[$nbParams+1] : '';
		$project = ($fichier == null)? $GLOBALS['home']: $fichier;
		$para=array(
			"controllerFolder"=>$controllerName.'Controller',
			"controller"=>$controllerName,
			"action"=>$action,
			"project"=>$project,
			"key"=>$key,
			"e"=>$nbParams+1,
		);
		return $para;
	}

	if(file_exists(ROOT.'src/ressources/translate/'.$params[0].'.yml') || $params[0] == $_SESSION['local'] && isset($params[0])){ # si lang dans url
		$_SESSION['lang'] = $params[0];
		if(!empty($params[1]) && file_exists(ROOT.'src/project/'.$params[1])){ # si params1 == $projet
			$para = (isset($params[2]) && $params[2] == "admin")? addParams(3,"admin",$params[1]): addParams(2,"public",$params[1]);
		}else{ # Si projet par defaut
			$para = (isset($params[1]) && $params[1] == "admin")? addParams(2,"admin"): addParams(1,"public");
		}
	}else{
		if(file_exists(ROOT.'src/project/'.$params[0])){ # si params1 == $projet
			$para = (isset($params[1]) && $params[1] == "admin")? addParams(2,"admin",$params[0]): addParams(1,"public",$params[0]);
		}else{ # Si projet par defaut
			$para = (isset($params[0]) && $params[0] == "admin")? addParams(1,"admin"): addParams(0,"public");
		}
	}

	$info = array(
		"Session"	=>	array(
			"ROLE" 	=> $_SESSION['ROLE'],
			"local" => $_SESSION['local'],
			"lang" => $_SESSION['lang'],
		),
		"Info"	=>	array(
			"Root"			=> 	ROOT,
			"Webroot"		=> 	WEBROOT,
			"Project"		=>	$para['project'],
			"Controller"	=>	$para['controller'],
			"Action"		=>	$para['action'],
			"Key"      		=> 	$para['key'],
			"lang" 			=> 	$_SESSION['lang'],
			"Template"		=>	$config['template'],
		),
	);

	require(ROOT.'src/project/'.$para['project'].'/controller/'.$para['controllerFolder'].'.php');

	$controllerFolder = new $para['controllerFolder']($bdd, $info);
	
	if(method_exists($controllerFolder, $para['action'])){
		switch ($para['e']) {
		    case 1:
				unset($params[0]);
		        break;
		    case 2:
		        unset($params[0]);
		        unset($params[1]);
		        break;
		    case 3:
		        unset($params[0]);
				unset($params[1]);
				unset($params[2]);
		        break;
		    case 4:
		        unset($params[0]);
				unset($params[1]);
				unset($params[2]);
				unset($params[3]);
		        break;
		}
		call_user_func_array(array($controllerFolder, $para['action']), $params);
	}else{
		Error::generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}

	if(!file_exists(ROOT.'src/project/'.$para['project'])){
		Error::generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}
