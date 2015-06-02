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

	public static function renderJson($data=array()){
		header('Content-Type: application/json');
		exit(json_encode($data));
	}

	public static function renderXml($data=array(),$unset=null,$rename=null){
		$d = array();
		foreach ($data as $k => $v) {
			$data[$k] = (array)$data[$k];

			if(isset($unset) && is_array($unset)){
				foreach ($unset as $key) {
					unset($data[$k][$key]);
				}
			}
			if(isset($rename) && is_array($rename)){
				foreach ($rename as $key => $value) {
					$data[$k][$value] = $data[$k][$key];
					unset($data[$k][$key]);
				}
			}

			$d[] = array_flip($data[$k]);
		}

		header('Content-Type: application/xml');
		$xml = new SimpleXMLElement('<items/>');
		
		foreach ($d as $key => $v) {
			$node = $xml->addChild('item');
			array_walk_recursive($v, array ($node, 'addChild'));
		}
		
		exit($xml->asXML());
	}

	public static function isPost(){
		if(!isset($_POST['update'])){
			if(isset($_POST['id'])){ unset($_POST['id']); }
			return true;
		}else{
			return false;
		}
	}

	public static function isPut(){
		if(isset($_POST['update'])){
			if(isset($_POST['id'])){ unset($_POST['id']); }
			return true;
		}else{
			return false;
		}
	}
}