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
            list($dom,$path, $ext) = explode(".", $link);
			echo $dom.".".$path."-".$size.".".$ext;
		}else{
			echo $link;
		}
	}