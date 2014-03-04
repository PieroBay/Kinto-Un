<?php
class Tutoriel extends Model{
	var $table = 'personnages';

	public function findBy($num=2){
		return $this->find(array(
			'limit' => $num,
			'order' => 'id DESC'
		));
	}
}