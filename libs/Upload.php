<?php 

	class Upload{

		private $final = array();
		private $i=0;
		private $j=1;
		private $ins = array();
		private $FILES;
		private $upload;
		private $repository;
		private $bdd;
		private $edition;

		function __construct($upload,$bdd,$token,$edition){
			$this->upload       = $upload;
			$this->edition      = $edition;
			$this->bdd          = $bdd;
			$this->ins['token'] = ($upload["table_name"])?$token:"";
			$this->repository   =	ROOT.'/src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/';
			
			if($upload["edit"] == "replace"){
				$this->upload["sort"] = false;
			}

			# si c'est un sort
			if($upload['sort']){
				# Si une modification, on récupere le dernier sort
				if($edition){
					$req = $this->bdd->prepare("SELECT MAX(sort) as max FROM ".$this->upload['table_name']." WHERE token=:token");
					$req->execute(array(':token' => $this->ins['token']));
					$d 		 = $req->fetch(PDO::FETCH_OBJ);
					$this->j = $d->max+1;
				}else{
					$this->j = 1;
				}
			}
		}

		public function multiple($data){
			$this->FILES = $data;

			if($this->edition && $this->upload['edit'] == "replace"){
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
					if(!$this->upload["sort"]){
						$this->ins['sort'] = ($key == 0)? 1: 0;

						if($this->edition && $this->upload['edit'] == "add"){
							$this->ins['sort'] = 0;
						}
					}else{
						$this->ins['sort'] = $this->j;

						$this->j++;
					}


					$sql = "INSERT INTO ".$this->upload['table_name']." (token, file_name, sort) VALUES (:token,:file_name,:sort)";
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

		public function current($data){
			$this->FILES = $data;
			$tmp = $this->upload();

			if($tmp['status'] == "ok"){
				$this->ins['file_name'] = $tmp['name'];
				
				$this->final[$this->i] = "ok";
			}else{
				$this->final[$this->i] = "error";
				$this->final['message'] = $tmp['message'];
			}
			return $this->verif(true);
		}

		public function single($data){
			$this->FILES = $data;
			if($this->edition && $this->upload['edit'] == "replace"){
				$req = $this->bdd->prepare("SELECT * FROM ".$this->upload['table_name']." WHERE token=:token");
				$req->execute(array(':token' => $this->ins['token']));
				while($d = $req->fetch(PDO::FETCH_OBJ)){
					unlink(ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'].'/'.$d->file_name);
				}
				$req = $this->bdd->prepare("DELETE FROM ".$this->upload['table_name']." WHERE token = :token");
				$req->execute(array(":token"=>$this->ins['token']));
			}

			$this->i = ($this->edition && $this->upload['edit'] != "replace")? 2 : $this->i;
			$this->i++;

			$tmp = $this->upload();

			if(!$this->upload["sort"]){
				$this->ins['sort'] = ($this->i == 1)? 1: 0;
			}else{
				$this->ins['sort'] = $this->j;
				
				$this->j++;
			}

			if($tmp['status'] == "ok"){
				$this->ins['file_name'] = $tmp['name'];
				
				$sql = "INSERT INTO ".$this->upload['table_name']." (token, file_name, sort) VALUES (:token,:file_name,:sort)";
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
								self::deleteDir($dir.$file.'/'); 
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

		public function verif($current=false){
			foreach ($this->final as $k => $v) {
				if($v == "error"){
					/*if(!$current){
						$linkN = ROOT.'src/ressources/files/'.$this->upload['target'].'/'.$this->ins['token'];
					
						if(file_exists($linkN.'/thumbnail')){
							self::deleteDir($linkN.'/thumbnail/');
						}if(file_exists($linkN)){
							self::deleteDir($linkN.'/');
						}
					}else{
						$linkN = ROOT.'src/ressources/files/'.$this->upload['target'];
					}*/
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
			     
			    $is_image = false;
			    if(in_array($infosImg[2] , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP))){
			        $is_image = true;
			    }


				if (!file_exists($this->repository)){
			    	mkdir($this->repository, 0755, true);
			    	if($is_image){
			    		mkdir($this->repository."thumbnail/", 0755, true);
					}
				}
				if(!is_dir($this->repository)){
					if(!mkdir($this->repository, 0755)){
						exit('Erreur : le répertoire cible ne peut-être créé');
					}
				}

			    if($is_image){
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
				}else{
					$condition = true;
				}

				if($condition){ 
					if(filesize($fTmp_name) <= $this->upload['maxWeight']){

          				if(isset($fTmp_name) && UPLOAD_ERR_OK === $fError){

      						if($is_image && $this->upload['thumbnail'] != false){

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
					            $side    = intval($this->upload['thumbnail']);
					            if(imagesx($image) != imagesy($image) && (imagesx($image) - imagesy($image)) > 20){
						            $big_size = min(imagesx($image), imagesy($image));
						            
						            if($big_size < $side){
										$max_sizeRes  = $side;
										$new_WRes     = imagesx($image) * $max_sizeRes / $big_size;
										$new_HRes     = imagesy($image) * $max_sizeRes / $big_size;

										$virtu_imgRes = imagecreatetruecolor($new_WRes, $new_HRes);
										imagecopyresampled($virtu_imgRes, $image, 0, 0, 0, 0, $new_WRes, $new_HRes, imagesx($image), imagesy($image));
						            }

						            $image  = (!isset($virtu_imgRes))?$image:$virtu_imgRes;
						            $xStart = imagesx($image)/2 - $this->upload['thumbnail']/2;
						            $yStart = imagesy($image)/2 - $this->upload['thumbnail']/2;

									$virtu_imgThumb = imagecreatetruecolor($side, $side);
									imagecopy($virtu_imgThumb, $image, 0, 0, $xStart, $yStart, imagesx($image), imagesy($image));									imagejpeg($virtu_imgThumb,$this->repository.'thumbnail/'.$name,90);
								}else{
									$virtu_imgThumb = imagecreatetruecolor($side, $side);
									imagecopyresampled($virtu_imgThumb, $image, 0, 0, 0, 0, $side, $side, imagesx($image), imagesy($image));
									imagejpeg($virtu_imgThumb,$this->repository.'thumbnail/'.$name,90);
								}
							}

          					if($is_image && $this->upload['resize'] != false){

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
								imagejpeg($virtu_img,$this->repository.$name,90);
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
