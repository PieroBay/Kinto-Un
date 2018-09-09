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
            $sql = "SELECT model,".$lang." FROM ".$configFile['translate']['dbTable'];

            $req = $bdd->prepare($sql);

            $req->execute();
            $arr = array();
            while($data = $req->fetch(\PDO::FETCH_OBJ)){
                $arr['langs'][$data->model] = $data->{$lang};
            }
        }else{
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
	 * @return void
	 */
	function trans($tmp){
        $dataTrad   = getLangFromCache($_SESSION['lang']);
        $traduction = array_change_key_case($dataTrad, CASE_LOWER);
        return (isset($traduction['langs'][strtolower($tmp)]))?$traduction['langs'][strtolower($tmp)]:$tmp;
	}