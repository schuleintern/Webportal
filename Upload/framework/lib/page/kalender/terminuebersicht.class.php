<?php

/**
 * Andere Kalender
 *
 * @author Christian Spitschka
 *
 */
class terminuebersicht extends AbstractPage {

  public function __construct() {
      
      parent::__construct(array("Kalender", "Terminübersicht"));
      
      if(DB::getSession() == null) {
          new errorPage("not logged in");
      }
  }

  
  public function execute() {

      $eventSources = [];
      

          $grades = klasse::getMyKlassen();
          
          // $grades = grade::getMyGradesFromStundenplan();
          
          for($i = 0; $i < sizeof($grades); $i++) {
              $eventSources[] = 'index.php?page=klassenkalender&grade=' . urlencode($grades[$i]->getKlassenName()) . '&action=getJSONData&showGrade=1&ignoreFerien=1';
          }
      
      // Andere Kalender
      
      $andereKalender = andereKalender::getKalenderWithAccess();
      
      for($i = 0; $i < sizeof($andereKalender); $i++) {
          $eventSources[] = 'index.php?page=andereKalender&kalenderID=' . $andereKalender[$i]['kalenderID']. '&action=getJSONData';
      }
      
      // Externe Kalender
      
      $externeKalender = extKalender::getKalenderWithAccess();
      
      for($i = 0; $i < sizeof($externeKalender); $i++) {
          $eventSources[] = 'index.php?page=extKalender&kalenderID=' . $externeKalender[$i]['kalenderID']. '&action=getJSONData';
      }
      
      
      $calFeeds = "";
      
      $calFeeds = implode("','",$eventSources);
      
      if($calFeeds != "") $calFeeds = "'" . $calFeeds . "'";
      
      $today = DateFunctions::getTodayAsSQLDate();
      
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("kalender/terminubersicht/index") . "\");");
  }
  
  
  public static function hasSettings() {
      return false;
  }


  public static function getSettingsDescription() {
    return [];
  }
  
  public static function getSiteDisplayName(){
    return "Terminübersicht";
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return [];
  }

  public static function hasAdmin() {
    return false;
  }

  public static function getAdminMenuGroup() {
    return 'Kalender';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-calendar';
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-male';
  }

  public static function getAdminGroup() {
    return 'Webportal_Terminuebersicht';
  }

  public static function displayAdministration($selfURL) {
    return "";
  }
  
  public static function siteIsAlwaysActive() {
      return false;
  }
}

