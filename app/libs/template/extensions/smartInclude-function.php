<?php
	/**
	 * Include spliting files from project if exists
	 *
	 * @param String $Info [Info var with Ressources,controllers and actions name]
	 * @return void
	 */
	function smartInclude($Info){

		$file = "";
		if(file_exists($Info['Ressources'].'css/global.css')){
			$file .= '<link rel="stylesheet" href="'.$Info['Ressources'].'css/global.css" />';
		}
		if(file_exists($Info['Ressources'].'js/global.js')){
			$file .= '<script type="text/javascript" src="'.$Info['Ressources'].'js/global.js"></script>';
		}
		if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.css')){
			$file .= '<link rel="stylesheet" href="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.css" />';
		}
		if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.'.$Info['Action'].'.css')){
			$file .= '<link rel="stylesheet" href="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.'.$Info['Action'].'.css" />';
		}
		if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/js/'.$Info['Controller'].'.js')){
			$file .= '<script type="text/javascript" src="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/js/'.$Info['Controller'].'.js"></script>';
		}
		if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/js/'.$Info['Controller'].'.'.$Info['Action'].'.js')){
			$file .= '<script type="text/javascript" src="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/js/'.$Info['Controller'].'.'.$Info['Action'].'.js"></script>';
		}
		
		echo $file;
	}