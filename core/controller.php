<?php 
	class Controller{

		var $value = array();
		protected $bdd;

		function __construct($bdd){
			$this->bdd=$bdd;
			if(isset($this->models)){
				foreach ($this->models as $v) {
					$this->loadModel($v);
				}
			}
		}

		function send($d){
			$this->value = array_merge($this->value,$d);
			$array = $this->value;
			$filename = explode("Action", debug_backtrace()[1]["function"])[0];
			$contro = explode("Controller", get_class($this))[0];
			require(ROOT.'libs/template/twig/lib/Twig/LoaderTemplate.php');
		}

		function loadModel($table){
			require_once(ROOT.'core/model.php');
			$this->$table = new Model($this->bdd, $table);
		}
	}

?>