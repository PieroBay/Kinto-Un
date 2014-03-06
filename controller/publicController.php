<?php 
	
	class publicController extends Controller{

		var $table = array('personnages'); 						# séparer par des virgules toute les tables qu'on a besoin

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

		function delAction($id){
			$perso = $this->personnages->delete($id);			# delete() -> delete from $table where id = $id

			$this->render(array(							
				"perso"	=>	$perso,
			));	
		}

		function ajoutAction(){
			$form = array();
			$form = new Form('zgzeg', 'POST');
			$form->add('Text', 'nom')
			     ->label('Ton nom');

			$form->add('Text', 'experience')
			     ->label('Ton exp');

			$form->add('Text', 'degats')
			     ->label('tes degats');

			$form->add('submit', 'valider');


			if ($form->is_valid($_POST)) {
				if($_POST['nom'] == 'kiki'){
					$this->Session->setFlash('error', 'Tu as mis kiki!');
				}elseif($_POST['degats'] < 10){
					$this->Session->setFlash('error', 'Degats trop bas!');
				}else{
					$this->personnages->save($_POST);
					$this->Session->setFlash('ok', 'Tout est ok!');

					$this->redirectUrl('public:lire.html.twig');
				}/*
				$this->render(array(							
					"form"	=>	$form,
					"sess"	=>	$this->Session->flash(),
				));	*/
			}else{
				$this->render(array(							
					"form"	=>	$form,
				));	
			}
		}

		function editeAction($id){
			$d['perso'] = $this->personnages->findById($id);
			$form = new Form('zgzeg', 'POST');
			$form->add('Text', 'nom')
			     ->label('Ton nom');

			$form->add('Text', 'experience')
			     ->label('Ton exp');

			$form->add('Text', 'degats')
			     ->label('tes degats');

			$form->add('submit', 'valider');
			$form->bound($d['perso']);

			$d['form'] = $form;
			if ($form->is_valid($_POST)) {
				if($_POST['nom'] == 'kiki'){
					$Session->setFlash('error', 'Tu as mis kiki!');
					$this->render($d);
				}else{
					$this->personnages->save($_POST);
				}

				

			}else{
				$this->render($d);
			}
		}
	}

?>