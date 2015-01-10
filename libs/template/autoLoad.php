<?php
	$template = $this->info['Info']['Template'];

	$dossier = opendir(ROOT.'libs/template/extensions/');
	while(false !== ($fichier = readdir($dossier))){
		if($fichier != '.' && $fichier != '..' && $fichier != 'index.php' && $fichier != '.DS_Store' && $fichier != '.htaccess'){
			$nomExtension = explode('.php', $fichier)[0];
			$type = explode('_', $nomExtension);
			require ROOT.'libs/template/extensions/'.$fichier;
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