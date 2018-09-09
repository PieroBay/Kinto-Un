<?php

	/**
	 * Retourne la taille de l'image
	 *
	 * @param String $url
	 * @return void
	 */
	function imageSize($url){
        list($width, $height) = getimagesize($url); 
        $arr = array('h' => $height, 'w' => $width );
        $txt = $width."x".$height;

        echo $txt;
    }