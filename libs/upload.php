<?php 
	function upload($data, $upload=array(
			"target"    =>	"upload",
			"maxSize"   => 2097152,
			"widthMax"  => 100,
			"heightMax" => 100,
			"ext"       => array('jpg','png','jpeg'),
			"red"       => false,)){

		define('TARGET', '../src/ressources/images/'.$upload['target'].'/');
		define('MAX_SIZE', $upload['maxSize']);
		define('WIDTH_MAX', $upload['widthMax']);
		define('HEIGHT_MAX', $upload['heightMax']);

		$tabExt = $upload['ext'];
		$infosImg = array();

		if( !is_dir(TARGET) ) {
		  if( !mkdir(TARGET, 0755) ) {
		    exit('Erreur : le répertoire cible ne peut-être créé');
		  }
		}

		$return = array("status"=>"","message"=>"","name"=>"");

		foreach($_FILES as $file){
			$extension = pathinfo(basename($file['name']));
			$extension = $extension['extension'];
			$name = uniqid().'_'.time().'.'.$extension;

			if(in_array(strtolower($extension),$tabExt)){

				$infosImg = getimagesize($file['tmp_name']);

				if($infosImg[2] >= 1 && $infosImg[2] <= 14){

					if(($infosImg[0] <= WIDTH_MAX) && ($infosImg[1] <= HEIGHT_MAX)){ 

						if(filesize($file['tmp_name']) <= MAX_SIZE){

	          				if(isset($file['error']) && UPLOAD_ERR_OK === $file['error']){

	          					if($upload['red'] != false){
									$image = imagecreatefromjpeg($file['tmp_name']);
									$plus_grande_des_tailles = max(imagesx($image), imagesy($image));
									$taille_maximum_autorisee = $upload['red'];
									$nouvelle_largeur = imagesx($image) * $taille_maximum_autorisee / $plus_grande_des_tailles;
									$nouvelle_hauteur = imagesy($image) * $taille_maximum_autorisee / $plus_grande_des_tailles;
									 
									$image_redimensionnee = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);
									imagecopyresized($image_redimensionnee, $image, 0, 0, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, imagesx($image), imagesy($image));
									imagejpeg( $image_redimensionnee,TARGET.$name,90 );
								}else{
									move_uploaded_file($file['tmp_name'],TARGET.$name);
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
						$return["message"] = 'Fichier trop grand ('.$upload['widthMax'].'x'.$upload['heightMax'].')';
					}
				}else{
					$return["status"] = 'error';
					$return["message"] ='Mauvaise format (jpg,png,jpeg)';
				}
			
			}else{
				$return["status"] = 'error';
				$return["message"] = 'Mauvais format (jpg,png,jpeg)';
			}
		}
		return $return;
	}
?>