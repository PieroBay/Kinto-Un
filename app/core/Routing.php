<?php
class Routing{
	static $params;
	static $pattern       = array();
	static $patternList   = array();
	static $parametreP    = array();
	static $deletedParams = array();
	static $patternP;
	static $setError;
	static $configYml;
	static $lang;


	/**
	 * Check if lang in pattern and change self::$pattern
	 * @param  Array: $linkEx: Explode of link
	 * @param   Bool:  $verif:  ...
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
			# Si deuxième passage
			if($verif){
				# Si contient un underscore et différent de lang
				$e = explode("/",$key);
				foreach ($e as $k => $v) {
					if(strpos($v,"{_") !== false && $v != "{_lang}"){
						$kj = str_replace($v."/", "", $key);
						$kj = trim($kj,"/")."/";
						self::$patternList[$kj] = $value;
						self::$deletedParams[$kj][] = $v;
						unset(self::$patternList[$key]);
					}
				}
			}
		}
	}

	public static function start($link,$setError,$configYml,$verif=false,$secondPatt=array()){
		$routeP          = spyc_load_file(ROOT.'app/config/Routing.yml');
		$linkEx          = explode('/', trim($link,'/'));
		self::$lang      = $_SESSION['lang'];
		$ct              = 0;
		$linkTrim        = trim($link,'/').'/';
		self::$setError  = $setError;
		self::$configYml = $configYml;
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

		self::ifLang($linkEx,$verif);
		if(isset(self::$patternList[$linkTrim]) && $verif == false){ // Si pas de paramètres
			self::quick($linkTrim, self::$patternList[$linkTrim]);
			$exis = true;
		}
		if(!$exis){
			self::finals($linkTrim);			
		}
	}

	/**
	 * Verif and find the correct pattern
	 * @param  String: $link "Current link"
	 * @return   Json
	 */
	public static function finals($link){
		$matchList = array();

		foreach (self::$patternList as $k => $v){
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

		# Si il en trouve plusieur, tu prends celui sans parametres
		if(count(self::$patternP) > 1){
			foreach (self::$patternP as $key => $value){
				if(strpos($value[1],"{") === false){
					self::$patternP = array($value);
				}
			}
		}elseif(empty(self::$patternP)){
			self::start($link,self::$setError,self::$configYml,true,self::$patternList);
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

	/**
	 * Quick return if not params in pattern
	 * @param  String: $link   Current link
	 * @param   Array: $params Route info
	 * @return   Json:         Route params
	 */
	public static function quick($link,$params){
		$output = (isset($_GET['output']) && $_GET['output'] == "xml")? true : false;
		$param  = explode(':', $params[0]);

		self::$params = array(
			'routeName'      => $params[1],
			'controllerLink' => $link,
			'pattern'        => $link,
			'project'        => $param[0],
			'controller'     => $param[1],
			'action'         => $param[2],
			'parametres'     => array(),
			'output'		 => $output,
			'lang'			 => self::$lang,
		);

		return self::$params;
	}
}