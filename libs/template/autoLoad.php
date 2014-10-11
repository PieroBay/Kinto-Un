<?php
	$template = $this->info['Info']['Template'];

	$dossier = opendir(ROOT.'libs/template/extensions/');
	while(false !== ($fichier = readdir($dossier))){
		if($fichier != '.' && $fichier != '..' && $fichier != 'index.php' && $fichier != '.DS_Store' && $fichier != '.htaccess'){
			$nomExtension = explode('.php', $fichier)[0];
			require ROOT.'libs/template/extensions/'.$fichier;

			switch (strtolower($template)){
			    case "twig":
			    	$filter = new Twig_SimpleFilter($nomExtension, $nomExtension);
			    	$twig->addFilter($filter);
			        break;
			    case "smarty":
					$smarty->registerPlugin('modifier',$nomExtension, $nomExtension);
			        break;
			}
		}
	}
	closedir($dossier);