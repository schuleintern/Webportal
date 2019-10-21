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
      $this->performUpdate($fromVersion,$toVersion);

      if($this->performUpdate($fromVersion, $toVersion)){
          DB::getSettings()->setValue("current-release-id", $updateInfo['updateToReleaseID']);
          DB::getSettings()->setValue('currentVersion', DB::getVersion());
      }
      else {
          echo("Kein Update möglich.");
          exit(0);
      }

      // Template Cache leeren
      DB::getDB()->query("TRUNCATE `templates`");

      // Abschluss
      unlink("../data/update.json");


      new infoPage("Update durchgeführt. Portal ist wieder in Betrieb.", "index.php");
  }

  private function performUpdate($from, $to) {

      if($from == "1.0" && $to == "1.0.1") {
          $this->from100to101();
      }

      if($from == "1.0.0" && $to == "1.0.1") {
          $this->from100to101();
      }

      if($from == "1.0.1" && $to == "1.0.1") {
          $this->from100to101();
      }

      return true;
  }

  private function from100to101() {
      // Änderungen an Datenbank:
      $sql = "CREATE TABLE `lerntutoren` (
            `lerntutorID`  int(11) NOT NULL AUTO_INCREMENT ,
            `lerntutorSchuelerAsvID`  varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            PRIMARY KEY (`lerntutorID`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
            ROW_FORMAT=Dynamic
            ;";

      $sql2 = "
            CREATE TABLE `lerntutoren_slots` (
            `slotID`  int(11) NOT NULL AUTO_INCREMENT ,
            `slotLerntutorID`  int(11) NOT NULL ,
            `slotFach`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            `slotJahrgangsstufe`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
            `slotSchuelerBelegt`  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '' ,
            PRIMARY KEY (`slotID`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
            ROW_FORMAT=Dynamic
            ;";


      DB::getDB()->query($sql, true);
      DB::getDB()->query($sql2, true);

      return true;

  }

    private static function deleteAll($dir) {
        if(is_file($dir)) unlink($dir);
        else if(is_dir($dir)) {
            $dirContent = opendir($dir);

            while($content = readdir($dirContent)) {
                if($content != '.' && $content != "..") {
                    self::deleteAll($content);
                }
            }

            return @rmdir($dir);
        }
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
