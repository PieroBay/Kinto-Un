<?php 
	require(ROOT.'libs/form.php');

	class publicController extends Controller{

		var $models = array('personnages'); 					# séparer par des virgules toute les tables qu'on a besoin

		function indexAction(){
			$d['perso'] = $this->personnages->findAll(array(	# $this->nom_de_la_table->requete_function()
					'limit'=>15,
			));		
			$this->send($d);									# envois les données et gener la vue
		}

		function lireAction($id){ 								# separer par des vigules si plusieur données dans l'url qui sont séparées par des /
			$d['perso'] = $this->personnages->findById($id);	# findById() -> select * from $table where id = $id

			$this->send($d);
		}

		function delAction($id){
			$d['perso'] = $this->personnages->delete($id);		# delete() -> delete from $table where id = $id

			$this->send($d);
		}

		function ajoutAction(){
			$form = new Form('zgzeg', 'POST');
			$form->add('Text', 'pseudo')
			     ->label('Ton pseudo');

			$form->add('Text', 'xp')
			     ->label('Ton exp');

			$form->add('submit', 'valider');

			$d['form'] = $form;
			
			if ($form->is_valid($_POST)) {
				// On récupère les valeurs
				list($pseudo, $xp) = $form->get_cleaned_data('pseudo', 'xp');
			}else{
				$this->send($d);
			}

		}
	}

?>