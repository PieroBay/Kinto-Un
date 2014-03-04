<?php 
	class publicController extends Controller{

		var $models = array('Tutoriel'); 					#load le model pour ne pas repeter

		function indexAction(){
			$d['perso'] = $this->Tutoriel->findBy(); 		# model
			$this->set($d); 								#ca pousse les var dans la vue
		}

		function lireAction($id){ 							#separer par des vigules si plusieur donné dans l'url séparer par des /
			$d['perso'] = $this->Tutoriel->find(array(
				'condition' => 'id='.$id
			));
			$d['perso'] = $d['perso'][0];
			$this->set($d);
		}

		function delAction($id){
			$d['perso'] = $this->Tutoriel->delete($id);

			$this->set($d);
		}
	}

?>