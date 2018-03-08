<?php

	/**
	 * Check if file translate exist
	 *
	 * @return void
	 */
	function fileExist(){ 
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

	/**
	 * Translate String
	 *
	 * @param String $tmp
	 * @return void
	 */
	function trans($tmp){
		if($_SESSION['lang'] != $_SESSION['local']){
			$traduction = array_change_key_case(fileExist(), CASE_LOWER);
			return $traduction[strtolower($tmp)];
		}else{
			return $tmp;
		}
	}