<?php

/**
 * Klasse, um das Encoding von Strings zu verändern.
 */
class Encoding {
	public static function isUTF8($string) {
		return mb_detect_encoding ($string, 'UTF-8') == 'UTF-8';
	}
	
	public static function getUTF8($string) {
		if(!self::isUTF8($string)) return utf8_encode($string);
		else return $string;
	}
}

