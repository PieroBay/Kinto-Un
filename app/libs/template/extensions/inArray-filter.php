<?php

	/**
	 * Find if value exist in multi-dimensionnel array
	 *
	 * @param Array $array
	 * @param String|int $key
	 * @param String|int $value
	 * @return bool
	 */
	function inArray($array,$key,$value){
		if(isset($array)){
			foreach ($array as $k => $v){
				$v = (array) $v;
				if($v[$key] === $value){
					return true;
				}
			}
		}
	}