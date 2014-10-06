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

	require(ROOT.'core/model.php');
	require(ROOT.'core/controller.php');
	require(ROOT.'libs/session.php');
	require(ROOT.'libs/upload.php');
	require(ROOT.'libs/error.php');
	require(ROOT.'libs/form.php');
	require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
	$error = new Error();

	$_SESSION['ROLE'] = !isset($_SESSION['ROLE']) ? 'visiteur' : $_SESSION['ROLE'] ;

	$home = $config['default_project'];
	$params = explode('/', $_GET['p']);
	$project = !empty($params[0]) ? $params[0] : $home;

	$dossier = opendir(ROOT.'src/project/');
	while(false !== ($fichier = readdir($dossier))){
		if($fichier != $params[0]){ # verifie que l'action n'a pas le nom d'un projet, si il a le nom d'un projet, ca devient un projet (../projet/action)
			if(!empty($params[0]) && $params[0] == "admin"){ # controller admin / projet home / action index (../home/admin/index)
				$controllerFolder = $params[0]."Controller";
				$controller = "admin";
				$action = !empty($params[1]) ? $params[1]."Action" : 'indexAction';
				$project = $home;
				$e=1;
			}else{ # controller public / projet home / action params0 (../action) (../home/public/index)
				$controllerFolder = 'publicController';
				$action = !empty($params[0]) ? $params[0]."Action" : 'indexAction';
				$controller = "public";
				$project = $home;
				$e=2;
			}
		}else{
			if(!empty($params[1]) && $params[1] == "admin"){
				$controllerFolder = $params[1]."Controller";
				$controller = "admin";
				$action = !empty($params[2]) ? $params[2]."Action" : 'indexAction';
				$project = $fichier;
				$e=3;
			}else{
				$controllerFolder = 'publicController';
				$action = !empty($params[1]) ? $params[1]."Action" : 'indexAction';
				$controller = "public";
				$project = $fichier;
				$e=4;
			}
		}
 	}
 	closedir($dossier);

	$info = array(
		"Session"	=>	array(
			"ROLE" => $_SESSION['ROLE'],
		),
		"Info"	=>	array(
			"Root"	=> ROOT,
			"Webroot"	=> WEBROOT,
			"Project"	=>	$project,
			"Controller"	=>	$controller,
			"Action"	=>	$action,
		),
	);

	require(ROOT.'src/project/'.$project.'/controller/'.$controllerFolder.'.php');

	$controllerFolder = new $controllerFolder($bdd, $info);
	
	if(method_exists($controllerFolder, $action)){
		switch ($e) {
		    case 1:
				unset($params[0]);
				unset($params[1]);
		        break;
		    case 2:
		        unset($params[0]);
		        break;
		    case 3:
		        unset($params[0]);
				unset($params[1]);
				unset($params[2]);
		        break;
		    case 4:
		        unset($params[0]);
				unset($params[1]);
		        break;
		}
		call_user_func_array(array($controllerFolder, $action), $params);
	}else{
		$error->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}

	if(!file_exists(ROOT.'src/project/'.$project)){
		$error->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
	}
?>