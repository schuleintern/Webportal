<?php

class NotenRespizienz extends AbstractPage {

    /**
     * @var SchuelerUnterricht
     */
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

                                                                                                    // Am 12.05.2021 entfernt wegen Respbogen bei von anderer Lehrkraft erstellter Arbeit
      if($arbeit->getFach()->getKurzform() == $this->unterricht->getFach()->getKurzform()) { //  && $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {

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

          $jgs = 0;

          if(sizeof($schueler) > 0) {
              $jgs = $schueler[0]->getKlassenObjekt()->getKlassenstufe();
          }

          $notenSkala = [];

          if($jgs <= 11) { // Seit 2023 ( isgy)
              for($i = 1; $i < 7; $i++) {
                  $notenSkala[$i] = 0;
              }
          }
          else {
              for($i = 0; $i < 16; $i++) {
                  $notenSkala[$i] = 0;
              }
          }

          $notenStatistik = $notenSkala;


          
          

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

          $perCentNote = $notenSkala;
          
          
          if($alle > 0) {

              foreach ($notenSkala as $note => $wert) {
                  $perCentNote[$note] = floor($notenStatistik[$note] / $mitgeschrieben * 100);
              }
          }
          
          $schnitt = $arbeit->getSchnitt();




      }


      $notenTabelleStatistik = "";

      foreach ($notenSkala as $note => $wert) {

          $notenText = "";

          if(sizeof($notenSkala) > 6) {
              if($note == 1) $notenText = "1 Punkt";
              else $notenText = $note . " Punkte";
          }
          else {
              $notenText = "Note " . $note;
          }

          $notenTabelleStatistik .= "<tr>
					<td>$notenText</td>
					<td>{$notenStatistik[$note]}</td>
					<td>{$perCentNote[$note]} %</td>
				</tr>";
      }

      $notenName = "Note";

      if(sizeof($notenSkala) > 6) {
          $notenName = "Punkte";
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
