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
                'name' => "",
                'typ' => 'TRENNER',
                'titel' => "Allgemeine Einstellungen zum Mailversand",
                'text' => ""
            ],
            [
                'name' => "mail-reply-to",
                'typ' => 'ZEILE',
                'titel' => "Antwort Adresse für abgehende Mails (ReplyTo)",
                'text' => "Wenn der Empfänger auf Antworten in seinem Mailprogramm klickt, wird diese Mail als Absender eingetragen. (Optionale Option)"
            ],
            [
                'name' => "mail-reply-to-name",
                'typ' => 'ZEILE',
                'titel' => "Name der Antwortadresse (z.B. Sekretariat Realschule Testhausen) - NICHT E-Mailadresse",
                'text' => ""
            ],
            [
                'name' => "",
                'typ' => 'TRENNER',
                'titel' => "Mailserver Einstellungen",
                'text' => ""
            ],
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
	    if($_REQUEST['action'] == 'sendTestMail') {
	        $recipient = $_REQUEST['recipient'];

	        $mail = new email($recipient, "Testmail von " . DB::getGlobalSettings()->siteNamePlain, "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.");
	        $mail->sendInstantMail();
        }

	    $html = "";

	    eval("\$html = \"" . DB::getTPL()->get("administration/mailsettings/index") . "\";");

	    return $html;


	}
		
}


?>