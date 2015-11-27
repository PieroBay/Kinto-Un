<?php
	function datePost($datePoste){
		$date = time() - strtotime($datePoste); 
		if($date <= 60){
			$date = $date;
			$unite = 'secondes';
			$affiche_date = 'Il y a '.round($date).' secondes';
		}elseif ($date > 60 && $date <= 3600) {
			$date = $date / 60;
			if($date < 2){
				$unite = 'minute';
			}else{
				$unite = 'minutes';
			}
			$affiche_date = 'Il y a '.round($date).' '.$unite;
		}elseif ($date > 3600 && $date <= 86400) {
			$date = $date / 60 / 60;
			if($date < 2){
				$unite = 'heure';
			}else{
				$unite = 'heures';
			}
			$affiche_date = 'Il y a '.round($date).' '.$unite;
		}elseif ($date > 86400 && $date <= 423000) {
			$date = $date / 60 / 60 / 24;
			if($date < 2){
				$unite = 'jour';
			}else{
				$unite = 'jours';
			}
			$affiche_date = 'Il y a '.round($date).' '.$unite;
		}elseif ($date > 423000) {
			 $affiche_date = 'Le '.date('d M Y',strtotime($datePoste));
		}
		return $affiche_date;
	}