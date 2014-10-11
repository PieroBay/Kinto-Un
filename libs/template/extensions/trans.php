<?php
	function fileExiste(){ 
		if($_SESSION['lang'] != $_SESSION['local']){
			$link = ROOT.'src/ressources/translate/'.$_SESSION['lang'].'.yml';
			if(file_exists($link)){
				$lang = spyc_load_file($link);
				return $lang;
			}else{
				echo "Aucun fichier de traduction disponible.";
				exit();
			}
		}
	}

	function trans($tmp){
		if($_SESSION['lang'] != $_SESSION['local']){
			$traduction = array_change_key_case(fileExiste(), CASE_LOWER);
			return $traduction[strtolower($tmp)];
		}else{
			return $tmp;
		}
	}