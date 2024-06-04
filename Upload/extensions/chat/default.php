<?php

 

class extChatDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-comments"></i> Chat - Default';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/groups/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/chat",
                "acl" => json_encode( $this->getAcl() )
            ],
            "submenu" => false
        ]);

	}


}
