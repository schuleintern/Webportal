<?php


use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;


class NotenverwaltungSchulleitung extends AbstractPage {


  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }

    parent::__construct(['Notenverwaltung', 'Schulleitungsfunktionen'],false,false,true);

    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

  }

  public function execute() {
      switch($_REQUEST['action']) {

          default:
              $this->index();
          break;

          case 'deleteNotenKlasse':
              if($_REQUEST['solution_input'] != $_REQUEST['solution']) {
                  $this->index("Lösung falsch!");
              }

              $klasse = klasse::getByName($_REQUEST['klasse']);

              if($klasse == null) new errorPage("klassenobjektnichtvorhanden");

              $schuelerInKlasse = $klasse->getSchueler();

              for($s = 0; $s < sizeof($schuelerInKlasse); $s++) {
                  DB::getDB()->query("DELETE FROM noten_noten WHERE noteSchuelerAsvID='" . $schuelerInKlasse[$s]->getAsvID() . "'");
              }

              $this->index("", "Noten der Klasse " . $klasse->getKlassenName() . " gelöscht.");
          break;
      }
  }

  private function index($error = "", $success = "") {


      $z1 = random_int(1,9);
      $z2 = random_int(1,9);

      $rechnung = "$z1 + $z2 = ";
      $loesung = $z1 + $z2;

      $optionsKlasse = "";
      $klassen = klasse::getAllKlassen();
      for($i = 0; $i < sizeof($klassen); $i++) $optionsKlasse .= "<option value=\"" . $klassen[$i]->getKlassenName() . "\">" . $klassen[$i]->getKlassenName() . "</option>\r\n";

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/schulleitung/index") . "\");");

      PAGE::kill();
  }

  /**
   *
   * @param lehrer $selectedTeacherObject
   */
  private function getTeacherSelectOptions($selectedTeacherObject = null) {
      $lehrer = lehrer::getAll();

      $html = "";

      for($i = 0; $i < sizeof($lehrer); $i++) {
          $selected = "";
          if($selectedTeacherObject != null) {
              if($selectedTeacherObject->getAsvID() == $lehrer[$i]->getAsvID()) $selected = "selected";
          }
          $html .= "<option value=\"" . $lehrer[$i]->getAsvID() . "\"$selected>" . $lehrer[$i]->getDisplayNameMitAmtsbezeichnung() . "</option>";
      }

      return $html;
  }

  public static function hasSettings() {
    return false;
  }

  public static function getSettingsDescription() {
    return [];
  }

  public static function getSiteDisplayName() {
    return 'Notenverwaltung - Startseite';
  }

  public static function siteIsAlwaysActive() {
    return true;
  }

  public static function getAdminGroup() {
    return 'Webportal_Notenverwaltung_Admin';
  }
  
  public static function need2Factor() {
      return TwoFactor::is2FAActive() && TwoFactor::force2FAForNoten();
  }

}


?>
