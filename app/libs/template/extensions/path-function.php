<?php
function path($routeName,$params=array(),$alternative=false){
    $route = spyc_load_file(ROOT.'config/Routing.yml');
    if (array_key_exists('_lang', $params)) {
        $lang = $params['_lang'];
    }else{
        $lang  = $_SESSION['lang'];
    }
    if (strpos($routeName, '{') !== false) {
        $routeName = explode("{",$routeName)[0];
    }

    foreach ($route as $key => $value) {
        $project = $route[$key]['project'];
        $linkP   = (is_array($route[$key]['pattern']))?$route[$key]['pattern'][$lang]:$route[$key]['pattern'];
        $routeP  = spyc_load_file(ROOT.'src/project/'.$project.'/config/routing.yml');

        if(isset($routeP[$routeName])){
            $link = $routeP[$routeName];
            if(!$alternative){
                $linkPattern = (is_array($link['pattern']))?$link['pattern'][$lang]:$link['pattern'];
            }else{
                $linkPattern = $alternative[$lang];
            }

            $patternEx = explode('/', trim($linkPattern,'/'));
            foreach ($patternEx as $k => $v) {
                if(isset($v[0]) && $v[0] == "{" && $v[1] == "_" && $v != "{_lang}"){
                    $v = substr($v,1,-1);

                    if(!array_key_exists($v, $params)){
                        $params[$v] = "";
                    }
                }
            }

            foreach ($params as $k => $v){
                $linkP = preg_replace('#\{'.$k.'\}#', $v, $linkP);
                $linkPattern = preg_replace('#\{'.$k.'\}#', $v, $linkPattern);
            }
            if(strpos($linkPattern, "{_lang}") !== false){
                $linkPattern = preg_replace('#\{_lang\}#', $lang, $linkPattern);
            }
            if(strpos($linkP, "{_lang}") !== false){
                $linkP = preg_replace('#\{_lang\}#', $lang, $linkP);
            }

            $link = preg_replace('/(\/+)/','/', trim($linkP,'/').'/'.trim($linkPattern,'/'));
            return WEBROOT.trim($link,'/');
        }

    }
}