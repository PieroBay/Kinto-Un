<?php
	function val($condition,$if,$else){
		if($condition){
			return $if;
		}else{
			return $else;
		}
	}