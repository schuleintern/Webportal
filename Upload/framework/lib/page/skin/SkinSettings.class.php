<?php

class SkinSettings extends AbstractPage {

  public function __construct() {
    new errorPage('Kein direkter Aufruf');
  }

  public function execute() {
  	
  }

  public static function getSettingsDescription() {
  	return array(
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
