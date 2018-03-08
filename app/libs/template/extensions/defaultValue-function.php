<?php
	/**
	 * Smart default value
	 *
	 * @param String $value
	 * @param String $default
	 * @return void
	 */
	function defaultValue($value,$default){
		if($value){
			echo $value;
		}else{
			echo $default;
		}
	}