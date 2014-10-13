<?php
class errorController extends Controller{

	protected $table = array();

	public function errorAction(){
		$data = array(
			"message"=>"Salut !",
		);	

		return $data;
	}
}