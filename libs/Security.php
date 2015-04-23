<?php
class Security{

	private $configYml;

	public function __construct($configYml){
		$this->configYml = $configYml;
	}

	public function isValid(){
		if($_POST && isset($_POST['KU_TOKEN_FIELD']) && $_POST['KU_TOKEN_FIELD'] == $_SESSION['KU_TOKEN'] && substr($_SESSION['KU_TOKEN'], -10) <= time() + $this->configYml['security']['expire']*60){
			unset($_POST['KU_TOKEN_FIELD']);
			unset($_SESSION['KU_TOKEN']);
			$this->newToken();
			return true;
		}else{
			return false;
		}
	}

	public function newToken(){
		$token = (!empty($this->configYml['security']['token']))? filter_var($this->configYml['security']['token'], FILTER_SANITIZE_NUMBER_INT) : time();
		if(!isset($_SESSION['KU_TOKEN']) || substr($_SESSION['KU_TOKEN'], -10) <= time() + $this->configYml['security']['expire']*60){
			$_SESSION['KU_TOKEN'] = hash('sha512', md5(time()*rand(1,$token).uniqid().$this->configYml['security']['token']).openssl_random_pseudo_bytes(32)).time();
		}
	}
}