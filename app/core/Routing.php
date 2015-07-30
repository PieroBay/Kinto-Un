<?php
class Routing{

	static $route;
	static $params;

	public static function start($link,$setError,$verif=false){
		$routeP = spyc_load_file(ROOT.'app/config/Routing.yml');
		$linkEx = explode('/', trim($link,'/'));
		$lang = $_SESSION['lang'];
		$ct = 0;

		# parcoure le routage principal
		foreach($routeP as $key => $val){
			$project = $routeP[$key]['project'];
			self::$route = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');
			$patternP = ($routeP[$key]['pattern'] == '/')? trim($routeP[$key]['pattern'], '/'):$routeP[$key]['pattern'];
			
			# parcoure les routages de chaques projets
			foreach(self::$route as $k => $v){
				$pattern = ($patternP != "" && self::$route[$k]['pattern'] == "/")? trim(self::$route[$k]['pattern'],'/'): self::$route[$k]['pattern'];
				$pattern = $patternP.$pattern;
				$controller = self::$route[$k]['controller'];
				$valP = $val['pattern'].$v['pattern']; # pattern parent + enfant
				$routeName = $k;
				$pattern_tmp = $pattern;
				$patternEx = explode('/', trim($pattern,'/'));

				/* VERIF LANG + VERIF UNDER */
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

					# Si deuxième passage
					if($verif){
						# Si contient un underscore et différent de lang
						if(isset($value[1]) && $value[1] == "_" && $value != "{_lang}"){
							unset($patternEx[$key]);
							$pattern_tmp = str_replace("/".$value, "", $pattern_tmp);
						}						
					}
				}
				/* END */

				$linkRegex = preg_replace('#{(\w+)}#', '(?P<${1}>([a-zA-Z0-9\-\_\+]+))', $pattern_tmp);
				$linkRegex = '/'.str_replace('/', '\/', $linkRegex).'/';

				if(preg_match($linkRegex, $link, $match)){
					if($match[0] == $link){
						$ct++;
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

						$parametreP = array();
						foreach ($match as $k => $v) {
							if(is_string($k)){
								if($k[0] == "_"){
									$parametreP[$k]=$match[$k];
								}else{
									$parametreP[$k]=$match[$k];
								}
							}
						}
						self::$params['parametres'] = $parametreP;
					}
				}
			}
		}

		# Si premier passage = 0 et verif à false -> restart
		# Sinon tu retournes les parametres
		if($ct < 1 && !$verif){
			self::start($link,$setError,true);
		}else{
			return self::verif($setError);
		}
	}

	public static function verif($setError){
		if(empty(self::$params['action'])){
			$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
		}
		return self::$params;
	}
}