<?php

class NotenRespizienz extends AbstractPage {

  private $unterricht;

  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }


    parent::__construct(['Notenverwaltung', 'Noteneingabe'],false,false,true);




    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

  }

  public function execute() {
      switch($_REQUEST['mode']) {
          case 'klassenlehrer':
              $this->klassenLehrer();
          break;
      }
  }

  public function klassenLehrer() {

    $this->unterricht = SchuelerUnterricht::getByID($_REQUEST['unterrichtID']);

    if($this->unterricht == null) new errorPage();


    if($this->unterricht->getLehrer()->getAsvID() != DB::getSession()->getTeacherObject()->getAsvID()) {
      new errorPage();
    }

    switch($_REQUEST['action']) {
      default:
        $this->showIndex();
      break;

      case 'generateRespBogen':
        $this->generateRespBogen();
      break;
    }
  }

  private function generateRespBogen() {
      // deleteArbeit&arbeitID=1

      $arbeit = NoteArbeit::getbyID($_REQUEST['arbeitID']);

      if($arbeit == null) new errorPage();

      if($arbeit->getFach()->getKurzform() == $this->unterricht->getFach()->getKurzform() && $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {

          $schuljahr = DB::getSettings()->getValue("general-schuljahr");
          $lehrer = DB::getSession()->getTeacherObject()->getDisplayNameMitAmtsbezeichnung();
          $klasse = $this->unterricht->getAllKlassenAsList();

          $art = $arbeit->getName();

          $datum = $arbeit->getDatumAsNaturalDate();

          $fach = $this->unterricht->getFach()->getLangform();


          $notenTabelle = "";

          $schueler = $this->unterricht->getSchueler();

          $alle = sizeof($schueler);
          $mitgeschrieben = 0;
          
          $notenStatistik = [
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0
          ];
          
          

          for($i = 0; $i < sizeof($schueler); $i++) {
              $notenTabelle .= "<tr><td>" . ($i+1) . "</td><td>" . $schueler[$i]->getCompleteSchuelerName() . "</td>";

              $notenTabelle .= "<td>";

              $note = $arbeit->getNoteForSchueler($schueler[$i]);

              if($note != null) {
                  $mitgeschrieben++;

                  $notenTabelle .= $note->getDisplayWert();
                  
                  $notenStatistik[$note->getWert()]++;

                  if($note->isNachtermin()) {
                      $notenTabelle .= "<br /><small>Nachtermin</small>";
                  }

              }
              else {
                  $notenTabelle .= "--";
              }

              $notenTabelle .= "</td></tr>";

          }
          
          $percentMitgeschrieben = 0;
          
          if($alle > 0) {
              $percentMitgeschrieben = floor($mitgeschrieben / $alle * 100);
          }

          $perCentNote = [
              1 => 0,
              2 => 0,
              3 => 0,
              4 => 0,
              5 => 0,
              6 => 0
          ];
          
          
          if($alle > 0) {
              $perCentNote = [
                  1 => floor($notenStatistik[1] / $mitgeschrieben * 100),
                  2 => floor($notenStatistik[2] / $mitgeschrieben * 100),
                  3 => floor($notenStatistik[3] / $mitgeschrieben * 100),
                  4 => floor($notenStatistik[4] / $mitgeschrieben * 100),
                  5 => floor($notenStatistik[5] / $mitgeschrieben * 100),
                  6 => floor($notenStatistik[6] / $mitgeschrieben * 100)
              ];
          }
          
          $schnitt = $arbeit->getSchnitt();




      }


      eval("\$html = \"" . DB::getTPL()->get("notenverwaltung/respizienz/lehrer/arbeit") . "\";");
      
      $print = new PrintNormalPageA4WithHeader("Abgabeliste");
      $print->setHTMLContent($html);
      $print->send();
  }

  public static function hasSettings() {
    return false;
  }

  public static function getSettingsDescription() {
    return [];
  }

  public static function getSiteDisplayName() {
    return 'Notenverwaltung - Respizienz Lehrer';
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
