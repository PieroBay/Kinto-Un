<?php

	/**
	 * Retourne la string slugifié
	 *
	 * @param String $str
	 * @return void
	 */
    function slugify($str){
        // Convert to lowercase and remove whitespace
        $str = strtolower(trim($str));  

        // Replace high ascii characters
        $chars = array("ä", "ö", "ü", "ß");
        $replacements = array("ae", "oe", "ue", "ss");
        $str = str_replace($chars, $replacements, $str);
        $pattern = array("/(é|è|ë|ê)/", "/(ó|ò|ö|ô)/", "/(ú|ù|ü|û)/");
        $replacements = array("e", "o", "u");
        $str = preg_replace($pattern, $replacements, $str);

        // Remove puncuation
        $pattern = array(":", "!", "?", ".", "/", "'");
        $str = str_replace($pattern, "", $str);

        // Hyphenate any non alphanumeric characters
        $pattern = array("/[^a-z0-9-]/", "/-+/");
        $str = preg_replace($pattern, "-", $str);

        return $str;
    }