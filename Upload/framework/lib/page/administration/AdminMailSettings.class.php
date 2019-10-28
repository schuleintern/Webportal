<?php

class AdminMailSettings extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
	    new errorPage();
	}
	
	private function index() {
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "mail-server",
				'typ' => 'ZEILE',
				'titel' => "Mailserver für den Mailversand",
				'text' => ""
			],
            [
                'name' => "mail-server-port",
                'typ' => 'NUMMER',
                'titel' => "Mailserver - Port für den Mailversand",
                'text' => "Standard oft 25"
            ],
            [
                'name' => "mail-server-auth",
                'typ' => 'BOOLEAN',
                'titel' => "Authentifizierung benötigt?",
                'text' => ""
            ],
            [
                'name' => "mail-server-auth-auto-tls",
                'typ' => 'BOOLEAN',
                'titel' => "Automatisches TLS versuchen?",
                'text' => ""
            ],
            [
                'name' => "mail-server-sender",
                'typ' => 'ZEILE',
                'titel' => "Absenderadresse",
                'text' => ""
            ],
            [
                'name' => "mail-server-username",
                'typ' => 'ZEILE',
                'titel' => "Benutzername",
                'text' => ""
            ],
            [
                'name' => "mail-server-password",
                'typ' => 'ZEILE',
                'titel' => "Passwort",
                'text' => ""
            ],
            [
                'name' => "mail-server-securetype",
                'typ' => 'SELECT',
                'options' => [
                    [
                        'value' => '',
                        'name' => 'Keine'
                    ],
                    [
                        'value' => 'starttls',
                        'name' => 'STARTTLS'
                    ],
                    [
                        'value' => 'smtps',
                        'name' => 'SMTPS'
                    ]
                ],
                'titel' => "Verschlüsselung",
                'text' => ""
            ],
		];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Mailserver';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_EMail_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-server';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-envelope';
	}
	
	public static function getAdminMenuGroup() {
		return 'E-Mailverwaltung';
	}

	public static function siteIsAlwaysActive() {
		return true;
	}
	/**
	 * Überprüft, ob die Seite eine Administration hat.
	 * @return boolean
	 */
	public static function hasAdmin() {
		return true;
	}

	public static function displayAdministration($selfURL) {

	    $html = "";
		return $html;
	}
		
}


?>