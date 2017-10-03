<?php
	$configFile = spyc_load_file(ROOT.'config/Config.yml');
	$template     = $configFile['configuration']['template'];

	$dossier = opendir(APP.'libs/template/extensions/');
	while(false !== ($fichier = readdir($dossier))){
		if($fichier != '.' && $fichier != '..' && $fichier != 'index.php' && $fichier != '.DS_Store' && $fichier != '.htaccess'){
			$nomExtension = explode('.php', $fichier)[0];
			$type = explode('-', $nomExtension);
			require_once APP.'libs/template/extensions/'.$fichier;
			if($type[1] == "function"){
				switch (strtolower($template)){
				    case "twig":
				    	$function = new Twig_SimpleFunction($type[0], $type[0]);
						$twig->addFunction($function);
				        break;
				    case "smarty":
						$smarty->registerPlugin('modifier',$type[0], $type[0]);
				        break;
				}
			}elseif ($type[1] == "filter") {
				switch (strtolower($template)){
				    case "twig":
				    	$filter = new Twig_SimpleFilter($type[0], $type[0]);
				    	$twig->addFilter($filter);
				        break;
				    case "smarty":
						$smarty->registerPlugin('modifier',$type[0], $type[0]);
				        break;
				}
			}
		}
	}
	closedir($dossier);