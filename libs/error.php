<?php
class Error{
	public function generate($number = '404', $message){
		$erreur = array(
			"Error"	=>	array(
				"Number"  => $number,
				"Message" => $message,
			),
			"Info" => array(
				"Webroot"	=> WEBROOT,
			)
		);

		require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
		echo $twig->render('core/errors/error.html.twig',$erreur);
		exit();
	}
}