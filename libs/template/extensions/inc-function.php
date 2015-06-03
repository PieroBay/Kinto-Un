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
				
				$opts = array('http'=>array('method'=>"POST",
				              'header'=>"Accept-language: fr\r\n"."Cookie: ".session_name()."=".session_id()."\r\n"));

				$context = stream_context_create($opts);
				session_write_close();
				
				$prev = ($_SERVER['REMOTE_ADDR'] != '::1')?"":"http://localhost";

				$json = file_get_contents($prev.WEBROOT.$link, false, $context);
				$obj = (array) json_decode($json);		
		
				require(ROOT.'libs/template/twig/LoaderTemplate.php');
				require (ROOT.'libs/template/autoLoad.php');
				echo $twig->render('src/project/'.$patternEx[0].'/views/'.$patternEx[1].'/'.$patternEx[2].'.html.twig',$obj);
			}
		}		
	}