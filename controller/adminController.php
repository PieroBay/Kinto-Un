<?php 
	class adminController extends Controller{

		var $table = array('personnages'); 						# séparer par des virgules toute les tables qu'on a besoin

		function indexAction(){
			if($_SESSION['ROLE'] != 'admin'){
				$this->redirectUrl('public:index.html.twig'); 
			}

			$perso = $this->personnages->findAll(array(			# $this->nom_de_la_table->requete_function()
				'limit'	=>	15,
			));		
			$this->render(array(								# envois un tableau et gener la vue twig avec le nom de l'action sans 'Action'
				"perso"	=>	$perso,
			));	
		}

		function delAction($id){
			if($_SESSION['ROLE'] != 'admin'){
				$this->redirectUrl('public:index.html.twig'); 
			}
			$perso = $this->personnages->delete($id);			# delete() -> delete from $table where id = $id

			$this->redirectUrl('public:index.html.twig'); 		# $this->table->id => get LastID if there are a save before
		}

		function ajoutAction(){
			if($_SESSION['ROLE'] != 'admin'){
				$this->redirectUrl('public:index.html.twig'); 
			}
			if ($_POST) {
				if($_POST['nom'] == 'kiki'){
					$this->Session->setFlash('error', 'Tu as mis kiki!');
				}elseif($_POST['degats'] < 10){
					$this->Session->setFlash('error', 'Degats trop bas!');
				}else{
					$this->personnages->save($_POST);
					$this->Session->setFlash('ok', 'Tout est ok!');

					$this->redirectUrl('public:lire.html.twig', $this->personnages->id); 		# $this->table->id => get LastID if there are a save before
				}

				$this->render(array(							
					"sess"	=>	$this->Session->flash(),
				));
			}else{
				$this->render(array(							
				));	
			}
		}

		function editeAction($id){
			if($_SESSION['ROLE'] != 'admin'){
				$this->redirectUrl('public:index.html.twig'); 
			}
			$perso = $this->personnages->findById($id);

			if ($_POST) {
				if($_POST['nom'] == 'kiki'){
					$this->Session->setFlash('error', 'Tu as mis kiki!');
				}elseif($_POST['degats'] < 10){
					$this->Session->setFlash('error', 'Degats trop bas!');
				}else{

					$this->personnages->save($_POST);
					$this->Session->setFlash('ok', 'Tout est ok!');

					$this->redirectUrl('public:lire.html.twig', $this->personnages->id); 		# $this->table->id => get LastID if there is a save before
				}

				$this->render(array(							
					"perso"	=>	$perso,
					"sess"	=>	$this->Session->flash(),
				));
			}else{
				$this->render(array(							
					"perso"	=>	$perso,
				));	
			}
		}
		
	}

?>