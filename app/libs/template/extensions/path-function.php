<?php
	function path($routeName,$params=array()){
		$route = spyc_load_file(ROOT.'config/Routing.yml');

		foreach ($route as $key => $value) {
			$project = $route[$key]['project'];
			$linkP = $route[$key]['pattern'];
			$routeP = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');

			if(isset($routeP[$routeName])){
				$link = $routeP[$routeName];

				$patternEx = explode('/', trim($link['pattern'],'/'));
				foreach ($patternEx as $k => $v) {
					if(isset($v[0]) && $v[0] == "{" && $v[1] == "_" && $v != "{_lang}"){
						$v = substr($v,1,-1);
						
						if(!array_key_exists($v, $params)){
							$params[$v] = "";
						}
					}
				}
				
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

				$link = preg_replace('/(\/+)/','/', trim($linkP,'/').'/'.trim($link['pattern'],'/'));
				return WEBROOT.trim($link,'/');				
			}

		}		
	}