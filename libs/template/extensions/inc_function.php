<?php
	function inc($routeName,$params=array()){
		$route = spyc_load_file(ROOT.'app/config/routing.yml');
		
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

				$link = WEBROOT.trim($linkP,'/').'/'.trim($link['pattern'],'/');			
				echo file_get_contents('http://'.$_SERVER['HTTP_HOST'].$link);
			}
		}		
	}