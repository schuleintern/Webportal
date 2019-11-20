<?php


class test extends AbstractPage {

  public function __construct() {
    parent::__construct(array("TEST"));

    $this->checkLogin();

    if(!DB::getSession()->isAdmin()) new errorPage();
  }

  public function execute() {
      // Put Tests here.
      // Only access for Admins


    $briefkopf = FileUpload::uploadPictureFromFile("imagesSchool/Briefkopf.jpg", "print-header");
    DB::getSettings()->setValue("print-header", $briefkopf['uploadobject']->getID());

    echo("OK");



  }

  public static function hasSettings() {
    return false;
  }

  /**
   * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
   * @return array(String, String)
   * array(
   * 	   array(
   * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
   *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
   *      'titel' => "Titel der Beschreibung",
   *      'text' => "Text der Beschreibung"
   *     )
   *     ,
   *     .
   *     .
   *     .
   *  )
   */
  public static function getSettingsDescription() {
    return array();
  }


  public static function getSiteDisplayName() {
    return 'Captcha auslesen';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array();

  }

  public static function siteIsAlwaysActive() {
    return true;
  }

}


?>
