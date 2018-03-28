<?php
	/**
	 * return picture link with correct size
	 *
	 * @param String $link
	 * @param String $size
	 * @return String
	 */
	function picture($link,$size=false){
		if($size && $size != "o"){
            list($path, $ext) = explode(".", $link);
			echo $path."-".$size.".".$ext;
		}else{
			echo $link;
		}
	}