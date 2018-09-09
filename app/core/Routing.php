<?php

namespace KintoUn\core;

use KintoUn\libs\Debug;

class Routing{
    static $params;
    static $pattern       = array();
    static $patternList   = array();
    static $patternListW  = array();
    static $parametreP    = array();
    static $patternP;
    static $langInUrl;
    static $setError;
    static $lang;

    /**
     * Check if lang in pattern and change self::$pattern
     *
     * @param array $linkEx [Explode of link]
     * @return void
     */
    public static function ifLang($linkEx){
        $ttt = "";
        foreach(self::$patternList as $key => $value){
            # si langue dans pattern
            if(strpos($key,"{_lang}") !== false){
                $explPatt = explode('/', trim($key,'/'));
                $lang     = $linkEx[array_search('{_lang}', $explPatt)];

                # la langue est dans l'url
                if(!empty($lang) && file_exists(ROOT.'src/ressources/translate/'.$lang.'.yml') || $lang == $_SESSION['local']){
                    self::$lang 	  = $lang;
                    $ttt = $lang;
                    self::$langInUrl = true;
                    $_SESSION['lang'] = $lang;
                    self::$patternList[preg_replace('#\{_lang\}#', $lang, $key)] = $value;
                }else{
                    self::$langInUrl = false;
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
        return $_SESSION['lang'];
    }

    public static function filtreRoutingArray($route){
        $routeFiltre = [];
        foreach($route as $k=>$v){
            if(is_array($v['pattern'])){
                foreach($v['pattern'] as $kp=>$vp){
                    $routeFiltre[$k."{".$kp."}"] = array(
                        "pattern"     => $vp,
                        "lang" 		  => $kp,
                        "controller"  => $v['controller'],
                        "alternative" => $v['pattern']
                    );
                }
            }else{
                $routeFiltre[$k] = $v;
            }
        }
        return $routeFiltre;
    }

    /**
     * Rooting start
     *
     * @param [type] $link
     * @param [type] $setError
     * @param boolean $verif
     * @param array $secondPatt
     * @return void
     */
    public static function start($link,$setError,$verif=false,$secondPatt=array()){
        $routeP          = spyc_load_file(ROOT.'config/Routing.yml');
        $routeP 	     = self::filtreRoutingArray($routeP);
        $linkEx          = explode('/', trim($link,'/'));
        self::$lang      = $_SESSION['lang'];
        $ct              = 0;
        $linkTrim        = trim($link,'/').'/';
        self::$setError  = $setError;
        $exis 			 = false;

        # parcoure le routage principal si première verif
        # Crée un array avec toutes les routes
        if(!$verif){
            foreach($routeP as $key => $val){
                $project  = $routeP[$key]['project'];
                $route    = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');

                $route    = self::filtreRoutingArray($route);
                $patternP = ($routeP[$key]['pattern'] == '/')? trim($routeP[$key]['pattern'], '/'):$routeP[$key]['pattern'];
                foreach($route as $k => $v){
                    $langArray = (isset($v['lang']))?$v['lang']:'';
                    $alternativeArray = (isset($v['alternative']))?$v['alternative']:'';
                    self::$patternList[trim($patternP.$v['pattern'],'/').'/'] = array($v["controller"],$k,$langArray,$alternativeArray);
                }

            }
        }else{
            self::$patternList = $secondPatt;
        }

        # Check si lang dans pattern et change self::$pattern
        # Si premier passage, vire les parametres optionnels
        if(!$verif){
            self::ifLang($linkEx);

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
     *
     * @param string $link
     * @param [type] $verif
     * @return void
     */
    public static function finals($link,$verif){
        $matchList = array();

        $patL = (!$verif)?self::$patternListW:self::$patternList;
        foreach ($patL as $k => $v){
            $linkRegex = '/'.str_replace('/', '\/', preg_replace('#{(\w+)}#', '(?P<${1}>([a-zA-Z0-9\-\_\+\.\@]+))', $k)).'/';

            if(preg_match($linkRegex, $link, $match)){
                $t = array_filter(explode("/",$k));
                $s = array_filter(explode("/",$link));

                if(count($t) == count($s)){
                    self::$patternP[$linkRegex] = array($v,$k);
                    $nP  = (!isset(self::$patternP[$linkRegex][0][2]))?$k:self::$patternP[$linkRegex][0][2];
                    $arM = array("pattern"=>$nP,"match"=>$match);
                    $matchList[] = $arM;
                    if($k == $link){break;}
                }
            }
        }

        # Si il y a des paramètres, on les ajoutes dans le tableau
        if(!empty($matchList) && count($matchList) > 0){
            foreach ($matchList as $k => $v) {
                $count[] = array_map('count', $v)["match"];
            }

            # Supprimer l'item avec le plus de clé dans self::$patternP
            if(count(self::$patternP) > 1){
                $i = 0;
                foreach (self::$patternP as $key => $value) {
                    if($i == array_keys($count, max($count))[0]){
                        unset(self::$patternP[$key]);
                    }
                    $i++;
                }
            }

            foreach ($matchList as $k => $v) {
                $min   = $matchList[array_keys($count, min($count))[0]]["match"];
                foreach($min as $key => $val){
                    if(is_string($key)){
                        self::$parametreP[$key]=$val;
                    }
                }
            }
        }

        # Si premier passage, et si il y a des parametres facultatif, les ajouter dans les parametres avec une valeur vide.
        # Ne passe jamais aux 2eme passage, l'index 2 ne posera donc pas de problème car l'index 2 n'existe pas au 2eme passage.
        if(!$verif){
            if(isset(self::$patternP)){
                $k = array_keys(self::$patternP)[0];
                if(strpos(self::$patternP[$k][0][2],"{_") !== false){
                    preg_match('#\{_(.*?)\}#', self::$patternP[$k][0][2], $match);
                    self::$parametreP["_".$match[1]] = "";
                }
            }
        }

        # Si il en trouve plusieurs, tu prends celui sans parametres
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


            self::$params = array(
                'routeName'        => $value[0][1],
                'controllerLink'   => $value[0][0],
                'routeLang' 	   => $value[0][2],
                'routeAlternative' => $value[0][3],
                'pattern'          => $value[1],
                'project'          => $param[0],
                'controller'       => $param[1],
                'action'           => $param[2],
                'parametres'       => "",
                'output'		   => $output,
                'lang'			   => self::$lang,
            );

            self::redirectToCorrectlang();

            self::$params['parametres'] = self::$parametreP;
        }

        if(empty(self::$params['action'])){
            self::$setError->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
        }

        return self::$params;
    }

    static public function redirectToCorrectLang(){
        if(self::$params['lang'] != self::$params['routeLang'] && self::$langInUrl==true){
            $RootCorrectLang  		   = self::$params['lang'];
            $RootIncorrectLang  	   = self::$params['routeLang'];
            $AlternativeCorrectParam   = self::$params['routeAlternative'][$RootCorrectLang];
            $AlternativeIncorrectParam = self::$params['routeAlternative'][$RootIncorrectLang];

            $newPattern = str_replace($AlternativeIncorrectParam,$AlternativeCorrectParam,self::$params['pattern']);

            $newLink = WEBROOT.$newPattern;
            header("Location: ".$newLink);
        }

        if(self::$langInUrl == false){
            self::$params['lang'] = self::$params['routeLang'];
            $_SESSION['lang'] = self::$params['routeLang'];
        }
    }
}