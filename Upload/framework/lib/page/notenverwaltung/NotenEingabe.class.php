<?php

class NotenEingabe extends AbstractPage {

  private $unterricht;

  private $kommaFuenfBesser = true;


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

    $this->unterricht = SchuelerUnterricht::getByID($_REQUEST['unterrichtID']);

    if($this->unterricht == null) new errorPage();


    if($this->unterricht->getLehrer()->getAsvID() != DB::getSession()->getTeacherObject()->getAsvID()) {
        if(!self::hasAllRightsInNotenverwaltung()) {
            new errorPage();
        }
    }

    switch($_REQUEST['action']) {
      default:
        $this->showIndex();
      break;

      case 'addArbeit':
        $this->addArbeit();
      break;

      case 'deleteArbeit':
        $this->deleteArbeit();
      break;

      case 'editArbeit':
          $this->editArbeit();
        break;

      case 'saveNoten':
        $this->saveNoten();
      break;

      case 'deleteNote':
          $this->deleteNote();
      break;

      case 'editNote':
          $this->editNote();
      break;

      case 'confirmNote':
          $this->confirmNote();
      break;

      case 'getNotenBogenJSON':
          $this->getNotenbogenJSON();
      break;
    }
  }

  private function getNotenbogenJSON() {
      $schueler = $this->unterricht->getSchueler();

      header("Content-type: text/json");

      for($i = 0; $i < sizeof($schueler); $i++) {
          if($schueler[$i]->getAsvID() == $_REQUEST['schuelerAsvID']) {
              $notenbogen = new Notenbogen($schueler[$i]);

              $table = '<table class="table table-striped table-bordered">' . $notenbogen->getNotentabelleZwischenbericht() . "</table>";

              $answer = [
                  'schuelerAsvID' => $schueler[$i]->getAsvID(),
                  'notentabelle' => $table
              ];



              echo json_encode($answer);
              exit();

          }
      }


      echo(json_encode([
          'schuelerAsvID' => '',
          'notentabelle' => 'Schüler unbekannt.'
      ]));
      exit();
  }

  private function confirmNote() {

      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
      $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);
      $fach = fach::getByKurzform($_REQUEST['fach']);

      if($_REQUEST['deleteIt'] > 0) {
          NoteZeugnisNote::deleteNoteForSchuelerAndFach($schueler, $zeugnis, $fach);
      }
      else {
          NoteZeugnisNote::setNoteForSchuelerAndZeugnisAndFach($schueler, $zeugnis, $fach, $_REQUEST['notenWert'], $_REQUEST['noteKommentar'], $_REQUEST['normalCalcNote'] != $_REQUEST['notenWert']);   
      }


      header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
      exit(0);
  }

  private function editNote() {
      $arbeit = NoteArbeit::getbyID($_REQUEST['arbeitID']);


      $val = $_POST['notenWert'];

      $tendenz = 0;

      $note = $val;
      if(substr($val,0,1) == "+") {
          $tendenz = 1;
          $note = substr($val,1);
      }

      if(substr($val,0,1) == "-") {
          $tendenz = -1;
          $note = substr($val,1);
      }

      $datum = '';

      if($_REQUEST['noteDatum'] != '' && DateFunctions::isNaturalDate($_REQUEST['noteDatum'])) $datum = DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['noteDatum']);

      if($datum != '') $datum = "'" . $datum . "'";
      else $datum = "null";

      if($arbeit != null) { // $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          DB::getDB()->query("INSERT INTO noten_noten (
                    noteSchuelerAsvID,
                    noteWert,
                    noteTendenz,
                    noteArbeitID,
                    noteDatum,
                    noteKommentar,
                    noteIsNachtermin,
                    noteNurWennBesser
                )
                values(
                    '" . DB::getDB()->escapeString($_REQUEST['schuelerAsvID']) . "',
                    '" . DB::getDB()->escapeString($note) . "',
                    '" . DB::getDB()->escapeString($tendenz) . "',
                    '" . $arbeit->getID() . "',
                    $datum,
                    '" . DB::getDB()->escapeString($_REQUEST['noteKommentar']) . "',
                    " . (int)DB::getDB()->escapeString($_REQUEST['noteIsNachtermin'] > 0) . ",
                    " . (int)DB::getDB()->escapeString($_REQUEST['noteNurWennBesser'] > 0) . "
                ) ON DUPLICATE KEY UPDATE
                    noteWert='" . DB::getDB()->escapeString($note) . "',
                    noteTendenz='" . DB::getDB()->escapeString($tendenz) . "',
                    noteDatum=$datum,
                    noteKommentar='" . DB::getDB()->escapeString($_REQUEST['noteKommentar']) . "',
                    noteIsNachtermin='" . DB::getDB()->escapeString($_REQUEST['noteIsNachtermin'] > 0) . "',
                    noteNurWennBesser='" . DB::getDB()->escapeString($_REQUEST['noteNurWennBesser'] > 0) . "'
            ");
      }

      header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
      exit(0);
  }


  private function deleteNote() {
      $arbeit = NoteArbeit::getbyID($_REQUEST['arbeitID']);

      if($arbeit != null) { // && $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          DB::getDB()->query("DELETE FROM noten_noten WHERE noteSchuelerAsvID='" . DB::getDB()->escapeString($_REQUEST['schuelerAsvID']) . "' AND noteArbeitID='" . $arbeit->getID() . "'");
      }

      header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
      exit(0);
  }

  private function saveNoten() {
    $unterricht = $this->unterricht;

    $alleArbeiten = NoteArbeit::getByUnterrichtID($unterricht->getID());

    $anzahl = [
        'SA' => 0,
        'KA' => 0,
        'EX' => 0,
        'MDL' => 0
    ];

    /**
     *
     * @var schueler[] $schueler
     */
    $schueler = $unterricht->getSchueler();

    $arbeiten = [
        'SA' => [],
        'KA' => [],
        'EX' => [],
        'MDL' => []
    ];

    for($i = 0; $i < sizeof($alleArbeiten); $i++) {
      $typ = $alleArbeiten[$i]->getBereich();
      $arbeiten[$typ][] = $alleArbeiten[$i];
    }


    for($i = 0; $i < sizeof($schueler); $i++) {
      foreach($arbeiten as $typ => $arbs) {
        if(sizeof($arbs) > 0) {
          for($a = 0; $a < sizeof($arbs); $a++) {

              if($arbs[$a]->getID() == $_REQUEST['editArbeit']) {

                /**
                 *
                 * @var Note[] $noten
                 */
                $noten = $arbs[$a]->getNoten();


                $val = $_REQUEST["note-" . $schueler[$i]->getAsvID() . "-" . $arbs[$a]->getID()];

                if($val != "") {
                  $tendenz = 0;

                  $note = $val;
                  if(substr($val,0,1) == "+") {
                    $tendenz = 1;
                    $note = substr($val,1);
                  }

                  if(substr($val,0,1) == "-") {
                    $tendenz = -1;
                    $note = substr($val,1);
                  }

                  $datum = 'null';

                  if($arbs[$a]->hasDatum()) {
                      $datum = "'" . $arbs[$a]->getDatumAsSQLDate() . "'";
                  }

                  DB::getDB()->query("INSERT INTO noten_noten (noteSchuelerAsvID, noteWert, noteTendenz, noteArbeitID, noteDatum)
                    values(
                      '" . $schueler[$i]->getAsvID() . "',
                      '" . $note . "',
                      '" . $tendenz . "',
                      '" . $arbs[$a]->getID() . "',
                                        $datum
                    ) ON DUPLICATE KEY UPDATE noteWert='" . $note . "', noteTendenz='" . $tendenz . "'
                  ");
                }
                else {
                  DB::getDB()->query("DELETE FROM noten_noten WHERE noteSchuelerAsvID='" . $schueler[$i]->getAsvID() . "' AND noteArbeitID='" . $arbs[$a]->getID() . "'");
                }
              }
          }
        }
      }
    }

    header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
    exit(0);
  }

  private function deleteArbeit() {
    // deleteArbeit&arbeitID=1

    $arbeit = NoteArbeit::getbyID($_REQUEST['arbeitID']);

    if($arbeit == null) new errorPage();

    if($arbeit->getFach()->getKurzform() == $this->unterricht->getFach()->getKurzform()) { //  && $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {

        if($_REQUEST['randNumberSolution'] == $_REQUEST['randNumber']) $arbeit->delete();
    }

    header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
    exit(0);
  }


  private function editArbeit() {
      // deleteArbeit&arbeitID=1

      $arbeit = NoteArbeit::getbyID($_REQUEST['arbeitID']);

      if($arbeit == null) new errorPage();

      if($arbeit->getFach()->getKurzform() == $this->unterricht->getFach()->getKurzform()) { //  && $arbeit->getLehrerKuerzel() == DB::getSession()->getTeacherObject()->getKuerzel()) {

          $arbeit->setIsMuendlich($_POST['arbeitIsMuendlich']);
          $arbeit->updateName($_POST['arbeitName']);
          $arbeit->setDatum($_POST['arbeitDatum']);
          $arbeit->setGewichtung($_REQUEST['arbeitGewicht']);
      }

      header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
      exit(0);
  }

  private function addArbeit() {

      $datum = '';

      switch($_REQUEST['arbeitBereich']) {
          case 'SA': $datum = $_REQUEST['arbeitDatumSA']; break;
          case 'KA': $datum = $_REQUEST['arbeitDatumKA']; break;
          case 'EX': $datum = $_REQUEST['arbeitDatumEX']; break;
      }


// 	    Debugger::debugObject($_REQUEST,1);

    DB::getDB()->query("INSERT INTO noten_arbeiten (arbeitBereich, arbeitName, arbeitLehrerKuerzel, arbeitGewicht, arbeitIsMuendlich, arbeitDatum, arbeitFachKurzform, arbeitUnterrichtName)
      values(
        '" . DB::getDB()->escapeString($_REQUEST['arbeitBereich']) . "',
        '" . DB::getDB()->escapeString($_REQUEST['arbeitName']) . "',
        '" . DB::getDB()->escapeString(DB::getSession()->getTeacherObject()->getKuerzel()) . "',
        '" . DB::getDB()->escapeString($_REQUEST['arbeitGewicht']) . "',
        '" . DB::getDB()->escapeString($_REQUEST['arbeitIsMuendlich']) . "',
        " . (($datum != '') ?  ("'" . DB::getDB()->escapeString(DateFunctions::getMySQLDateFromNaturalDate($datum)) . "'") : ('NULL')) . ",
        '" . DB::getDB()->escapeString($this->unterricht->getFach()->getKurzform()) . "',
                '" . DB::getDB()->escapeString($this->unterricht->getBezeichnung()) . "'
      )
    ");
    header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
    exit(0);
  }

  /**
   *
   * @param schueler $schueler
   * @param NoteZeugnisNote[] $noten
   */
  private function getZeugnisnoteForSchueler($schueler, $noten) {
      for($i = 0; $i < sizeof($noten); $i++) {
          if($noten[$i]->getSchueler()->getAsvID() == $schueler->getAsvID()) return $noten[$i];
      }

      return null;
  }

  private function showIndex() {
    $unterricht = $this->unterricht;

    $alleArbeiten = NoteArbeit::getByUnterrichtID($unterricht->getID());

    $anzahl = [
      'SA' => 0,
      'KA' => 0,
      'EX' => 0,
      'MDL' => 0
    ];

    /**
     *
     * @var schueler[] $schueler
     */
    $schueler = $unterricht->getSchueler();

    $arbeiten = [
      'SA' => [],
      'KA' => [],
      'EX' => [],
      'MDL' => []
    ];

    for($i = 0; $i < sizeof($alleArbeiten); $i++) {
      $typ = $alleArbeiten[$i]->getBereich();
      $arbeiten[$typ][] = $alleArbeiten[$i];
    }


    /**
     *
     * @var NoteZeugnisKlasse[] $zeugnisse
     */
    $zeugnisse = [];

    // Zeugnisse
    if(sizeof($schueler) > 0) 	$zeugnisse = NoteZeugnis::getForKlasse($schueler[0]->getKlassenObjekt());


    $colspanGesamtNoten = 1 + sizeof($zeugnisse);

    $zeugnisseTH = "";
    $notenZeugnisse = [];

    $fachSchnitte = [];

    for($i = 0; $i < sizeof($zeugnisse); $i++) {
        $zeugnisseTH .= "<th>:" . $zeugnisse[$i]->getZeugnis()->getArt() . "</th>";
        $notenZeugnisse[] = NoteZeugnisNote::getZeugnisNotenForUnterricht($zeugnisse[$i], $unterricht);

        $fachSchnitte[] = "---";

    }


    // Fachschnitte ausrechnen

    for($i = 0; $i < sizeof($zeugnisse); $i++) {
        $summe = 0;
        $anzahl = 0;

        for($n = 0; $n < sizeof($notenZeugnisse[$i]); $n++) {
            $summe += $notenZeugnisse[$i][$n]->getWert();
            $anzahl++;
        }

        if($anzahl > 0) {
            $fachSchnitte[$i] = number_format((float)($summe / $anzahl),2,",",".");
        }
    }





    $htmlSchueler = '<tr><td>&nbsp;</td><td>&nbsp;</td>';


    foreach($arbeiten as $typ => $arbs) {
        if(sizeof($arbs) == 0 && $typ != 'KA') {
            $htmlSchueler .= "<td>&nbsp;</td>";
        }
        else {
            for($a = 0; $a < sizeof($arbs); $a++) {
                $htmlSchueler .= '<td>';

                switch($typ) {
                    case 'SA': $htmlSchueler .= "<b>" . ($a+1) . ". SA</b><br />"; break;
                    case 'KA': $htmlSchueler .= "<b>" . ($a+1) . ". KA</b><br />"; break;
                    case 'EX': $htmlSchueler .= "<b>" . ($a+1) . ". EX</b><br />"; break;
                    case 'MDL': $htmlSchueler .= "<b>" . ($a+1) . ". MDL</b><br />"; break;
                }

                $info = '<b>' . $arbs[$a]->getName() . "</b><br />(" . number_format((float)$arbs[$a]->getGewichtung(),2,",",".") . " fach)";

                $htmlSchueler .= $info;
               //  $htmlSchueler .= "<button class=\"btn btn-xs btn-success\" type=\"button\" data-toggle=\"tooltip\" data-html=\"true\" title=\"" . addslashes(strip_tags($info)) . "\"><i class=\"fa fa-info-circle\"></i></button>";

                if($arbs[$a]->hasDatum()) {
                    $htmlSchueler .= "<br /><small>" . $arbs[$a]->getDatumAsNaturalDate() . "</small>";
                }
                else {
                    $htmlSchueler .= "<br /><small><i>Ohne Datum</i></small>";
                }



                if($arbs[$a]->isMuendlich()) {
                    $htmlSchueler .= "<br /><i>Mündlich</i>";
                }
                else $htmlSchueler .= "<br /><i>schriftlich</i>";

                // (id, titel, gewicht, datum, isMuendlich) {

                $datum = "";
                if($arbs[$a]->hasDatum()) $datum = $arbs[$a]->getDatumAsNaturalDate();

                $muendlich = 0;
                if($arbs[$a]->isMuendlich()) $muendlich = 1;

                $htmlSchueler .= " <button type=\"button\" class=\"btn btn-xs\"
                        onclick=\"javascript:editArbeit(" . $arbs[$a]->getID() . ",'" . addslashes($arbs[$a]->getName()) . "'," . $arbs[$a]->getGewichtung() . ",'" . $datum . "'," . $muendlich . ")\">
                    <i class=\"fa fas fa-pencil-alt\"></i></button><br />";
                
                $htmlSchueler .= " <a href=\"index.php?page=NotenRespizienz&mode=klassenlehrer&action=generateRespBogen&unterrichtID=" . $unterricht->getID() . "&arbeitID=" . $arbs[$a]->getID() . "\" class=\"btn btn-sm btn-default\">";
                $htmlSchueler .= "<i class=\"fas fa fa-print\"></i> Abgabeliste</a>";

                if($arbs[$a]->getID() == $_REQUEST['editArbeit']) {
                    $htmlSchueler .= '<br /><button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i></button>';
                }
                else {
                    $htmlSchueler .= '<br /><button type="button" class="btn btn-sm" onclick="javascript:window.location.href=\'index.php?page=NotenEingabe&unterrichtID=' . $unterricht->getID() . "&editArbeit=" . $arbs[$a]->getID() . '\';"><i class="fa fas fa-pencil-alt"></i> Noten</button>';
                }

                $htmlSchueler .= "</td>";





                // <if($showSave)><then>
                //
                // </then></if>
            }
        }

        if($typ == "SA" || $typ == 'MDL') {
            $htmlSchueler .= "<td>&nbsp;</td>";
        }
    }

    $hasVerrechnung = false;

    $verrechnungen = [];

    for($i = 0; $i < sizeof($schueler); $i++) {

        $verrechnung = NoteVerrechnung::getVerrechnungForUnterricht($this->unterricht, $schueler[$i]);
        if($verrechnung != null) {
            $hasVerrechnung = true;

            $found = false;
            for($v = 0; $v < sizeof($verrechnungen); $v++) {
                if($verrechnungen[$v]->getID() == $verrechnung->getID()) {
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $verrechnungen[] = $verrechnung;
            }
        }
    }

    if($hasVerrechnung) {

        $htmlSchueler .= "<td>Wird Verrechnet mit:";
        
        for($v = 0; $v < sizeof($verrechnungen); $v++) {
        
            $htmlSchueler .= $verrechnungen[$v]->getOtherFach($this->unterricht)->getBezeichnung() . "<br /> " .
    
                $verrechnungen[$v]->getUnterricht1()->getBezeichnung() . " zu " . $verrechnungen[$v]->getUnterricht2()->getBezeichnung() . " (" .
                $verrechnungen[$v]->getGewicht1() . " : " . $verrechnungen[$v]->getGewicht2() . ")<br />";
    

       }

        $htmlSchueler .= "</td>";


        $htmlSchueler .= "<td>Verrechnungsfach</td>";

        $htmlSchueler .= "<td>Gesamtschnitt</td>";


        $colspanGesamtNoten += 2;

    }
    else {

        $htmlSchueler .= "<td>&nbsp;</td>";
    }



    for($i = 0; $i < sizeof($zeugnisse); $i++) {
        $htmlSchueler .= "<td>" . $zeugnisse[$i]->getZeugnis()->getArtName() . "<br />NoS: " . DateFunctions::getNaturalDateFromMySQLDate($zeugnisse[$i]->getNotenschulussAsSQLDate());


        $htmlSchueler .= "<br />";

        if($zeugnisse[$i]->isNotenschlussVorbei()) {
            $htmlSchueler .= "<label class=\"label label-danger\">Notenschluss vorbei</label>";
        }
        else {
            $htmlSchueler .= "<label class=\"label label-success\">Notenbestätigung möglich</label><br /><br />";

            $htmlSchueler .= "<button type=\"button\" class=\"btn btn-primary btn-xs btn-block\" onclick=\"confirmAction('Mit dieser Aktion werden die Zeugnisnoten nach Standardrundung automatisch eingetragen. Eventuelle pädagogische Noten können Sie danach manuell vergeben.','index.php?page=NotenEingabe&unterrichtID=" . $unterricht->getID() . "&mode=confirmAllNoten&zeugnisID=" . $zeugnisse[$i]->getZeugnis()->getID() . "');\"><i class=\"fa fa-check\"></i> Alle nicht bestätigten Noten<br />mit Standardrundung bestätigen</button><br />";
        
            $htmlSchueler .= "<button type=\"button\" class=\"btn btn-danger btn-xs btn-block\" onclick=\"confirmAction('Alle bestätigten Noten wieder entfernen? Sie können auch einzelne Zeugnisnoten wieder entfernen.','index.php?page=NotenEingabe&unterrichtID=" . $unterricht->getID() . "&mode=deconfirmAllNoten&zeugnisID=" . $zeugnisse[$i]->getZeugnis()->getID() . "');\"><i class=\"fa fa-trash\"></i> Alle bestätigten Noten entfernen</button>";
            
        
        }


        $htmlSchueler .= "</td>";



    }


    $htmlSchueler .= "</tr></thead><tbody>";

    $htmlSchueler .= '<tr><td>&nbsp;</td><td>&nbsp;</td>';


    foreach($arbeiten as $typ => $arbs) {
        if(sizeof($arbs) == 0 && $typ != 'KA') {
            $htmlSchueler .= "<td>&nbsp;</td>";
        }
        else {
            for($a = 0; $a < sizeof($arbs); $a++) {
                $htmlSchueler .= "<td><b>&Oslash;</b> " . $arbs[$a]->getSchnitt();

                $htmlSchueler .= "</td>";
            }
        }

        if($typ == "SA" || $typ == 'MDL') {
            $htmlSchueler .= "<td>&nbsp;</td>";
        }
    }

    if($verrechnung != null) {
        $htmlSchueler .= "<td colspan=\"3\">&nbsp;</td>";
    }
    else {
        $htmlSchueler .= "<td>&nbsp;</td>";
    }




    for($i = 0; $i < sizeof($zeugnisse); $i++) {
        $htmlSchueler .= "<td><b>&Oslash;</b> " . $fachSchnitte[$i];
    }


    $htmlSchueler .= "</tr>";

    $tabIndex = 1;

    for($i = 0; $i < sizeof($schueler); $i++) {
      $htmlSchueler .= "<tr><td>" . ($i+1) . "</td><td>";



      $notenCalculator = new NotenCalculcator($schueler[$i], $this->unterricht->getFach());

      $htmlSchueler .= "<a href=\"#\" onclick=\"showNotenbogen('" . $schueler[$i]->getAsvID() . "','" . addslashes($schueler[$i]->getCompleteSchuelerName()) . "')\">" . $schueler[$i]->getCompleteSchuelerName() . "</a>";

      if(sizeof($unterricht->getKoppelUnterricht()) > 0) {
          $htmlSchueler .= " (" . $schueler[$i]->getKlasse() . ")";
      }

      $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler[$i]);

      if($na != null) {

          $htmlSchueler .= "<br /><button type=\"button\" class=\"btn btn-xs\" data-toggle=\"tooltip\" data-html=\"true\" title=\"" . addslashes(strip_tags($na->getInfoString())) . "\"><i class=\"fa fa-exclamation-triangle\"></button>";

          // $htmlSchueler .= "<br /><b>Nachteilsausgleich:</b> " . $na->getInfoString() . "";

      }

      $htmlSchueler .= "</td>";

      $notenSumme = 0;
      $summeGewichtung = 0;

      foreach($arbeiten as $typ => $arbs) {
        if(sizeof($arbs) == 0 && $typ != 'KA') {
          $htmlSchueler .= "<td colspan=\"1\">&nbsp;</td>";
        }
        else {

          for($a = 0; $a < sizeof($arbs); $a++) {

            /**
             *
             * @var Note[] $noten
             */
            $noten = $arbs[$a]->getNoten();

            $noteVorhanden = '';

            $wert = 0;

            $kommentar = '';
            $isNachtermin = false;
            $notenDatum = '';
            $kommentar = '';
            
            $nurWennBesser = false;

            $schnitt = $arbs[$a]->getSchnitt();

            $farbe = '';
            for($n = 0; $n < sizeof($noten); $n++) {
              if($noten[$n]->getSchueler()->getAsvID() == $schueler[$i]->getAsvID()) {
                if($noten[$n]->getTendenz() > 0) {
                  $noteVorhanden = "+" . $noten[$n]->getWert();
                }
                else if($noten[$n]->getTendenz() < 0) {
                  $noteVorhanden = "-" . $noten[$n]->getWert();
                }
                else {
                  $noteVorhanden = $noten[$n]->getWert();
                }

                $notenCalculator->addNote($noten[$n]);

                $wert = $noten[$n]->getWert();

                $farbe = $noten[$n]->getColor();

                $kommentar = $noten[$n]->getKommentar();

                $isNachtermin = $noten[$n]->isNachtermin();

                $notenDatum = $noten[$n]->getDatum();
                
                $nurWennBesser = $noten[$n]->nurWennBesser();

                break;
              }
            }

            if($noteVorhanden != '') {
              $notenSumme += 1;
            }



            if($_GET['editArbeit'] == $arbs[$a]->getID()) {
                $showSave = true;
                $htmlSchueler .= "<td style=\"width:1%\"><input tabindex=\"$tabIndex\" class=\"form-control\" autocomplete=\"off\" type=\"text\" name=\"note-" . $schueler[$i]->getAsvID() . "-" . $arbs[$a]->getID() . "\" value=\"" . $noteVorhanden . "\"></td>";
                $tabIndex++;
            }
            else {



                if($noteVorhanden == '') $noteVorhanden = '<i class="fa fa-ban"></i>';

                




                $noteEditForm = $noteVorhanden;
                if($noteEditForm == '<i class="fa fa-ban"></i>') $noteEditForm = '';

                $notenDatumEdit = "";

                if($notenDatum != "") $notenDatumEdit = DateFunctions::getNaturalDateFromMySQLDate($notenDatum);

                if($notenDatum == "" && $arbs[$a]->hasDatum()) $notenDatumEdit = $arbs[$a]->getDatumAsNaturalDate();


                $htmlSchueler .= "<td style=\"width:1%\">";


                $htmlSchueler .= "<button type=\"button\" class=\"btn btn-block btn-default btn-xs disbaled\"
                                onclick=\"editNote('" . $schueler[$i]->getAsvID() . "'," . $arbs[$a]->getID() . ",'$noteEditForm','$notenDatumEdit','" . str_Replace("\n","",str_replace("\r","",addslashes(htmlspecialchars($kommentar)))) . "'," . ($isNachtermin ? 1 : 0) . "," . ($nurWennBesser ? 1 : 0) . ")\">


                <font size=\"+2\" color=\"" . $farbe . "\">";
                
                $htmlSchueler .= (($nurWennBesser) ? "(" : "");
                $htmlSchueler .= $noteVorhanden;
                $htmlSchueler .= (($nurWennBesser) ? ")" : "");
                
                $htmlSchueler .= "</font>";

                if(!$arbs[$a]->hasDatum()) {
                    if($notenDatum != "") $htmlSchueler .= "<br />" . DateFunctions::getNaturalDateFromMySQLDate($notenDatum);
                    else $htmlSchueler .= "<br /><i class=\"fa fa-ban\"></i><i class=\"fa fa-calendar\"></i>";
                }
                else {
                    if($notenDatum != "" && $arbs[$a]->getDatumAsSQLDate() != $notenDatum) {
                        $htmlSchueler .= "<br /><small>" . DateFunctions::getNaturalDateFromMySQLDate($notenDatum) . "</small>";
                    }
                }



                if($isNachtermin) $htmlSchueler .= "<br /><small>Nachtermin</small>";

                if($kommentar != "") {
                    $htmlSchueler .= '<br /><button type="button" class="btn btn-xs" data-toggle="tooltip" data-html="true" title="' . addslashes(strip_tags(htmlspecialchars($kommentar))) . '"><i class="fa fa-sticky-note"></i></button>';

                }


                // function editNote(schuelerAsvID, arbeitID, wert, datum, kommentar, isNachtermin) {
                $htmlSchueler .= "<br />


                                </button>
                            </td>";
            }
          }
        }

        if($typ == 'SA') {
            $htmlSchueler .= "<td><font size=\"+2\">" . number_format((float)$notenCalculator->getSchnittGrossMitRechnung(),2,",",".") . "</font></td>";
        }

        if($typ == 'MDL') {
            $htmlSchueler .= "<td><font size=\"+2\">" . number_format((float)$notenCalculator->getSchnittKleinMitRechnung(),2,",",".") . "</font></td>";
        }
      }

      if($notenSumme == 0) {
        $htmlSchueler .= "<td>--</td>";
      }
      else {
          $htmlSchueler .= "<td><font size=\"+2\">" . number_format((float)$notenCalculator->getSchnitt(),2,",",".") . "</font>";

        if($notenCalculator->isNotenschutzrechnung()) {
            $htmlSchueler .= "<br ><small>§34 Abs. 7 Nr. 2 BaySchO</small>";
        }

        $htmlSchueler .= "</td>";
      }


      // Verrechnung?

      $gesamtSchnitt = $notenCalculator->getSchnitt();


      if($hasVerrechnung != null) {

          $verrechnungFound = false;

          for($v = 0; $v < sizeof($verrechnungen); $v++) {

              if($verrechnungFound) break;


              $verrechnung = $verrechnungen[$v];


              $otherFach = $verrechnung->getOtherFach($this->unterricht);

              $unterrichtNote = new UnterrichtsNoten($otherFach, $schueler[$i]);

              if ($unterrichtNote->hasNoten()) {
                  $htmlSchueler .= "<td><font size=\"+2\">" . number_format((float)$unterrichtNote->getNotenCalculator()->getSchnitt(), 2, ",", ".") . "</font>";

                  if ($unterrichtNote->getNotenCalculator()->isNotenschutzrechnung()) {
                      $htmlSchueler .= "<br ><small>§34 Abs. 7 Nr. 2 BaySchO</small>";
                  }

                  $htmlSchueler .= "</td>";

                  $verrechnungFound = true;
              } else {
                  if($i == sizeof($verrechnungen)-1)
                        $htmlSchueler .= "<td>--</td>";
              }

              $noteMyFach = $notenCalculator->getSchnitt();
              $noteOtherFach = $unterrichtNote->getNotenCalculator()->getSchnitt();

              $gesamtNote = 0;

              if ($noteMyFach > 0 && $noteOtherFach > 0) {
                  // Verrechnen

                  $summe = $verrechnung->getMyGewicht($this->unterricht) * $noteMyFach + $verrechnung->getOtherGewicht($this->unterricht) * $noteOtherFach;

                  $gesamtNote = NotenCalculcator::NoteRunden($summe / ($verrechnung->getGewicht1() + $verrechnung->getGewicht2()));
              } else if ($noteMyFach > 0) {
                  $gesamtNote = $noteMyFach;
              } else if ($noteOtherFach > 0) {
                  $gesamtNote = $noteOtherFach;
              }

              $gesamtSchnitt = $gesamtNote;

          }

          if ($gesamtNote > 0) $htmlSchueler .= "<td><font size=\"+2\">" . number_format((float)$gesamtNote, 2, ",", ".") . "</font></td>";
          else {
              $htmlSchueler .= "<td>--</td>";
          }
      }



      // Zeugnisse laden

      for($z = 0; $z < sizeof($zeugnisse); $z++) {
          $note = $this->getZeugnisnoteForSchueler($schueler[$i], $notenZeugnisse[$z]);

          $buttonInnerHTML = "";

          $presetKommentar = "";
          $presetNote = 0;


          $normalCalcNote = 0;



          if($schueler[$i]->getKlassenObjekt()->getKlassenstufe() > 11) {
              if($gesamtSchnitt < 1) $normalCalcNote = 0;
              else $normalCalcNote = round($gesamtSchnitt, 0, PHP_ROUND_HALF_UP);
          }
          else {
              $normalCalcNote = round($gesamtSchnitt, 0, PHP_ROUND_HALF_DOWN);
          }


          if($note == null) {
              $displayNote = $normalCalcNote;

              if($_REQUEST['mode'] == 'confirmAllNoten' && !$zeugnisse[$z]->isNotenschlussVorbei() && $_REQUEST['zeugnisID'] == $zeugnisse[$z]->getZeugnis()->getID()) {
                  NoteZeugnisNote::setNoteForSchuelerAndZeugnisAndFach($schueler[$i], $zeugnisse[$z]->getZeugnis(), $this->unterricht->getFach(), $displayNote, "", false);
              }      
              

              
              $presetNote = $displayNote;
              $buttonInnerHTML .= $displayNote . "</font>";
              $buttonInnerHTML .= "<br /><small><font color=\"red\"><i class=\"fa fa-ban\"></i> nicht bestätigt</font></small>";
          }
          else {
              
              if($_REQUEST['mode'] == 'deconfirmAllNoten' && !$zeugnisse[$z]->isNotenschlussVorbei() && $_REQUEST['zeugnisID'] == $zeugnisse[$z]->getZeugnis()->getID()) {
                  NoteZeugnisNote::deleteNoteForSchuelerAndFach($schueler[$i], $zeugnisse[$z]->getZeugnis(), $this->unterricht->getFach());
              }
              
              
              $presetNote = $note->getWert();
              $presetKommentar = str_replace("'","\'",str_replace("\r\n","\\n",$note->getPaedBegruendung()));

              $buttonInnerHTML .= "<font size=\"+2\" color=\"" . Note::getNotenColor($note->getWert(), $note->getSchueler()->getKlassenObjekt()->getKlassenstufe()) . "\">";


              if($note->getWert() >= 5 && $note->getSchueler()->getKlassenObjekt()->getKlassenstufe() <= 11) {
                  $buttonInnerHTML .= " <i class=\"fa fa-exclamation-triangle\"></i> ";
              }

              if($note->getWert() < 5 && $note->getSchueler()->getKlassenObjekt()->getKlassenstufe() > 11) {
                  $buttonInnerHTML .= " <i class=\"fa fa-exclamation-triangle\"></i> ";
              }

              $buttonInnerHTML .= $note->getWert();


              $buttonInnerHTML .= "</font>";

              if($note->isPaedNote()) $buttonInnerHTML .= " <i>PN</i> ";

              if($note->getPaedBegruendung() != "") $buttonInnerHTML .= " <i class=\"fa fa-file-o\"></i> <br />";

              $buttonInnerHTML .= "<br /><small><font color=\"green\"><i class=\"fa fa-check\"></i> bestätigt</small></font>";


          }

          if($notenSumme == 0) {
              $noteSchnitt = "---";
          }
          else {
              $noteSchnitt = number_format((float)$gesamtNote,2,",",".");
          }

          if($zeugnisse[$z]->isNotenschlussVorbei()) $disabled = " disabled=\"disabled\"";
          else $disabled = "";

          $htmlSchueler .= "<td><button type=\"button\" $disabled class=\"btn btn-xs\" style=\"width:100%\" onclick=\"confirmNote('" . $schueler[$i]->getAsvID() . "','" . $zeugnisse[$z]->getZeugnis()->getID() . "','" . $this->unterricht->getFach()->getKurzform() . "','" . $presetKommentar . "','" . $presetNote . "','" . $normalCalcNote . "','" . ($noteSchnitt) . "')\">";

          $htmlSchueler .= $buttonInnerHTML;

          $htmlSchueler .= "</button></td>";

      }




      $htmlSchueler .= "</tr>";
    }

    if($_REQUEST['mode'] == 'confirmAllNoten') {
        header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
        exit(0);
    }

    if($_REQUEST['mode'] == 'deconfirmAllNoten') {
        header("Location: index.php?page=NotenEingabe&unterrichtID=" . $this->unterricht->getID());
        exit(0);
    }

   
    $gewichtung = null;
    
    if($this->unterricht->getSchueler()[0] != null) {
        $gewichtung = NoteGewichtung::getByFachAndJGS($this->unterricht->getFach(), $this->unterricht->getSchueler()[0]->getKlassenObjekt()->getKlassenstufe());
        
    }
    
    $gewichtungDisplay = "";
    
    if(schulinfo::isGymnasium()) {
        $gewichtungDisplay = "Gewichtung: 2:1";
        
        if($gewichtung != null) {
            $gewichtungDisplay = "Gewichtung: " . $gewichtung->getGewichtGross() . ":" . $gewichtung->getGewichtKlein();
        }
    }
    
    
    $randNumber = random_int(100000,999999);

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get('notenverwaltung/noteneingabe/unterricht') . "\");");
  }

    /**
     * Hat der Nutzer Zugriff auf die erweiterten Features der Notenverwaltung?
     * @param user $user
     * @return bool
     */
  public static function hasAllRightsInNotenverwaltung($user = null) {
      if($user == null) $user = DB::getSession()->getUser();

      if($user == null) return false;

      if($user->isAdmin()) return true;

      if(schulinfo::isSchulleitung($user)) return true;

      if($user->isMember(self::getAdminGroup())) return true;

      return false;
  }

  public static function hasSettings() {
    return false;
  }

  public static function getSettingsDescription() {
    return [];
  }

  public static function getSiteDisplayName() {
    return 'Noteneingabe / Berechnung';
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

  
  public static function getAdminMenuGroup() {
      return "Notenverwaltung";
  }
  
  public static function getAdminMenuGroupIcon() {
      return "fa fas fa-award";
  }
  
  public static function getAdminMenuIcon() {
      return "fa fas fa-award";
  }
  
  public static function hasAdmin() {
      return true;
  }
  
  public static function displayAdministration($selfURL) {
      
      switch($_REQUEST['action']) {
          case 'gewichtung':
          default:
              return self::adminGewichtung($selfURL);
      }
  }
  
  
  private static function adminGewichtung($selfURL) {
      $html = "";
      
      switch($_REQUEST['mode']) {
          case 'add':

              if($_REQUEST['fach'] == 'alle_faecher_der_schule') {
                  $faecher = fach::getAll();
                  for($i = 0; $i < sizeof($faecher); $i++) {
                      NoteGewichtung::addGewichtung($faecher[$i], $_POST['jgs'], $_POST['gewichtGross'], $_POST['gewichtKlein']);
                  }
              }
              else {
                  $fach = fach::getByKurzform($_REQUEST['fach']);


                  if($fach != null)
                      NoteGewichtung::addGewichtung($fach, $_POST['jgs'], $_POST['gewichtGross'], $_POST['gewichtKlein']);

              }
              

              header("Location: $selfURL");
              exit(0);
          break;
          
          case 'delete':
              $gewichtung = NoteGewichtung::getByFachAndJGS(fach::getByKurzform($_REQUEST['fach']), $_REQUEST['jgs']);
              if($gewichtung != null) $gewichtung->delete();
              
              header("Location: $selfURL");
              exit(0);
          break;
      }
      
      $faecher = fach::getAll();
      
      $selectFaecher = "";
      for($i = 0; $i < sizeof($faecher); $i++) {
          $selectFaecher .= "<option value=\"" . $faecher[$i]->getKurzform() . "\">" . $faecher[$i]->getLangform() . " (" . $faecher[$i]->getKurzform() . ")</option>";
      }
      
      
      
      $gewichtungHTML = "";
      
      $allGewicht = NoteGewichtung::getAll();
      
      for($i = 0; $i < sizeof($allGewicht); $i++) {
          $gewichtungHTML .= "<tr>";
          $gewichtungHTML .= "<td>";
          if ( $allGewicht[$i]->getFach() ) {
              $gewichtungHTML .= $allGewicht[$i]->getFach()->getKurzform();
          }
          $gewichtungHTML .= "</td>";
          $gewichtungHTML .= "<td>" . $allGewicht[$i]->getJGS() . "</td>";
          $gewichtungHTML .= "<td>" . $allGewicht[$i]->getGewichtGross() . "</td>";
          
          $gewichtungHTML .= "<td>" . $allGewicht[$i]->getGewichtKlein() . "</td>";

          $gewichtungHTML .= "<td>";
          if ( $allGewicht[$i]->getFach() ) {
              $gewichtungHTML .= "<button class=\"btn btn-xs btn-danger\" type=\"button\" onclick=\"confirmAction('Soll die Gewichtung wirklich gelöscht werden?','$selfURL&mode=delete&fach=" . urlencode($allGewicht[$i]->getFach()->getKurzform()) . "&jgs=" . $allGewicht[$i]->getJGS() . "')\"><i class=\"fa fa-trash\"></i></button>";
          }
          $gewichtungHTML .= "</td>";


          $gewichtungHTML .= "</tr>";
          
          
      }
      
      eval("\$html = \"" . DB::getTPL()->get("notenverwaltung/noteneingabe/admin/gewichtung") . "\";");
      
      return $html;
  }
  
}


?>
