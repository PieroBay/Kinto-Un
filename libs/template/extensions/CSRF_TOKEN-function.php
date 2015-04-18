<?php
	function CSRF_TOKEN(){
		echo '<input type="hidden" name="KU_TOKEN_FIELD" value="'.$_SESSION['KU_TOKEN'].'" \/>';
	}