<?php
class Request{

	public static $_PUT = array();

	public static function POST($data=null){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			return true;
		}
	}
	public static function GET($data=null){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			if(!isset($data)){
				return true;
			}elseif($data){
				header('Content-Type: application/json');
				exit(json_encode($data));				
			}else{
				header('Content-Type: application/json');
				exit(json_encode(null));	
			}
		}
	}
	public static function PUT(){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			return true;
		}
	}
	public static function DELETE(){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			return true;
		}
	}

	public static function parsePutReq($params){
		if($_SERVER['REQUEST_METHOD'] == "PUT"){
			$rid = $params['id'];
			$input = file_get_contents('php://input');

			preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
			$boundary = $matches[1];

			$a_blocks = preg_split("/-+$boundary/", $input);
			array_pop($a_blocks);

			foreach ($a_blocks as $id => $block){
				if (empty($block))
					continue;
				if (strpos($block, 'application/octet-stream') !== FALSE){
					preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
				}
				else{
					preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				}
				self::$_PUT[$matches[1]] = $matches[2];
			}
			self::$_PUT['id'] = $rid;
		}
	}
}