<?php 

	class Upload{

		private $final = array();
		private $i=0;
		private $ins = array();
		private $FILES;
		private $upload;
		private $repository;
		private $bdd;
		private $id;

		function __construct($upload,$bdd,$token,$id){
			$this->upload = $upload;
			$this->id = $id;
			$this->bdd = $bdd;
			$this->ins['token'] = $token;
			$this->repository =	ROOT.'/src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/';
		}

		public function multiple($data){
			$this->FILES = $data;

			if($this->id != 0 && $this->upload['edit'] == "replace"){
				$req = $this->bdd->prepare("SELECT * FROM ".$this->upload['table_name']." WHERE token=:token");
				$req->execute(array(':token' => $this->ins['token']));
				while($d = $req->fetch(PDO::FETCH_OBJ)){
					unlink(ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/'.$d->file_name);
				}

				$req = $this->bdd->prepare("DELETE FROM ".$this->upload['table_name']." WHERE token = :token");
				$req->execute(array(":token"=>$this->ins['token']));
			}

			foreach ($this->FILES['name'] as $key => $value) {
				$tmp = $this->upload($key);
				if($tmp['status'] == "ok"){
					$this->ins['file_name'] = $tmp['name'];
					$this->ins['principal'] = ($key == 0)? 1: 0;

					if($this->id != 0 && $this->upload['edit'] == "add"){
						$this->ins['principal'] = 0;
					}

					$sql = "INSERT INTO ".$this->upload['table_name']." (token, file_name, principal) VALUES (:token,:file_name,:principal)";
					$req = $this->bdd->prepare($sql);
					$req->execute($this->ins);

					$this->final[$key] = "ok";
				}else{
					$this->final[$key] = "error";
					$this->final['message'] = $tmp['message'];
				}
			}
			return $this->verif();
		}

		public function single($data){
			$this->FILES = $data;
			if($this->id != 0 && $this->upload['edit'] == "replace"){
				$req = $this->bdd->prepare("SELECT * FROM ".$this->upload['table_name']." WHERE token=:token");
				$req->execute(array(':token' => $this->ins['token']));
				while($d = $req->fetch(PDO::FETCH_OBJ)){
					unlink(ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/'.$d->file_name);
				}
				$req = $this->bdd->prepare("DELETE FROM ".$this->upload['table_name']." WHERE token = :token");
				$req->execute(array(":token"=>$this->ins['token']));
			}

			$this->i = ($this->id != 0 && $this->upload['edit'] != "replace")? 2 : $this->i;
			
			$this->i++;
			$tmp = $this->upload();
			if($tmp['status'] == "ok"){
				$this->ins['file_name'] = $tmp['name'];
				$this->ins['principal'] = ($this->i == 1)? 1: 0;
				$sql = "INSERT INTO ".$this->upload['table_name']." (token, file_name, principal) VALUES (:token,:file_name,:principal)";
				$req = $this->bdd->prepare($sql);
				$req->execute($this->ins);
				
				$this->final[$this->i] = "ok";
			}else{
				$this->final[$this->i] = "error";
				$this->final['message'] = $tmp['message'];
			}
			return $this->verif();
		}

		static public function deleteDir($dir){
			if($handle = opendir($dir)){
				$array = array();
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if(is_dir($dir.$file)){
							if(!@rmdir($dir.$file)){
								deleteDir($dir.$file.'/'); 
							}
						}else{
						   @unlink($dir.$file);
						}
					}
				}
				closedir($handle);
				@rmdir($dir);
			}
		}

		public function verif(){
			foreach ($this->final as $k => $v) {
				if($v == "error"){
					if(file_exists(ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'])){
						self::deleteDir(ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/');
					}
					$this->final['verif'] = false;
					$this->final['file_name'] = "";
					$this->final['token'] = "";
					break;
				}else{
					$this->final = array(
						"verif"     => true,
						"file_name" => $this->ins['file_name'],
						"token"     => $this->ins['token'],
					);
				}
			}
			return $this->final;
		}

		public function upload($i="single"){
			$tabExt = $this->upload['ext'];
			$infosImg = array();

			if (!file_exists($this->repository)){
		    	mkdir($this->repository, 0755, true);
			}
			if(!is_dir($this->repository)){
				if(!mkdir($this->repository, 0755)){
					exit('Erreur : le répertoire cible ne peut-être créé');
				}
			}

			$return = array("status"=>"","message"=>"","name"=>"");
			if(!is_string($i)){ # multi
				$fName = $this->FILES['name'][$i];
				$fTmp_name = $this->FILES['tmp_name'][$i];
				$fError = $this->FILES['error'][$i];
			}else{ #single
				$fName = $this->FILES['name'];
				$fTmp_name = $this->FILES['tmp_name'];
				$fError = $this->FILES['error'];
			}

			$extension = pathinfo(basename($fName));
			$extension = $extension['extension'];
			$name = uniqid().'_'.time().'.'.$extension;


			if(in_array(strtolower($extension),$tabExt)){

				$infosImg  = getimagesize($fTmp_name);
				$condition = "";

				if(count($this->upload['size']) > 1){
					foreach ($this->upload['size'] as $key) {
						$e = explode("x", $key);
						$condition .= $infosImg[0] == $e[0] && $infosImg[1] == $e[1]." || "; 
					}
					$condition = rtrim($condition, " || ");
				}else{
					$e = explode("x", $this->upload['size'][0]);
					$condition = $infosImg[0] <= $e[0] && $infosImg[1] <= $e[1];
				}

				if($condition){ 
					if(filesize($fTmp_name) <= $this->upload['maxWeight']){

          				if(isset($fTmp_name) && UPLOAD_ERR_OK === $fError){

          					if($this->upload['resize'] != false){

					            switch ($extension) {
					                case 'jpg':
					                    $image = imagecreatefromjpeg($fTmp_name);
					                    break;
					                case 'jpeg':
					                    $image = imagecreatefromjpeg($fTmp_name);
					                    break;
					                case 'png':
					                    $image = imagecreatefrompng($fTmp_name);
					                    break;
					                case 'gif':
					                    $image = imagecreatefromgif($fTmp_name);
					                    break;
					                default:
					                    $image = imagecreatefromjpeg($fTmp_name);
					            }

								if(is_int($this->upload['resize'])){
									$big_size = max(imagesx($image), imagesy($image));
									$max_size = $this->upload['resize'];
									$new_W    = imagesx($image) * $max_size / $big_size;
									$new_H    = imagesy($image) * $max_size / $big_size;
								}else{
									$size  = explode("x", $this->upload['resize']);
									$new_W = $size[0];
									$new_H = $size[1];
								}
								$virtu_img = imagecreatetruecolor($new_W, $new_H);
								imagecopyresampled($virtu_img, $image, 0, 0, 0, 0, $new_W, $new_H, imagesx($image), imagesy($image));
								imagejpeg( $virtu_img,$this->repository.$name,90 );
							}else{
								move_uploaded_file($fTmp_name,$this->repository.$name);
							}
				
							$return["status"] = "ok";
							$return["name"] = $name;

						}else{
							$return["status"] = 'error';
							$return["message"] = 'Erreur lors de l\'upload';
						}
					}else{
						$return["status"] = 'error';
						$return["message"] = 'Fichier trop volumineux';
					}
				}else{
					$return["status"] = 'error';
					$return["message"] = 'Fichier trop grand ('.$this->upload['size'][0].')';
				}
			
			}else{
				$return["status"] = 'error';
				$return["message"] = 'Mauvais format (jpg,png,jpeg)';
			}
			return $return;
		}
	}
