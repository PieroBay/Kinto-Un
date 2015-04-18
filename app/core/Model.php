<?php
	class Model{
		protected $table;
		protected $id;
		protected $bdd;
		protected $connexion = false;
		protected $allOk = true;
		protected $error = "";
		protected $configYml;

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
		public function allOk(){
			return $this->allOk;
		}
		public function testConnect(){
			return $this->connexion;
		}

		public function save($data=null, $upload=array(
			"target"    =>	"upload",
			"table_name"=>	"image",
			"edit" 		=>	"add",
			"champ_name"=>	false, 
			"maxSize"   => 	2097152,
			"widthMax"  => 	100,
			"heightMax" => 	100,
			"ext"       => 	array('jpg','png','jpeg'),
			"red"       => 	false,)){
			
     	   try{
     	   	if(!isset($data) || !is_array($data)) throw new Exception("Aucun tableau n'a été envoyé");
     	   }catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   }

			if($_FILES){
				$token = time();
				$idF = 0;
				if(isset($data['id'])){
					$req2 = $this->bdd->query("SELECT * FROM ".$this->table." WHERE id=".$data['id']); # si update, récupere le token de la table
					$data2 = $req2->fetch(PDO::FETCH_OBJ);
					$token = $data2->$upload['champ_name'];
					$idF=$data['id'];
				}
				$uploading = new Upload($upload,$this->bdd,$token,$idF); 		# init la class
				if(COUNT($_FILES) > 1){
					$newArray = [];
					foreach ($_FILES as $key => $value) {
						if(!empty($_FILES[$key]['name'])){
							foreach($_FILES[$key] as $k => $v) {
								$newArray[$upload['champ_name']][$k][] = $v;
							    #$results[$upload['champ_name']][$k] = array_column($_FILES, $k);
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
						$k = htmlspecialchars($k, ENT_QUOTES);
						$v = htmlspecialchars($v, ENT_QUOTES);
						$sql .= "$k='$v',";
					}
				}
				$sql = substr($sql, 0,-1);
				$i = htmlspecialchars($data['id'], ENT_QUOTES);
				$sql .= "WHERE id = ".$i;
			}else{
				$sql = "INSERT INTO ".$this->table."(";
				unset($data['id']);
				if(isset($data['uniqid'])){unset($data['uniqid']);};
				if(isset($data['valider'])){unset($data['valider']);};
				foreach ($data as $k => $v) {
					$k = htmlspecialchars($k, ENT_QUOTES);
					$v = htmlspecialchars($v, ENT_QUOTES);
					$sql .= "$k,";
				}
				$sql = substr($sql, 0,-1);
				$sql .= ") VALUES (";
				foreach ($data as $k => $v) {
					$k = htmlspecialchars($k, ENT_QUOTES);
					$v = htmlspecialchars($v, ENT_QUOTES);
					$sql .= ":$k,";
				}
				$sql = substr($sql, 0,-1);
				$sql .= ")";
			}

			if($this->allOk){
				$req = $this->bdd->prepare($sql);
				$req->execute($data);
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
			if(isset($data['where'])){ $where = $data['where']; }
			if(isset($data['fields'])){ $fields = $data['fields']; }
			if(isset($data['limit'])){ $limit = "LIMIT ".$data['limit']; }
			if(isset($data['order'])){ $order = $data['order']; if($order != ""){ $order = "ORDER BY ".$order; } }
			$sql = "SELECT $fields FROM ".$this->table." WHERE $where $order $limit";
			$d = array();

			$req = $this->bdd->query($sql);
			while($data = $req->fetch(PDO::FETCH_OBJ)){
				$d[] = $data;
			};
			return $d;
		}

		public function findOne($where=null){
			try{
     	   		if(!isset($where) || is_array($where)) throw new Exception("Aucune data n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}

			$sql = "SELECT * FROM ".$this->table." WHERE $where";
			
			$req = $this->bdd->query($sql);
			$data = $req->fetch(PDO::FETCH_OBJ);
			return $data;
		}
		
		public function findById($id=null){
			try{
     	   		if(!isset($id) || !is_numeric($id) || is_array($id)) throw new Exception("Aucun ID n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}
			$id = htmlspecialchars($id, ENT_QUOTES);
			$sql = "SELECT * FROM ".$this->table." WHERE id= $id";
			$req = $this->bdd->query($sql);
			$data = $req->fetch(PDO::FETCH_OBJ);
			return $data;
		}

		public function delete($data=null){
			try{
     	   		if(!isset($data) || is_array($data)) throw new Exception("Aucun ID n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}
     	   	if(is_numeric($data)){
     	   		$data = htmlspecialchars($data, ENT_QUOTES);
     	   		$where = "id = '".$data."'";
     	   	}else{
     	   		$where = $data;
     	   	}
			$sql = "DELETE FROM ".$this->table." WHERE $where";
			$this->bdd->exec($sql);
		}

		public function connexion($d=null){
			try{
				if(!isset($d) || !is_array($d)) throw new Exception("Aucun tableau n'a été envoyé");
			}catch(Exception $e){
				Error::renderError($e);
				exit();
			}

			$login = $this->configYml['connection']['login'];
			$password = $this->configYml['connection']['password'];
			$connect = htmlspecialchars($connect, ENT_QUOTES);
			$pwd = htmlspecialchars($pwd, ENT_QUOTES);
			$sql = "SELECT *, COUNT(*) AS nb FROM ".$this->table." WHERE ".$login." = '$connect' AND ".$password." = '$pwd'";
			$req = $this->bdd->query($sql);
			$data = $req->fetch(PDO::FETCH_OBJ);
			if($data->nb > 0){
				$sess = explode("|", $this->configYml['connection']['session']);
				foreach ($sess as $k => $v) {
					if($v != 'role'){
						$_SESSION[$v] = $data->$v;
					}
				}
				$_SESSION['ROLE'] = $data->role;

				if($this->configYml['connection']['remember']){
					setcookie("ku_login", $d[$login]);
					setcookie("ku_pwd", $d[$password]);
				}

				$this->setConnexion(true);
			}else{
				$this->setConnexion(false);
			}
		}

		public function deconnexion(){
			$sess = explode("|", $this->configYml['connection']['session']);
			foreach ($sess as $k => $v) {
				if($v != 'role'){
					unset($_SESSION[$v]);
				}
			}
			unset($_SESSION['KU_TOKEN']);
			$_SESSION['ROLE'] = 'visiteur';
		}
	}