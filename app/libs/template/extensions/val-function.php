<?php
	/**
	 * Smart condition
	 *
	 * @param String $condition
	 * @param String $if
	 * @param String $else
	 * @return void
	 */
	function val($condition,$if,$else){
		if($condition){
			return $if;
		}else{
			return $else;
		}
	}