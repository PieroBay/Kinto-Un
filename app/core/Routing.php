<?php
class Routing{
	static $params;
	static $pattern       = array();
	static $patternList   = array();
	static $patternListW  = array();
	static $parametreP    = array();
	static $deletedParams = array();
	static $patternP;
	static $setError;
	static $lang;


	/**
	 * Check if lang in pattern and change self::$pattern
	 * @param  Array: $linkEx: Explode of link
	 * @param  Bool:  $verif:  ...
	 */
	public static function ifLang($linkEx,$verif){
		foreach(self::$patternList as $key => $value){
			# si langue dans pattern
			if(strpos($key,"{_lang}") !== false && !$verif){
				$explPatt = explode('/', trim($key,'/'));
				$lang     = $linkEx[array_search('{_lang}', $explPatt)];

				# la langue est dans l'url
				if(!empty($lang) && file_exists(ROOT.'src/ressources/translate/'.$lang.'.yml') || $lang == $_SESSION['local']){
					self::$lang 	  = $lang;
					$_SESSION['lang'] = $lang;
					self::$patternList[preg_replace('#\{_lang\}#', $lang, $key)] = $value;
				}else{
					self::$lang  = $_SESSION['lang'];
					if($key == "{_lang}/"){
						self::$patternList[preg_replace('#\{_lang\}#', '', $key)] = $value;
						unset(self::$patternList[$key]);
					}else{
						self::$patternList[preg_replace('#\{_lang\}/#', '', $key)] = $value;
						unset(self::$patternList[$key]);
					}
				}
			}
		}
	}

	public static function start($link,$setError,$verif=false,$secondPatt=array()){
		$routeP          = spyc_load_file(ROOT.'app/config/Routing.yml');
		$linkEx          = explode('/', trim($link,'/'));
		self::$lang      = $_SESSION['lang'];
		$ct              = 0;
		$linkTrim        = trim($link,'/').'/';
		self::$setError  = $setError;
		$exis 			 = false;

		# parcoure le routage principal si première verif
		# Crée un array avec toute les routes
		if(!$verif){
			foreach($routeP as $key => $val){
				$project  = $routeP[$key]['project'];
				$route    = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');
				$patternP = ($routeP[$key]['pattern'] == '/')? trim($routeP[$key]['pattern'], '/'):$routeP[$key]['pattern'];
				
				foreach($route as $k => $v){
					self::$patternList[trim($patternP.$v["pattern"],'/').'/'] = array($v["controller"],$k);
				}
			}
		}else{
			self::$patternList = $secondPatt;
		}

		# Check si lang dans pattern et change self::$pattern
		# Si 2ème passage, remplace les '_'
		self::ifLang($linkEx,$verif);

		# Si premier passage, vire les parametres optionel
		if(!$verif){
			foreach (self::$patternList as $key => $value) {
				$nv = preg_replace('/'.preg_quote('{_').'.*?'.preg_quote('}').'/','', $key);
				$nv = str_replace("//", "/", $nv);
				self::$patternListW[$nv] = $value;
				self::$patternListW[$nv][] = $key;
			}
		}
		
		self::finals($linkTrim,$verif);
	}

	/**
	 * Verif and find the correct pattern
	 * @param  String: $link "Current link"
	 * @return   Json
	 */
	public static function finals($link,$verif){
		$matchList = array();

		$patL = (!$verif)?self::$patternListW:self::$patternList;
		foreach ($patL as $k => $v){
			$linkRegex = '/'.str_replace('/', '\/', preg_replace('#{(\w+)}#', '(?P<${1}>([a-zA-Z0-9\-\_\+]+))', $k)).'/';
			if(preg_match($linkRegex, $link, $match)){
				$t = explode("/",$k);
				$s = explode("/",$link);
				if(count($t) == count($s)){
					if($match[0] == $link){
						self::$patternP[$linkRegex] = array($v,$k);
					}
					$matchList[] = $match;
				}
			}
		}

		# Si il y a des paramètres, on les ajoutes dans le tableau
		if(!empty($matchList)){
			$count = array_map('count', $matchList);
			$min   = $matchList[array_keys($count, min($count))[0]];

			foreach($min as $key => $val){
				if(is_string($key) && $key != "_lang"){
					self::$parametreP[$key]=$val;
				}
			}
		}
		if(!$verif){
			if(isset(self::$patternP)){
				$k = array_keys(self::$patternP)[0];

				if(strpos(self::$patternP[$k][0][2],"{_") !== false){
					preg_match('#\{_(.*?)\}#', self::$patternP[$k][0][2], $match);
					self::$parametreP[$match[1]] = "";
				}				
			}
		}

		# Si il en trouve plusieur, tu prends celui sans parametres
		if(count(self::$patternP) > 1){
			foreach (self::$patternP as $key => $value){
				if(strpos($value[1],"{") === false){
					self::$patternP = array($value);
				}
			}
		}elseif(empty(self::$patternP)){
			if($verif){
				self::$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
			}else{
				self::start($link,self::$setError,true,self::$patternList);
			}
		}

		foreach (self::$patternP as $key => $value){
			$output = (isset($_GET['output']) && $_GET['output'] == "xml")? true : false;
			$param  = explode(':', $value[0][0]);

			if(isset(self::$deletedParams[$value[1]])){
				foreach(self::$deletedParams[$value[1]] as $k => $v){
					$v = str_replace(array( '{', '}' ), '', $v);
					self::$parametreP[$v] = "";
				}				
			}

			self::$params = array(
				'routeName'      => $value[0][1],
				'controllerLink' => $value[0][0],
				'pattern'        => $value[1],
				'project'        => $param[0],
				'controller'     => $param[1],
				'action'         => $param[2],
				'parametres'     => "",
				'output'		 => $output,
				'lang'			 => self::$lang,
			);

			self::$params['parametres'] = self::$parametreP;
		}

		if(empty(self::$params['action'])){
			self::$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
		}

		return self::$params;
	}
}