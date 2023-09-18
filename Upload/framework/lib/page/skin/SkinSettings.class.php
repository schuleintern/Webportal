<?php

class SkinSettings extends AbstractPage {

  public function __construct() {
  }

  public function execute() {
		if($_REQUEST['action'] == 'getLogo') {
			return PAGE::logo();

			/*
			$upload = DB::getSettings()->getUpload('global-logo');
			if($upload != null) $upload->sendFile();
			else {
				return header("Location: cssjs/images/Icon.png");
				exit(0);
			}
			*/
		}
  }

  public static function getSettingsDescription() {
  	return array(
  		[
  			'name' => 'global-logo',
			'typ' => 'BILD',
			'titel' => 'Logo für die Seite',
			'text' => 'Vorlage kann unter <a href=\"https://doku.schule-intern.de/display/ADMINHANDBUCH/Logo\">Administrationshandbuch</a> herunter geladen werden. Bitte hier ein Bild hochladen.'
		],
  		array(
  			'name' => "global-skin-default-color",
  			'typ' => 'SELECT',
  			'options' => [
  				[
  					'value' => 'green',
  					'name' => 'Grün'
  				],
  					[
  							'value' => 'blue',
  							'name' => 'Blau'
  					],
  					[
  							'value' => 'black',
  							'name' => 'Schwarz / weiß'
  					],
  					[
  							'value' => 'purple',
  							'name' => 'Violett (Purple)'
  					],
  					[
  							'value' => 'yellow',
  							'name' => 'Gelb'
  					],
  					[
  							'value' => 'red',
  							'name' => 'rot'
  					]
  					
  			],
  			'titel' => "Standardfarbe für die Oberfläche",
  			'text' => ""
  			)
  			, 
  			array(
  					'name' => "global-skin-force-color",
  					'typ' => 'BOOLEAN',
  					'titel' => "Standardfarbe für alle Benutzer erzwingen?",
  					'text' => ""
  			)
  	);
  }

  public static function getSiteDisplayName() {
    return "Aussehen, Farbe";
  }

  public static function hasSettings() {
    return true;
  }

  public static function getUserGroups() {
    return array();

  }

  public static function siteIsAlwaysActive() {
    return true;
  }
  
  public static function hasAdmin() {
  	return true;
  }
  
  public static function getAdminGroup() {
  	return 'Webportal_Admin_Skin_Settings';
  }
  
  public static function getAdminMenuGroup() {
  	return 'Allgemeine Einstellungen';
  }
  
  public static function displayAdministration($selfURL) {
    return '';
  }
}

?>
