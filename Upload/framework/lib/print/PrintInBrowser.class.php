<?php

class PrintInBrowser {
	
	private $html = "";
			
	private $datum = "";
	
	public function __construct($name) {
	    
	}
    
    public function setHTMLContent($html) {
        $this->html = $html;
    }
    
    public function send() {
        $html = $this->html;
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/print_in_browser") . "\");");
    }
}