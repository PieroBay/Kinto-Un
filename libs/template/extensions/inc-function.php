<?php
	function inc($routeName,$params=array()){
		$route = spyc_load_file(ROOT.'app/config/Routing.yml');
		foreach ($route as $key => $value) {
			$project = $route[$key]['project'];
			$linkP = $route[$key]['pattern'];
			$routeP = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');
			
			if(isset($routeP[$routeName])){
				$link = $routeP[$routeName];
				foreach ($params as $k => $v){
					$linkP = preg_replace('#\{'.$k.'\}#', $v, $linkP);
					$link['pattern'] = preg_replace('#\{'.$k.'\}#', $v, $link['pattern']);
				}
				if(strpos($link['pattern'], "{_lang}") !== false){
					$link['pattern'] = preg_replace('#\{_lang\}#', $_SESSION['lang'], $link['pattern']);
				}
				if(strpos($linkP, "{_lang}") !== false){
					$linkP = preg_replace('#\{_lang\}#', $_SESSION['lang'], $linkP);
				}
				$patternEx = explode(':', $link['controller']);
				$link = preg_replace('/(\/+)/','/', trim($linkP,'/').'/'.trim($link['pattern'],'/'));
				$push = (isset($_COOKIE["push"]))?$_COOKIE["push"]:"";
				$opts = array('http'=>array('method'=>"POST",
				              'header'=>"Accept-language: fr\r\n"."Cookie: ".session_name()."=".session_id().";push=".$push."\r\n"));
				$context = stream_context_create($opts);
				session_write_close();
				
				$prev = ($_SERVER['REMOTE_ADDR'] != '::1')?"":"http://localhost";
				$json = file_get_contents($prev.WEBROOT.$link, false, $context);
				$obj = (array) json_decode($json);		

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
						"Output"	       =>	"",
						"Parametres"	   =>	"",
						"GET"			   =>	$_GET,
					),
				);
				$obj = array_merge($obj, $info);
				require (ROOT.'libs/template/twig/LoaderTemplate.php');
				require (ROOT.'libs/template/autoLoad.php');
				echo $twig->render('src/project/'.$patternEx[0].'/views/'.$patternEx[1].'/'.$patternEx[2].'.html.twig',json_decode(json_encode($obj), true));
			}
		}		
	}
/*				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $link);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_COOKIE, session_name()."=".session_id());
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
				$json = curl_exec($ch);
				curl_close($ch);*/

