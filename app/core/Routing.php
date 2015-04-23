<?php
class Routing{

	static $route;
	static $params;

	public static function start($link,$setError){
		$routeP = spyc_load_file(ROOT.'app/config/Routing.yml');
		$linkEx = explode('/', trim($link,'/'));
		$lang = $_SESSION['lang'];

		foreach($routeP as $key => $val){
			$project = $routeP[$key]['project'];
			self::$route = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');
			$patternP = ($routeP[$key]['pattern'] == '/')? trim($routeP[$key]['pattern'], '/'):$routeP[$key]['pattern'];
			foreach(self::$route as $k => $v){
				$pattern = ($patternP != "" && self::$route[$k]['pattern'] == "/")? trim(self::$route[$k]['pattern'],'/'): self::$route[$k]['pattern'];
				$pattern = $patternP.$pattern;
				$controller = self::$route[$k]['controller'];
				$valP = $val['pattern'].$v['pattern'];
				$routeName = $k;
				$pattern_tmp = $pattern;
				$patternEx = explode('/', trim($pattern,'/'));
				foreach($patternEx as $key => $value){
					if($value == "{_lang}"){ # si langue dans pattern
						if(isset($linkEx[$key]) && (file_exists(ROOT.'src/ressources/translate/'.$linkEx[$key].'.yml') || $linkEx[$key] == $_SESSION['local'])){ # la langue est dans l'url
							$lang = $linkEx[$key];
							$_SESSION['lang'] = $lang;
						}else{
							$lang = $_SESSION['lang'];
							$pattern_tmp = ($valP == "/{_lang}")? preg_replace('#\{_lang\}#', '', $pattern_tmp): preg_replace('#\/\{_lang\}#', '', $pattern_tmp);
							$pattern_tmp = ($pattern_tmp == "")? '/' : $pattern_tmp ;			
						}
					}
				}

				$linkRegex = preg_replace('#{(\w+)}#', '(?P<${1}>([a-zA-Z0-9\-\_\+]+))', $pattern_tmp);
				$linkRegex = '/'.str_replace('/', '\/', $linkRegex).'/';

				if(preg_match($linkRegex, $link, $match)){
					if($match[0] == $link){
						$patternEx = explode('/', trim($pattern,'/'));
						$output = (isset($_GET['output']) && $_GET['output'] == "xml")? true : false;
						$para = explode(':', $controller);

						self::$params = array(
							'routeName'      => $routeName,
							'controllerLink' => $controller,
							'pattern'        => $pattern,
							'project'        => $para[0],
							'controller'     => $para[1],
							'action'         => $para[2],
							'parametres'     => "",
							'output'		 => $output,
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
		}
		if(empty(self::$params['action'])){
			$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
		}

		return self::$params;
	}
}