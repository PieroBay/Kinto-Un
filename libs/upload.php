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
			$this->repository =	ROOT.'/src/ressources/images/'.$this->upload['target'].'/'.$this->ins['token'].'/';
		}

		public function multiple($data){
			$this->FILES = $data;

			if($this->id != 0 && $this->upload['edit'] == "replace"){
				$req = $this->bdd->query("SELECT * FROM ".$this->upload['table_name']." WHERE token=".$this->ins['token']);
				while($d = $req->fetch(PDO::FETCH_OBJ)){
					unlink(ROOT.'src/ressources/images/'.$this->upload['target'].'/'.$this->ins['token'].'/'.$d->file_name);
				}
				$this->bdd->exec("DELETE FROM ".$this->upload['table_name']." WHERE token = ".$this->ins['token']);
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
				$req = $this->bdd->query("SELECT * FROM ".$this->upload['table_name']." WHERE token=".$this->ins['token']);
				while($d = $req->fetch(PDO::FETCH_OBJ)){
					unlink(ROOT.'src/ressources/images/'.$this->upload['target'].'/'.$this->ins['token'].'/'.$d->file_name);
				}
				$this->bdd->exec("DELETE FROM ".$this->upload['table_name']." WHERE token = ".$this->ins['token']);
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
					if(file_exists(ROOT.'src/ressources/images/'.$this->upload['target'].'/'.$this->ins['token'])){
						self::deleteDir(ROOT.'src/ressources/images/'.$this->upload['target'].'/'.$this->ins['token'].'/');
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

				$infosImg = getimagesize($fTmp_name);

				//if($infosImg[2] >= 1 && $infosImg[2] <= 14){

					if(($infosImg[0] <= $this->upload['widthMax']) && ($infosImg[1] <= $this->upload['heightMax'])){ 

						if(filesize($fTmp_name) <= $this->upload['maxSize']){

	          				if(isset($fTmp_name) && UPLOAD_ERR_OK === $fError){

	          					if($this->upload['red'] != false){
									$image = imagecreatefromjpeg($fTmp_name);
									$plus_grande_des_tailles = max(imagesx($image), imagesy($image));
									$taille_maximum_autorisee = $this->upload['red'];
									$nouvelle_largeur = imagesx($image) * $taille_maximum_autorisee / $plus_grande_des_tailles;
									$nouvelle_hauteur = imagesy($image) * $taille_maximum_autorisee / $plus_grande_des_tailles;
									 
									$image_redimensionnee = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);
									imagecopyresized($image_redimensionnee, $image, 0, 0, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, imagesx($image), imagesy($image));
									imagejpeg( $image_redimensionnee,$this->repository.$name,90 );
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
							$return["message"] = 'Fichier trop volumineux (2mb max)';
						}
					}else{
						$return["status"] = 'error';
						$return["message"] = 'Fichier trop grand ('.$this->upload['widthMax'].'x'.$this->upload['heightMax'].')';
					}
				/*}else{
					$return["status"] = 'error';
					$return["message"] ='Le fichier à uploader n\'est pas une image';
				}*/
			
			}else{
				$return["status"] = 'error';
				$return["message"] = 'Mauvais format (jpg,png,jpeg)';
			}
			return $return;
		}
	}