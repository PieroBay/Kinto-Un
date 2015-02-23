<?php
	class Model{
		protected $table;
		protected $id;
		protected $bdd;
		protected $connexion = false;
		protected $allOk = true;
		protected $error = "";
		protected $connectYml;

		public function __construct(PDO $bdd, $table, $connectYml){
			$this->setBdd($bdd);
			$this->connectYml = $connectYml;
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
			$this->allOk = $tmp;
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
			"maxSize"   => 2097152,
			"widthMax"  => 100,
			"heightMax" => 100,
			"ext"       => array('jpg','png','jpeg'),
			"red"       => false,)){
			
     	   try{
     	   	if(!isset($data) || !is_array($data)) throw new Exception("Aucun tableau n'a été envoyé");
     	   }catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   }

			if($_FILES){
				foreach ($_FILES as $k => $v) {
					$uploadEtat = upload($_FILES, $upload);
					if($uploadEtat['status'] == "ok"){
						$data[$k] = $uploadEtat['name'];
					}else{
						$this->setAllOk(false);
						$this->setError($uploadEtat['message']);
						$data[$k] = "";
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
						$sql .= "$k='$v',";
					}
				}
				$sql = substr($sql, 0,-1);
				$i = strip_tags($data['id']);
				$sql .= "WHERE id = ".$i;
			}else{
				$sql = "INSERT INTO ".$this->table."(";
				unset($data['id']);
				if(isset($data['uniqid'])){unset($data['uniqid']);};
				if(isset($data['valider'])){unset($data['valider']);};
				foreach ($data as $k => $v) {
					$k = strip_tags($k);
					$sql .= "$k,";
				}
				$sql = substr($sql, 0,-1);
				$sql .= ") VALUES (";
				foreach ($data as $k => $v) {
					$k = strip_tags($k);
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

		public function findById($id=null){
			try{
     	   		if(!isset($id) || !is_numeric($id) || is_array($id)) throw new Exception("Aucun ID n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}

			$sql = "SELECT * FROM ".$this->table." WHERE id= $id";
			$req = $this->bdd->query($sql);
			$data = $req->fetch(PDO::FETCH_OBJ);
			return $data;
		}

		public function delete($id=null){
			try{
     	   		if(!isset($id) || !is_numeric($id) || is_array($id)) throw new Exception("Aucun ID n'a été envoyé");
     	   	}catch(Exception $e){
     	   		Error::renderError($e);
     	   		exit();
     	   	}

			$sql = "DELETE FROM ".$this->table." WHERE id = $id";
			$this->bdd->exec($sql);
		}

		public function connexion($d=null){
			try{
				if(!isset($d) || !is_array($d)) throw new Exception("Aucun tableau n'a été envoyé");
			}catch(Exception $e){
				Error::renderError($e);
				exit();
			}

			$login = $this->connectYml['login'];
			$password = $this->connectYml['password'];

			$connect = strip_tags($d[$login]);
			$pwd = strip_tags($d[$password]);
			$sql = "SELECT *, COUNT(*) AS nb FROM ".$this->table." WHERE ".$login." = '$connect' AND ".$password." = '$pwd'";
			$req = $this->bdd->query($sql);
			$data = $req->fetch(PDO::FETCH_OBJ);
			if($data->nb > 0){
				$sess = explode("|", $this->connectYml['session']);
				foreach ($sess as $k => $v) {
					if($v != 'role'){
						$_SESSION[$v] = $data->$v;
					}
				}
				$_SESSION['ROLE'] = $data->role;

				if($this->connectYml['remember']){
					setcookie("ku_login", $d[$login]);
					setcookie("ku_pwd", $d[$password]);
				}

				$this->setConnexion(true);
			}else{
				$this->setConnexion(false);
			}
		}

		public function deconnexion(){
			$_SESSION['ROLE'] = 'visiteur';
		}
	}