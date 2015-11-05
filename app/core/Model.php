<?php
	class Model{
		protected $table;
		protected $id;
		protected $bdd;
		protected $connexion = false;
		protected $connexionError = "";
		protected $allOk = true;
		protected $error = "";
		protected $configYml;

		protected $data;

		public function __construct(PDO $bdd, $table, $configYml){
			$this->setBdd($bdd);
			$this->configYml = $configYml;
			$this->setTable($table);
		}

		public function setBdd($tmp){
			$this->bdd = $tmp;
		}
		public function setTable($tmp){
			$this->table = $tmp;
		}
		public function setConnexion($tmp){
			$this->connexion = $tmp;
		}
		public function setConnexionError($tmp){
			$this->connexionError = $tmp;
		}
		public function setId($tmp){
			$this->id = $tmp;
		}
		public function setAllOk($tmp){
			$this->allOk = $tmp;
		}
		public function setError($tmp){
			$this->error = $tmp;
		}


		public function lastId(){
			return $this->id;
		}
		public function getError(){
			return $this->error;
		}
		public function getCoError(){
			return $this->connexionError;
		}
		public function allOk(){
			return $this->allOk;
		}
		public function testConnect(){
			return $this->connexion;
		}
		public function exec(){
			return $this->data;
		}

		public function save($data=null, $upload=array(
			"target"    =>	"uploads",
			"table_name"=>	"image",
			"edit" 		=>	"add",
			"champ_name"=>	false, 
			"sort"		=>	false, 
			"size"		=>	["100x100"], 
			"maxWeight" => 	2097152,
			"ext"       => 	array('jpg','png','jpeg','gif'),
			"resize"    => 	false,)){
			
     	   try{
     	   	if(!isset($data) || !is_array($data)) throw new Exception("Aucun tableau n'a été envoyé");
     	   }catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   }

     	   	$fl = ""; if(isset($_FILES) && !empty($_FILES['images']['name'][0])){foreach ($_FILES as $key => $value) { $fl = (count($_FILES[$key]['name']) > 1)? $_FILES[$key]['name'][0]:$_FILES[$key]['name'];}}

			if(!empty($fl)){
				$token = time().uniqid();
				$issetT = false;
				if(isset($data['id'])){
					$req2 = $this->bdd->prepare("SELECT * FROM ".$this->table." WHERE id = :id");  # si update, récupere le token de la table
					$req2->execute(array(':id' => $data['id']));
					$data2 = $req2->fetch(PDO::FETCH_OBJ);

					$token = ($data2->$upload['champ_name'] != NULL)?$data2->$upload['champ_name']:$token;

					$req2 = $this->bdd->prepare("SELECT * FROM ".$upload['table_name']." WHERE token = :token");  # si update, récupere le token de la table
					$req2->execute(array(':token' => $token));
					$data2 = $req2->fetch(PDO::FETCH_OBJ);

					$issetT = (is_object($data2))? true: false;
				}
				$uploading = new Upload($upload,$this->bdd,$token,$issetT); 		# init la class
				if(COUNT($_FILES) > 1){
					$newArray = [];
					foreach ($_FILES as $key => $value) {
						if(!empty($_FILES[$key]['name'])){
							foreach($_FILES[$key] as $k => $v) {
								$newArray[$upload['champ_name']][$k][] = $v;
							}
						}
					}
					$_FILES = $newArray;
				}

				foreach ($_FILES as $k => $v) { 
					if($_FILES[$k]['name'] != "" && is_string($_FILES[$k]['name'] != "") || $_FILES[$k]['name'][0] != ""){ # si champs non vide
						if(is_array($v['name'])){ 							# Si un multiUpload
							$file = $uploading->multiple($_FILES[$k]);		# return array
						}else{												# Si un simple upload ou plusieur champs file
							$file = $uploading->single($_FILES[$k]);		# return array
						}

						if($file['verif']){  									# Si tout est ok pour l'upload
							if($upload['champ_name']){ 							# Si avec table champ image = token (dossier)
								unset($data[$k]);								# Enleve le(s) post file
								$data[$upload['champ_name']] = $file['token'];	# et tu l'init avec le nom du dossier
							}else{ 												# 
								$data[$file[$k]] = $file['file_name'];			# Sinon le post file = le nom du fichier (si image dans la meme table)
							}
						}else{
							$this->setAllOk(false); 							# Genere l'erreur
							$this->setError($file['message']);					# Genere le message d'erreur

							if($upload['champ_name']){ 							# si avec table champ image = token (dossier)
								unset($data[$k]);								# Enleve le(s) post file
								$data[$upload['champ_name']]  = "";				# et tu l'init vide
							}else{												#
								$data[$file[$k]] = "";							# Sinon le post = vide
							}
						}
					}
				}
			}

			if(isset($data['id']) && !empty($data['id'])){
				$sql = "UPDATE ".$this->table." SET ";
						if(isset($data['uniqid'])){unset($data['uniqid']);};
						if(isset($data['valider'])){unset($data['valider']);};
				foreach ($data as $k => $v) {
					if($k != "id"){
						$k = strip_tags($k);
						$v = strip_tags($v);
						$sql .= "$k=:$k,";
					}
				}
				$sql = substr($sql, 0,-1);
				$i = strip_tags($data['id']);
				$sql .= " WHERE id = :id";
			}else{
				$sql = "INSERT INTO ".$this->table."(";
				unset($data['id']);
						if(isset($data['uniqid'])){unset($data['uniqid']);};
						if(isset($data['valider'])){unset($data['valider']);};
				foreach ($data as $k => $v) {
					$k = strip_tags($k);
					$v = strip_tags($v);
					$sql .= "$k,";
				}
				$sql = substr($sql, 0,-1);
				$sql .= ") VALUES (";
				foreach ($data as $k => $v) {
					$k = strip_tags($k);
					$v = strip_tags($v);
					$sql .= ":$k,";
				}
				$sql = substr($sql, 0,-1);
				$sql .= ")";
			}

			if($this->allOk){
				$req = $this->bdd->prepare($sql);

				$c = array();
				foreach ($data as $k => $v) {
					$key = ':'.$k;
					$c[$key] = $v;
				}

				$req->execute($c);
				unset($_FILES);
				if(!isset($data['id'])){
					$this->setId($this->bdd->lastInsertId());
				}else{
					$this->setId($data['id']);
				}
			}
		}

		public function findAll($data=array()){
			$where = "1=1";
			$fields = "*";
			$limit = "";
			$order = "";
			$group = "";
			$like = "";
			if(isset($data['where'])){  $where  = $data['where']; }
			if(isset($data['fields'])){ $fields = $data['fields']; }
			if(isset($data['limit'])){  $limit  = "LIMIT ".$data['limit']; }
			if(isset($data['group'])){  $limit  = "GROUP BY ".$data['group']; }
			if(isset($data['order'])){  $order  = "ORDER BY ".$data['order']; }
			if(isset($data['like'])){  $like    = "LIKE ".$data['like']; }
			$sql = "SELECT $fields FROM ".$this->table." WHERE $where $order $limit $group $like";
			$d = array();

			$req = $this->bdd->query($sql);
			while($data = $req->fetch(PDO::FETCH_OBJ)){
				$d[] = $data;
			};
			$this->data = $d;
			return $this;
		}

		public function find($where=null){
			try{
     	   		if(!isset($where) || !is_array($where)) throw new Exception("Aucune data n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}

			$sql = "SELECT * FROM ".$this->table." WHERE ";

			foreach ($where as $k => $v) {
				$sql .= "$k=:$k AND ";
			}
			$sql = rtrim($sql, " AND ");
			$req = $this->bdd->prepare($sql);

			$c = array();
			foreach ($where as $k => $v) {
				$key = ':'.$k;
				$c[$key] = $v;
			}
			$d = array();
			$req->execute($c);
			while($data = $req->fetch(PDO::FETCH_OBJ)){
				$d[] = $data;
			}
			$this->data = $d;
			return $this;
		}

		public function findOne($where=null){
			try{
     	   		if(!isset($where) || !is_array($where)) throw new Exception("Aucune data n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}

			$sql = "SELECT * FROM ".$this->table." WHERE ";

			foreach ($where as $k => $v) {
				$sql .= "$k=:$k AND ";
			}
			$sql = rtrim($sql, " AND ");
			$req = $this->bdd->prepare($sql);

			$c = array();
			foreach ($where as $k => $v) {
				$key = ':'.$k;
				$c[$key] = $v;
			}
			$req->execute($c);
			$data = $req->fetch(PDO::FETCH_OBJ);
			$this->data = $data;
			return $this;
		}
		
		public function findById($id=null){
			try{
     	   		if(!isset($id) || !is_numeric($id) || is_array($id)) throw new Exception("Aucun ID n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}
			$id = strip_tags($id);
			$sql = "SELECT * FROM ".$this->table." WHERE id = :id";
			$req = $this->bdd->prepare($sql);
			$req->execute(array(':id' => $id));

			$this->data = $req->fetch(PDO::FETCH_OBJ);

			return $this;
		}

		public function delete($data=null){
			try{
     	   		if(!isset($data) || is_string((int) $data)) throw new Exception("Mauvaise donnée envoyé à la requète");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}
     	   	$sql = "DELETE FROM ".$this->table." WHERE ";

     	   	if(!is_array($data)){
     	   		$data = strip_tags($data);
     	   		$data = array("id"=>$data);
     	   		$sql .= "id = :id";
     	   	}else{
     	   		foreach ($data as $k => $v) {
     	   			$k = strip_tags($k);
     	   			$sql .= "$k=:$k AND ";
     	   		}
     	   		$sql = rtrim($sql, " AND ");
     	   	}

     	   	$req = $this->bdd->prepare($sql);

			$c = array();
			foreach ($data as $k => $v) {
				$key = ':'.$k;
				$c[$key] = $v;
			}
			$req->execute($c);
		}

		public function link($array=array(), $field=null){
			$key  = $array[0];
			$as   = $array[1];
			$from = $array[2];

			$field = (is_array($field))?implode(",", $field):"*";

			if(strpos($key, "[]") !== false){
				$key = explode("[]", $key)[0];
				$ar  = true;
			}else{ $ar  = false; }

			if(is_array($this->data)){
				foreach ($this->data as $k => $v) {
					$d = array();
					$req = $this->bdd->query("SELECT $field FROM ".$from." WHERE ".$as."='".$this->data[$k]->$key."'");
					while($data = $req->fetch(PDO::FETCH_OBJ)){
						$d[] = $data;
					}
					if($ar){
						$this->data[$k]->$from = (object) $d;
					}else{
						$d = (count($d) == 1)?$d[0]:$d;
						$this->data[$k]->$from = (object) $d;
					}
					
					if($key != $from && $key != "id"){
						unset($this->data[$k]->$key);
					}
				}
			}else{
				$d = array();
				$req = $this->bdd->query("SELECT $field FROM ".$from." WHERE ".$as."='".$this->data->$key."'");
				while($data = $req->fetch(PDO::FETCH_OBJ)){
					$d[] = $data;
				}

				if($ar){
					$this->data->$from = (object) $d;
				}else{
					$d = (count($d) == 1)?$d[0]:$d;
					$this->data->$from = (object) $d;
				}
				
				if($key != $from && $key != "id"){
					unset($this->data->$key);
				}
			}

			return $this;
		}

		public function connexion($d=null){
			try{
				if(!isset($d) || !is_array($d)) throw new Exception("Aucun tableau n'a été envoyé");
			}catch(Exception $e){
				Error::renderError($e);
				exit();
			}

			$login = $this->configYml['login']['login'];
			$password = $this->configYml['login']['password'];
			$connect = strip_tags($d[$login]);
			$pwd = strip_tags($d[$password]);
			$activation = $this->configYml['login']['activation'];

			$sql = "SELECT *, COUNT(*) AS nb FROM ".$this->table." WHERE ".$login." = :".$login." AND ".$password." = :".$password;
			
			$req = $this->bdd->prepare($sql);
			$req->execute(array(':'.$login => $connect, ':'.$password => $pwd));
			$data = $req->fetch(PDO::FETCH_OBJ);

			if($data->nb > 0){
				if($activation && $data->activation != "1"){
					$this->setConnexion(false);
					$this->setConnexionError("act");
				}else{
					$sess = explode("|", $this->configYml['login']['session']);
					foreach ($sess as $k => $v) {
						if($v != 'role'){
							$_SESSION[$v] = $data->$v;
						}
					}
					$_SESSION['ROLE'] = $data->role;

					if($this->configYml['login']['remember']){
						setcookie("ku_login", $d[$login]);
						setcookie("ku_pwd", $d[$password]);
					}

					$this->setConnexion(true);
				}
			}else{
				$this->setConnexion(false);
				$this->setConnexionError("id");
			}
		}

		public function deconnexion(){
			$sess = explode("|", $this->configYml['login']['session']);
			foreach ($sess as $k => $v) {
				if($v != 'role'){
					unset($_SESSION[$v]);
				}
			}
			unset($_SESSION['KU_TOKEN']);
			$_SESSION['ROLE'] = 'visiteur';
		}
	}