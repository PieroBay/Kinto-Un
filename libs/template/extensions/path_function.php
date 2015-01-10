<?php
	function path($routeName,$params=array()){
		$route = spyc_load_file(ROOT.'src/ressources/config/routing.yml');
		$link = $route[$routeName];
		foreach ($params as $k => $v){
			$link['pattern'] = preg_replace('#\{'.$k.'\}#', $v, $link['pattern']);
		}
		if(strpos($link['pattern'], "{_lang}") !== false){
			$link['pattern'] = preg_replace('#\{_lang\}#', $_SESSION['lang'], $link['pattern']);
		}

		$link = WEBROOT.trim($link['pattern'],'/');
		return $link;
	}