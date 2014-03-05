<?php 
	class publicController extends Controller{

		var $models = array('Tutoriel'); 					#load le model pour ne pas repeter

		function indexAction(){
			$d['perso'] = $this->Tutoriel->findBy(); 		# model
			$this->send($d); 								# envoie le(s) tableau vers la vue qui sera recuperer en twig
		}

		function lireAction($id){ 							# separer par des vigules si plusieur donné dans l'url séparer par des /
			$d['perso'] = $this->Tutoriel->find(array(
				'condition' => 'id='.$id
			));
			$d['perso'] = $d['perso'][0];
			$this->send($d);
		}

		function delAction($id){
			$d['perso'] = $this->Tutoriel->delete($id);

			$this->send($d);
		}

		function ajoutAction(){

		}
	}

?>