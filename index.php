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

	$count=0;
	$dossier = opendir('src/project');
	while(false !== ($fichier = readdir($dossier))){
		if($fichier[0] == '_'){
			$count++;
			$home = $fichier; # on recupere le projet qui sera lancé en premier
		}
	}
	closedir($dossier);
	if($count>1){ 
		$erreur = array(
			"Error"	=>	array(
				"Number"  => 409,
				"Message" => "Il ne peut y avoir qu'un projet de lancement.",
			),
		);
		echo $twig->render('core/errors/error.html.twig',$erreur); return false; 
	}

	$params = explode('/', $_GET['p']);
	$project = !empty($params[0]) ? $params[0] : $home; # $home = _project
	$controller = !empty($params[1]) ? $params[1] : 'public';

	if(!empty($controller) && $controller == "admin"){	# si le controller n'est pas vide = "Admin" sinon "public"
		$controllerFolder = $controller."Controller"; # = adminController
	}else{
		$controllerFolder = 'publicController'; # sinon publicController (il ne peut avoir que deux controller "publicController" et "adminController")
	}
	
	if($controllerFolder == 'publicController'){ # le controller public ne doit jamais se mettre dans l'url
		$action = !empty($params[1]) ? $params[1]."Action" : 'indexAction'; # si le controller est public, on l'affiche pas dans l'url et le premier param devient l'action
	}else if($controllerFolder == 'adminController'){
		$action = !empty($params[2]) ? $params[2]."Action" : 'indexAction'; # si le controller est admin, on l'affiche dans l'url et le deuxieme param deviens l'action
	}
	
	$project = ($project == substr($home,1)) ? '_'.$home : $project;

	$info = array(
		"Project"    =>	$project,
		"Controller" => $controller,
		"Action"     => $action,
	);

	require('src/project/'.$project.'/controller/'.$controllerFolder.'.php');

	$controllerFolder = new $controllerFolder($bdd, $info);
	
	if(method_exists($controllerFolder, $action)){
/*		if($controller.'Controller' == 'publicController'){
			unset($params[0]);
			unset($params[1]);
			unset($params[2]);
		}else{
			unset($params[0]);
			unset($params[1]);
			unset($params[2]);
		}*/
		call_user_func_array(array($controllerFolder, $action), $params);
	}else{
		$erreur = array(
			"Error"	=>	array(
				"Number"  => 404,
				"Message" => "La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.",
			),
		);
		echo $twig->render('core/errors/error.html.twig',$erreur);
	}
?>