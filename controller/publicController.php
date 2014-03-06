<?php 
	class publicController extends Controller{

		var $table = array('personnages', 'user'); 				# séparer par des virgules toute les tables qu'on a besoin

		function indexAction(){
			$perso = $this->personnages->findAll(array(			# $this->nom_de_la_table->requete_function()
				'limit'	=>	15,
			));
			$this->render(array(								# envois un tableau et gener la vue twig avec le nom de l'action sans 'Action'
				"perso"	=>	$perso,
			));
		}

		function lireAction($id){ 								# separer par des vigules si plusieur données dans l'url qui sont séparées par des /
			$perso = $this->personnages->findById($id);			# findById() -> select * from $table where id = $id

			$this->render(array(
				"perso"	=>	$perso,
			));
		}

		function connexionAction(){
			if($_POST){
				$this->user->connexion($_POST);
				$this->redirectUrl('public:index.html.twig');
			}else{
				$this->render();
			}
		}

		function deconnexionAction(){
			$this->user->deconnexion();
			$this->redirectUrl('public:index.html.twig');
		}
	}
?>