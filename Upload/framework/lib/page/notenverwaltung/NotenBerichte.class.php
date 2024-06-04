<?php

class NotenBerichte extends AbstractPage {

  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }


    parent::__construct(['Notenverwaltung', 'Notenberichte'],false,false,true);

    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

  }

  
  public function execute() {
      switch($_REQUEST['type']) {
          case 'UnterrichtNotenListe':
              $this->printUnterrichtNotenListe();
          break;
          
          default:
              new errorPage("No direct call.");
          break;
      }
  }
  
  public function printUnterrichtNotenListe() {

    $this->unterricht = SchuelerUnterricht::getByID($_REQUEST['unterrichtID']);

    if($this->unterricht == null) new errorPage();


    if($this->unterricht->getLehrer()->getAsvID() != DB::getSession()->getTeacherObject()->getAsvID() && !DB::getSession()->isAdmin() && !schulinfo::isSchulleitung(DB::getSession()->getUser())) {
      new errorPage();
    }

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
            $fachSchnitte[$i] = number_format((float)$summe / $anzahl,2,",",".");
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
        $htmlSchueler .= "<td>" . $zeugnisse[$i]->getZeugnis()->getArtName();

        $htmlSchueler .= "</td>";



    }


    $htmlSchueler .= "</tr>";

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

      $htmlSchueler .= $schueler[$i]->getCompleteSchuelerName();

      if(sizeof($unterricht->getKoppelUnterricht()) > 0) {
          $htmlSchueler .= " (" . $schueler[$i]->getKlasse() . ")";
      }

      $htmlSchueler .= "</td>";

      $notenSumme = 0;
      $summeGewichtung = 0;

      foreach($arbeiten as $typ => $arbs) {
        if(sizeof($arbs) == 0 && $typ != 'KA') {
          $htmlSchueler .= "<td>&nbsp;</td>";
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
                ; // stub
            }
            else {



                if($noteVorhanden == '') $noteVorhanden = '-';

                $noteEditForm = $noteVorhanden;

                $notenDatumEdit = "";

              
                $htmlSchueler .= "<td>";

                
                $htmlSchueler .= (($nurWennBesser) ? "(" : "");
                $htmlSchueler .= $noteVorhanden;
                $htmlSchueler .= (($nurWennBesser) ? ")" : "");
                
                $htmlSchueler .= "</td>";
            }
          }
        }

        if($typ == 'SA') {
            
            $htmlSchueler .= "<td>" . number_format((float)$notenCalculator->getSchnittGrossMitRechnung(),2,",",".") . "&nbsp;</td>";
        }

        if($typ == 'MDL') {
            $htmlSchueler .= "<td>" . number_format((float)$notenCalculator->getSchnittKleinMitRechnung(),2,",",".") . "&nbsp;</td>";
        }
      }

      if($notenSumme == 0) {
        $htmlSchueler .= "<td>--</td>";
      }
      else {
          $htmlSchueler .= "<td>" . number_format((float)$notenCalculator->getSchnitt(),2,",",".");

        if($notenCalculator->isNotenschutzrechnung()) {
            $htmlSchueler .= "<br ><small>§34 Abs. 7 Nr. 2 BaySchO</small>";
        }

        $htmlSchueler .= "</td>";
      }


      // Verrechnung?

      $gesamtSchnitt = $notenCalculator->getSchnitt();


      if($hasVerrechnung != null) {
          
          $verrechnung = NoteVerrechnung::getVerrechnungForUnterricht($this->unterricht, $schueler[$i]);
          
          $otherFach = $verrechnung->getOtherFach($this->unterricht);

          $unterrichtNote = new UnterrichtsNoten($otherFach, $schueler[$i]);

          if($unterrichtNote->hasNoten()) {
              $htmlSchueler .= "<td>" . number_format((float)$unterrichtNote->getNotenCalculator()->getSchnitt(),2,",",".") . "";

              if($unterrichtNote->getNotenCalculator()->isNotenschutzrechnung()) {
                  $htmlSchueler .= "<br ><small>§34 Abs. 7 Nr. 2 BaySchO</small>";
              }

              $htmlSchueler .= "</td>";
          }
          else {
              $htmlSchueler .= "<td>--</td>";
          }

          $noteMyFach = $notenCalculator->getSchnitt();
          $noteOtherFach = $unterrichtNote->getNotenCalculator()->getSchnitt();

          $gesamtNote = 0;

          if($noteMyFach > 0 && $noteOtherFach > 0) {
              // Verrechnen

              $summe = $verrechnung->getMyGewicht($this->unterricht) * $noteMyFach + $verrechnung->getOtherGewicht($this->unterricht) * $noteOtherFach;

              $gesamtNote = NotenCalculcator::NoteRunden($summe / ($verrechnung->getGewicht1() + $verrechnung->getGewicht2()));
          }
          else if($noteMyFach > 0) {
              $gesamtNote = $noteMyFach;
          }
          else if($noteOtherFach > 0) {
              $gesamtNote = $noteOtherFach;
          }

          $gesamtSchnitt = $gesamtNote;

          if($gesamtNote > 0) $htmlSchueler .= "<td>" . number_format((float)$gesamtNote,2,",",".") . "&nbsp;</td>";
          else $htmlSchueler .= "<td>--</td>";
      }



      // Zeugnisse laden

      for($z = 0; $z < sizeof($zeugnisse); $z++) {
          $note = $this->getZeugnisnoteForSchueler($schueler[$i], $notenZeugnisse[$z]);

          $buttonInnerHTML = "";

          $presetKommentar = "";
          $presetNote = 0;



          $normalCalcNote = round($gesamtSchnitt, 0, PHP_ROUND_HALF_DOWN);

          if($note == null) {
              $displayNote = "n/a";
          }
          else {
              $presetNote = $note->getWert();
              $presetKommentar = str_replace("'","\'",str_replace("\r\n","\\n",$note->getPaedBegruendung()));

              $buttonInnerHTML .= "<font size=\"+2\" color=\"" . Note::getNotenColor($note->getWert()) . "\">";


              if($note->getWert() >= 5) {
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
              $noteSchnitt = number_format((float)$notenCalculator->getSchnitt(),2,",",".");
          }

          $htmlSchueler .= "<td>";

          $htmlSchueler .= $buttonInnerHTML;

          $htmlSchueler .= "&nbsp;</td>";

      }



      $htmlSchueler .= "</tr>";
    }

    eval("\$html = \"" . DB::getTPL()->get('notenverwaltung/berichte/fachnotenliste/index') . "\";");
    
    $print = new PrintInBrowser("");
    $print->setHTMLContent($html);
    $print->send();

    exit;
    
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
