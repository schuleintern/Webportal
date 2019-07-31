<?php

class TemplateParser {

	public function __construct() {

	}

	/**
	* Kompiliert Template Source Code
	*
	* @param string code template sourcecode
	* @return string compiled templatecode
	*
	*/
	public function parse($code) {

	    // Debug replace https://inc.schule-intern.de in http://inc.schule-intern.de

        // if(DB::isDebug()) $code = str_replace("https://inc.schule-intern.de", "http://inc.schule-intern.de", $code);


		// addslashes
		$code = addcslashes($code, '"\\');
		// replace single if -> if/else
		$code = preg_replace('!</then>(\s*)</if>!i', '</then><else></else>\\1</if>', $code);

		// replace if tag
		// $code = preg_replace('!<if\((.*)\)>!sieU', '"\".((".$this->stripSlashes(\'\\1\').") "', $code);
		$code = preg_replace_callback(
				'!<if\((.*)\)>!siU',
				function($m) { return '" . ((' .$this->stripSlashes($m[1]). ') '; },
				$code);


		// replace end if tag
		$code = preg_replace('!</if>!i', ')."', $code);

		// replace then tag
		$code = preg_replace('!<then>!i', '? ("', $code);

		// replace end then tag
		$code = preg_replace('!</then>!i', '") ', $code);

		// replace else tag
		$code = preg_replace('!<else>!i', ': ("', $code);

		// replace end else tag
		$code = preg_replace('!</else>!i', '")', $code);

		// replace expression tags
		// $code = preg_replace('!<expression>(.*)</expression>!sieU', '"\".".$this->stripSlashes(\'\\1\').".\""', $code);
		$code = preg_replace_callback(
				'!<expression>(.*)</expression>!siU',
				function($m) { return '" . ' . $this->stripSlashes($m[1]) . ' . "'; },
				$code
		);



		return $code;
	}

	/**
	* strip slashes from conditions
	*
	* @param string code if condition
	* @return string if condition
	*
	*/
	function stripSlashes($code) {
		//$code = str_replace('\$', '$', $code);
		$code = str_replace('\\\\', '\\', $code);
		$code = str_replace('\"', '"', $code);
		$code = str_replace('\"', '"', $code);

		return $code;
	}

}
?>