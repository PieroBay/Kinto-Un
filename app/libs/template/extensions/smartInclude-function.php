<?php
	/**
	 * Include spliting files from project if exists
	 *
	 * @param string $Info [Info var with Ressources,controllers and actions name]
	 * @param string $type [si c'est du css ou du js à loader]
	 * @return void
	 */
	function smartInclude($Info,$type){

		$file = "";

		if($type == "css"){
			if(file_exists($Info['Ressources'].'css/global.css')){
				$file .= '<link rel="stylesheet" href="'.$Info['Ressources'].'css/global.css" />';
			}
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Project'].'.css')){
				$file .= '<link rel="stylesheet" href="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Project'].'.css" />';
			}			
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.css')){
				$file .= '<link rel="stylesheet" href="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.css" />';
			}
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.'.$Info['Action'].'.css')){
				$file .= '<link rel="stylesheet" href="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/css/'.$Info['Controller'].'.'.$Info['Action'].'.css" />';
			}
		}elseif($type == 'js'){
			if(file_exists($Info['Ressources'].'js/global.js')){
				$file .= '<script type="text/javascript" src="'.$Info['Ressources'].'js/global.js"></script>';
			}
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Project'].'.js')){
				$file .= '<script type="text/javascript" src="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Project'].'.js"></script>';
			}			
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Controller'].'.js')){
				$file .= '<script type="text/javascript" src="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Controller'].'.js"></script>';
			}
			if(file_exists($Info['Root'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Controller'].'.'.$Info['Action'].'.js')){
				$file .= '<script type="text/javascript" src="'.$Info['Webroot'].'src/project/'.$Info['Project'].'/inc/js/dist/'.$Info['Controller'].'.'.$Info['Action'].'.js"></script>';
			}
		}

		echo $file;
	}