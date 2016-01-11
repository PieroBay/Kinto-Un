<?php
class Request{

	public static $_PUT = array();

	public static function POST($data=null){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			$_POST = (!empty($_POST))?$_POST:json_decode(file_get_contents("php://input"), true);
			return true;
		}
	}
	public static function GET($data=null){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			$_POST = (!empty($_POST))?$_POST:json_decode(file_get_contents("php://input"), true);
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
			$_POST = (!empty($_POST))?$_POST:json_decode(file_get_contents("php://input"), true);
			return true;
		}
	}
	public static function DELETE(){
		if($_SERVER['REQUEST_METHOD'] == __FUNCTION__ && preg_match('#application/json#', $_SERVER['HTTP_ACCEPT'])){
			$_POST = (!empty($_POST))?$_POST:json_decode(file_get_contents("php://input"), true);
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

	public static function headerResponse($code){
		header('HTTP/1.1 '.$code);
	}

	public static function renderXml($data=array(),$unset=null,$rename=null){
		$d  = array();

		if(count($data) == 1){
			$tmp_d[] = $data;
			$data    = $tmp_d;
		}

		$data = json_decode(json_encode($data), true);

		foreach ($data as $k => $v){
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

			$d[] = $data[$k];
		}

		header('Content-Type: application/xml');
		$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><items></items>");
		self::array_to_xml($d,$xml);
		exit($xml->asXML());
	}

	public static function array_to_xml($array, &$xml) {
	    foreach($array as $key => $value){
	        if(is_array($value)){
	            if(!is_numeric($key)){
	                $subnode = $xml->addChild("$key");
	                self::array_to_xml($value, $subnode);
	            }else{
	                $subnode = $xml->addChild("item");
	                self::array_to_xml($value, $subnode);
	            }
	        }else{
	            $xml->addChild("$key",htmlspecialchars("$value"));
	        }
	    }
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

	public static function getContent($link){
		$opts = array('http'=>array('method'=>"GET",
		              'header'=>"Accept: application/json"));

		$context = stream_context_create($opts);
		session_write_close();

		$json = json_decode(file_get_contents($link, false, $context));
		return $json;
	}
}