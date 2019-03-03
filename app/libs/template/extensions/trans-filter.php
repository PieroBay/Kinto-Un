<?php
/**
 * If cachefile exist, get from cache
 * else create a new cache
 *
 * @param $lang
 * @return mixed
 */
function getLangFromCache($lang){
    $cacheFile = isset($_GET['refresh_trad'])?false:file_get_contents("cache_lang_".$lang);
    if($cacheFile){
        return unserialize($cacheFile);
    }else{
        return setLangToCache($lang);
    }
}

/**
 * Lower toutes les key du tableau
 * @param array $arr
 * @return array
 */
function array_change_key_case_unicode($arr) {
    foreach ($arr as $k => $v) {
        $ret[mb_convert_case($k, MB_CASE_LOWER, "UTF-8")] = $v;
    }
    return $ret;
}

/**
 * Affiche ou non le lien vers la trad
 * @param int $idTrad
 * @param boolean $showTradOnlyString
 * @return sring
 */
function showLinkToTrad($idTrad,$showTradOnlyString=false){
    if(isset($_GET['show_trad'])){
        if($idTrad){
            if($showTradOnlyString){
                echo "[ID: ".$idTrad."]";
            }else{
                //echo "<a target='_blank' href=''>[T]</a>";
            }
        }else{
            echo "[X]";
        }
    }else{
        echo "";
    }
}
/**
 * Connection to db
 *
 * @param array $configFile
 * @return array
 */
function dbConnect($configFile){
    $dsn = 'mysql:host='.$configFile['database_host'].';dbname='.$configFile['database_name'];
    try{
        $bdd = new \PDO($dsn, $configFile['database_user'], $configFile['database_password']);
        $bdd->exec('SET NAMES utf8');
        $bdd->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){ echo 'Échec lors de la connexion : ' . $e->getMessage(); }
    return $bdd;
}
/**
 * Set lang file into cache
 * @param $lang
 * @return mixed
 */
function setLangToCache($lang){
    $configFile = spyc_load_file(ROOT.'config/Config.yml');
    if($configFile['translate']['type'] == "db") {
        $bdd = dbConnect($configFile['configuration']);
        $sql = "SELECT id,model,".$lang." FROM ".$configFile['translate']['dbTable'];
        $req = $bdd->prepare($sql);
        $req->execute();
        $arr = array();
        while($data = $req->fetch(\PDO::FETCH_OBJ)){
            $arr['langs'][$data->model] = array("id"=>$data->id,"trad"=>$data->{$lang});
        }
    }else{
        $link = "";
        if(file_exists($link)){
            $arr = json_decode(file_get_contents($link),true);
        }
    }
    file_put_contents("cache_lang_".$lang, serialize($arr));
    return $arr;
}

/**
 * Translate String
 *
 * @param String $tmp
 * @param array $valeur
 * @param boolean $showTradOnlyString
 * @return void
 */
function trans($tmp,$valeur=false,$showTradOnlyString=false){
    $dataTrad   = getLangFromCache($_SESSION['lang']);
    $traduction = array_change_key_case_unicode($dataTrad['langs']);

    if(isset($traduction[mb_strtolower($tmp)])){
        if($traduction[mb_strtolower($tmp)]["trad"] != ""){
            $trad = $traduction[mb_strtolower($tmp)]['trad'].showLinkToTrad($traduction[mb_strtolower($tmp)]['id'],$showTradOnlyString);
        }else{
            $trad = $tmp.showLinkToTrad($traduction[mb_strtolower($tmp)]['id'],$showTradOnlyString);
        }
    }else{
        $trad = $tmp.showLinkToTrad(false);
    }
    if($valeur){
        return vsprintf($trad,$valeur);
    }else{
        return $trad;
    }
}