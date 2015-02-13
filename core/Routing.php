<?php
class Routing{

	static $route;
	static $params;

	public static function start($link,$setError){
		self::$route = spyc_load_file(ROOT.'src/ressources/config/routing.yml');
		$linkEx = explode('/', trim($link,'/'));
		$lang = $_SESSION['lang'];
		foreach(self::$route as $k => $v){
			$pattern = self::$route[$k]['pattern'];
			$controller = self::$route[$k]['controller'];
			$routeName = $k;
			$pattern_tmp = $pattern;
			$patternEx = explode('/', trim($pattern,'/'));
			foreach($patternEx as $key => $value){
				if($value == "{_lang}"){
					if(isset($linkEx[$key]) && (file_exists(ROOT.'src/ressources/translate/'.$linkEx[$key].'.yml') || $linkEx[$key] == $_SESSION['local'])){ # la langue est dans l'url
						$lang = $linkEx[$key];
						$_SESSION['lang'] = $lang;
					}else{
						$lang = $_SESSION['lang'];
						$pattern_tmp = preg_replace('#\/\{_lang\}#', '', $pattern_tmp);
					}
				}
			}

			$linkRegex = preg_replace('#{(\w+)}#', '(?P<${1}>([a-zA-Z0-9\-\_\+]+))', $pattern_tmp);
			$linkRegex = '/'.str_replace('/', '\/', $linkRegex).'/';

			if(preg_match($linkRegex, $link, $match)){
				if($match[0] == $link){
					$patternEx = explode('/', trim($pattern,'/'));

					$para = explode(':', $controller);
					self::$params = array(
						'routeName'      => $routeName,
						'controllerLink' => $controller,
						'pattern'        => $pattern,
						'project'        => $para[0],
						'controller'     => $para[1],
						'action'         => $para[2],
						'parametres'     => "",
						'lang'			 => $lang,
					);	
					foreach ($match as $k => $v) {
						if(is_numeric($k)){
							unset($match[$k]);
							unset($match["_lang"]);
							self::$params['parametres'] = $match;
						}
					}
				}
			}
		}

		if(empty(self::$params['action'])){
			$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
		}
		return self::$params;
	}
}