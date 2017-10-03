<?php

	namespace KintoUn\libs;

class Debug{

	/**
	 * Return print_r with style
	 *
	 * @param array $debugArray
	 * @return void
	 */
	static function Show($debugArray = array()){
		echo "<pre style=\"color:#f1c40f;position:relative;z-index:333;background:#e74c3c;margin:1px;padding:5px;border-radius:2px\">";
		echo "<u>Debug ligne ".debug_backtrace()[0]["line"].'</u><br/><br/>';
		print_r($debugArray);
		echo "</pre>";	
	}
}