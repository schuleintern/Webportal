<?php

/**
 * Template Klasse
 * @author Christian
 *
 */
class tpl {
	/**
	 * Wenn true, dann Entwicklermodus: Templates werden immer geparst! (Aber zur Scriptlaufzeit auch nur einmal)
	 * Ist der Entwicklermodus aus, dann werden die Templates in der Datenbank hinterlegt.
	 * @var boolean An/Aus (true/false)
	 */
	private $isDevelopment = false;
	
	private $templateCache = array();
	
	/**
	 * Der Template Compiler
	 * @var TemplateParser
	 */
	private $templateCompiler;
	
	public function __construct() {
		$this->templateCompiler = new TemplateParser();

		$this->isDevelopment = DB::isDebug();
	}
	
	public function get($name, $ignoreCache = false) {

        if(DB::isDebug() && !file_exists("../framework/templates/$name.htm")) {

            new errorPage("Ein angefordertes Template ist nicht verfügbar: " . $name);
            exit(0);

        }
        else if(!file_exists("../framework/templates/$name.htm")) {
            return "internal error. (tpl)";
        }

        if($this->isDevelopment) $ignoreCache = true;


	    if($ignoreCache) {
            return $this->templateCompiler->parse(file_get_contents("../framework/templates/$name.htm"));
        }


		if(isset($this->templateCache[$name])) {
			return $this->templateCache[$name];
		}
		
		
		$templateContent = DB::getDB()->query_first("SELECT * FROM templates WHERE templateName LIKE '" . $name . "'");

		if($templateContent['templateCompiledContents'] != "") {
			if(!$this->isDevelopment) return $templateContent['templateCompiledContents'];
		}
		

	
		$this->templateCache[$name] = $this->templateCompiler->parse(file_get_contents("../framework/templates/$name.htm"));
		
		DB::getDB()->query("INSERT INTO templates
				(templateName, templateCompiledContents)
				values(
					'" . DB::getDB()->escapeString($name) . "',
					'" . DB::getDB()->escapeString($this->templateCache[$name]) . "'
				) ON DUPLICATE KEY UPDATE templateCompiledContents='" . DB::getDB()->escapeString($this->templateCache[$name]) . "'
				");
		
		
		
		return $this->templateCache[$name];
	}
	
	public function out($string) {
		echo($string);
	}
}

?>