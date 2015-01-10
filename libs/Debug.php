<?php
class Debug{
	static function Show($t = array()){
		echo "<pre style=\"color:#f1c40f;background:#e74c3c;margin:1px;padding:5px;border-radius:2px\">";
		echo "<u>Debug ligne ".debug_backtrace()[0]["line"].'</u><br/><br/>';
		print_r($t);
		echo "</pre>";	
	}
}