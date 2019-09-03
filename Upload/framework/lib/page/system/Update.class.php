<?php

/**
 * Aufruf nach einem Update
 */
class Update extends AbstractPage {

  public function __construct() {
    parent::__construct ( array (
      "Update"
    ) );
  }

  public function execute() {
      $updateInfo = file_get_contents("../data/update.json");

      if($updateInfo === false) {
          echo("update fail. (not availible)");
          exit(0);
      }

      $updateInfo = json_decode($updateInfo, true);

      $fromVersion = $updateInfo['updateFromVersion'];
      $toVersion = $updateInfo['updateToVersion'];

      // Updates durchführen

      // Abschluss
      unlink("../data/update.json");

      new infoPage("Update durchgeführt. Portal ist wieder in Betrieb.", "index.php");
  }

  private function performUpdate($from, $to) {


      return true;
  }

  public static function getSettingsDescription() {
    return [];
  }

  public static function getSiteDisplayName() {
    return "Update";
  }

  public static function hasSettings() {
    return false;
  }

  public static function getUserGroups() {
    return array();

  }

  public static function siteIsAlwaysActive() {
    return true;
  }

  public static function hasAdmin() {
    return false;
  }

  public static function displayAdministration($selfURL) {
    return '';
  }
}

?>
