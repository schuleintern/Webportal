<?php


use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;


class NotenverwaltungZeugnisse extends AbstractPage {


  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht aktiviert.");
    }

    parent::__construct(['Notenverwaltung', 'Zeugnisse'],false,false,true);

    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

  }

  public function execute() {
      switch($_REQUEST['action']) {
          case 'zwischenbericht':
              $this->zwischenbericht();
          break;
          
          case "printZwischenbericht":
              $this->printZwischenbericht();
          break;

          case 'addZeugnis':
              $this->addZeugnis();
          break;

          case 'deleteZeugnis':
              $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);
              if($zeugnis != null) {
                  $zeugnis->delete();
              }

              header("Location: index.php?page=NotenverwaltungZeugnisse");
              exit(0);
          break;

          case 'printZeugnis':
              $this->printZeugnis();
          break;

          case 'exportUnterMittelstufeToASV':
                $this->exportNotOSToASV();
          break;

          case 'exportOberstufe':
              $this->exportOberstufe();
          break;

          default:
              $this->index();
          break;
      }
  }

  private function exportOberstufe() {
      $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);

      if($zeugnis == null) {
          new errorPage('Ungültige Zeugnis Angabe');
      }

      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Import></Import>');
      $xml->addAttribute("Generierungsdatum", date("Y-m-d") . "+02:00");
      $xml->addAttribute("Schemaversion", "1.0");
      $xml->addAttribute("Schuljahr", DB::getSettings()->getValue('general-schuljahr'));

      for($k = 0; $k < sizeof($zeugnisKlassen); $k++) {

          $schueler = $zeugnisKlassen[$k]->getKlasse()->getSchueler(false);

          for ($s = 0; $s < sizeof($schueler); $s++) {

              $schuelerChild = $xml->addChild("Schueler_Notenimport");
              $schuelerStammdaten = $schuelerChild->addChild("Stammdaten");
              $schuelerStammdaten->addChild("Lokales_Differenzierungsmerkmal", $schueler[$s]->getAsvID());
              $schuelerStammdaten->addChild("Name", $schueler[$s]->getName());
              $schuelerStammdaten->addChild("Vorname", $schueler[$s]->getRufname());
              $schuelerStammdaten->addChild("Geschlecht", ($schueler[$s]->getGeschlecht() == 'w' ? 'weiblich' : 'männlich'));
              $schuelerStammdaten->addChild("Geburtsdatum", $schueler[$s]->getGeburtstagAsSQLDate() . "+02:00");

              $schuelerStammdaten->addChild("Bekenntnis", $schueler[$s]->getBekenntnis());



              $notenbogen = new Notenbogen($schueler[$s]);

              $unterrichtsNoten = $notenbogen->getUnterrichtsNoten();

              for($n = 0; $n < sizeof($unterrichtsNoten); $n++) {
                  if($unterrichtsNoten[$n]->getUnterricht()->isPflichtunterricht()) {

                      $gross = $unterrichtsNoten[$n]->getSchnittGrossOhneRunden();
                      $klein = $unterrichtsNoten[$n]->getSchnittKleinOhneRunden();

                      $hasNoten = true;

                      if($gross >= 0 && $klein >= 0) {
                          $schnittGesamt = ($gross+$klein)/2;
                      }
                      elseif($gross >= 0) {
                          $schnittGesamt = $gross;
                      }
                      elseif($klein >= 0) {
                          $schnittGesamt = $klein;
                      }
                      else {
                          // Keine Noten
                          $schnittGesamt = -1;
                          $hasNoten = false;
                      }

                      if($schnittGesamt >= 0) {
                          $note = round($schnittGesamt, 0, PHP_ROUND_HALF_UP);
                      }

                      if($hasNoten) {

                          $belegungNotenImport = $schuelerChild->addChild("Belegung_NotenImport");
                          $fachSubGroup = $belegungNotenImport->addChild("FachSubGroup");
                          $Schluessel_NotenImport = $fachSubGroup->addChild("Schluessel_NotenImport");
                          $Schluessel_NotenImport->addChild("Schluessel", $unterrichtsNoten[$n]->getUnterricht()->getFach()->getASDID());

                          $kursdaten = $belegungNotenImport->addChild("Kursdaten");

                          $semesterNotenImport = $kursdaten->addChild("Semester_Notenimport");
                          $semesterNotenImport->addChild("Nummer", $_REQUEST['aa']);

                          // Sonderfälle prüfen
                          $isSonderfall = false;
                          /**
                           *
                           * Geschichte und einstündige Sozialkunde (Sk)
                           * Kunst und Bildnerische Praxis (KuB)
                           * Musik und Instrument (MuI)
                           * Sport und Sporttheorie (S-T)
                           */

                          {
                              // Geschichte und einstündige Sozialkunde (Sk)
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'G') {
                                  $skUnterricht = $this->getUnterichtsnotenForFach($unterrichtsNoten, 'Sk');
                                  if ($skUnterricht != null) {
                                      if ($skUnterricht->getUnterricht()->getStunden() == 1) {
                                          $isSonderfall = true;
                                      }
                                  }
                              }

                              // Geschichte und einstündige Sozialkunde (Sk) - vice versa
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'Sk' && $unterrichtsNoten[$n]->getUnterricht()->getStunden() == 1) {
                                  $gUnterricht = $this->getUnterichtsnotenForFach($unterrichtsNoten, 'G');
                                  if ($gUnterricht != null) {
                                      $isSonderfall = true;
                                  }
                              }
                          }

                          {
                              // Sport und Sporttheorie (S-T)
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'Smw') {
                                  $skUnterricht = $this->getUnterichtsnotenForFach($unterrichtsNoten, 'S-T');
                                  if ($skUnterricht != null) {
                                      $isSonderfall = true;
                                  }
                              }

                              // Sport und Sporttheorie (S-T) - vice versa
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'S-T') {
                                  $gUnterricht = $this->getUnterichtsnotenForFach($unterrichtsNoten, 'Smw');
                                  if ($gUnterricht != null) {
                                      $isSonderfall = true;
                                  }
                              }
                          }

                          {
                              // Musik und Instrument (MuI)
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'MuI') {
                                  $isSonderfall = true;
                              }
                          }

                          {
                              // Kunst und Bildnerische Praxis (KuB)
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'KuB') {
                                  $isSonderfall = true;
                              }
                          }

                          if ($isSonderfall) $semesterNotenImport->addAttribute("Sonderfall", "true");

                          // Sportnote als kleiner Leistungsnachweis exportieren
                          {
                              if ($unterrichtsNoten[$n]->getUnterricht()->getFach()->getKurzform() == 'Smw' && schulinfo::isGymnasium()) {
                                  if($klein >= 0 && $gross >= 0) {
                                      $gesamtNote = (
                                          $klein +
                                            (2 * $gross)
                                          ) / 3;
                                      $klein = $gesamtNote;
                                      $gross = -1;
                                  }
                              }
                          }



                          $leistung = $semesterNotenImport->addChild("Leistung");

                          // if($klein >= 0) die(number_format((float)$klein, 12));

                          if ($klein >= 0) $leistung->addChild("Schnitt_Kleine_Leistung", (string)number_format((float)$klein, 12));

                          if ($gross >= 0) $leistung->addChild("Schnitt_Grosse_Leistung", (string)number_format((float)$gross, 12));

                          if ($unterrichtsNoten[$n]->hasNoten()) $leistung->addChild("Schnitt_Gesamt", (string)number_format((float)$schnittGesamt, 12));

                          if ($unterrichtsNoten[$n]->hasNoten()) $leistung->addChild("Note", $note);


                          $semesterNotenImport->addChild("Unterrichtselement", $unterrichtsNoten[$n]->getUnterricht()->getAsvIDForExport());


                      }
                  }
              }

              $schuelerChild->addChild("Abiturjahr", "2022");
          }
      }


      header("Content-type: text/xml");
      if(!DB::isDebug()) header("Content-Disposition: attachment; filename=\"import.xml\"");

      $dom = dom_import_simplexml($xml)->ownerDocument;
      $dom->formatOutput = true;
      echo $dom->saveXML();

      exit(0);
  }


    /**
     * @param UnterrichtsNoten[] $UnterrichtsNoten
     * @param string$fachKurzform
     * @return UnterrichtsNoten|null
     */
  private function getUnterichtsnotenForFach($UnterrichtsNoten, $fachKurzform) {
      for($i = 0; $i < sizeof($UnterrichtsNoten); $i++) {
          if($UnterrichtsNoten[$i]->getUnterricht()->getFach()->getKurzform() == $fachKurzform) return $UnterrichtsNoten[$i];
      }

      return null;
  }

  private function exportNotOSToASV() {
      $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);

      if($zeugnis == null) {
          new errorPage('Ungültige Zeugnis Angabe');
      }

      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();


      $xml = '<?xml version="1.0" encoding="UTF-8"?>
<zeugnisnoten-import version="0.1">
';

      $xml .= "<schulen>
<schule>
<schulnummer>" . DB::getGlobalSettings()->schulnummer . "</schulnummer>
<schuelerinnen>";



      for($k = 0; $k < sizeof($zeugnisKlassen); $k++) {
          $schueler = $zeugnisKlassen[$k]->getKlasse()->getSchueler(false);

          for($s = 0; $s < sizeof($schueler); $s++) {
              $xml .= "
    <schuelerin>
        <identifizierende_merkmale>
            <lokales_differenzierungsmerkmal>" . $schueler[$s]->getAsvID() . "</lokales_differenzierungsmerkmal>
            <familienname></familienname>
            <rufname></rufname>
            <geschlecht></geschlecht>
            <geburtsdatum></geburtsdatum>
        </identifizierende_merkmale>
        <zeugnisse>
            <zeugnis>
                <zeugnisart>" . $_REQUEST['zeugnis_typ'] . "</zeugnisart>
                <gefaehrdung>01</gefaehrdung>
                <ziel_der_jahrgangsstufe>50</ziel_der_jahrgangsstufe>
                <noten>
                ";

              $zeugnisNoten = NoteZeugnisNote::getZeugnisNotenForSchueler($zeugnis, $schueler[$s]);#

              for($n = 0; $n < sizeof($zeugnisNoten); $n++) {
                  $xml .= "
                    <note>
                        <fach>
                            <schluessel>" . $zeugnisNoten[$n]->getFach()->getASDID() . "</schluessel>
                            <kurzform>" . $zeugnisNoten[$n]->getFach()->getKurzform() . "</kurzform>
                        </fach>
                        <notenwert>" . $zeugnisNoten[$n]->getWert() . "</notenwert>
                        <datum>" . DateFunctions::getNaturalDateFromMySQLDate($zeugnisKlassen[$k]->getDatumAsSQLDate()) . "</datum>
                    </note>
                    ";

              }


              $xml .= "
                </noten>
            </zeugnis>
        </zeugnisse>
    </schuelerin>";
          }


      }




      $xml .= "</schuelerinnen>
</schule>
</schulen>
</zeugnisnoten-import>";

      header("Content-type: text/xml");
      header("Content-Disposition: attachment; filename=\"export.xml\"");
      echo($xml);
      exit(0);

  }

  private function printZeugnis() {
      $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);

        if($zeugnis == null) {
            new errorPage('Ungültige Zeugnis Angabe');
        }

        switch($_REQUEST['mode']) {
            default:
                $this->zeugnisDruckIndex($zeugnis);
            break;

            case 'viewKlasse':
                $this->zeugnisDruckViewKlasse($zeugnis);
            break;

            case 'generateZeugnis':
                $this->generateSingleZeugnis($zeugnis);
            break;

            case 'generateForKlasse':
                $this->generateZeugnisForKlasse($zeugnis);
            break;

            case 'getAllInZip':
                $this->getAllIinZip($zeugnis);
            break;
        }
  }


  /**
   *
   * @param NoteZeugnis $zeugnis
   */
  private function generateZeugnisForKlasse($zeugnis) {
      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $noteZeugnisKlasse = null;

      for($i = 0; $i < sizeof($zeugnisKlassen); $i++) {
          if($zeugnisKlassen[$i]->getKlasse()->getKlassenName() == $_REQUEST['klasse']) {
              $noteZeugnisKlasse = $zeugnisKlassen[$i];
              break;
          }
      }

      if($noteZeugnisKlasse == null) {
          die("FAIL");
      }

      $schueler = $noteZeugnisKlasse->getKlasse()->getSchueler();

      for($i = 0; $i < sizeof($schueler); $i++) {
          $this->generateZeugnis($zeugnis, $schueler[$i], $noteZeugnisKlasse);
      }

      // if($schueler == null) die("FAIL SCHÜLER UNBEKANNT.");

      // $this->generateZeugnis($zeugnis, $schueler, $noteZeugnisKlasse);

      header("Location: index.php?page=NotenverwaltungZeugnisse&action=printZeugnis&zeugnisID=" . $zeugnis->getID() . "&mode=viewKlasse&klasse=" . $noteZeugnisKlasse->getKlasse()->getKlassenName());
  }

  private function getAllIinZip($zeugnis) {
      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $noteZeugnisKlasse = null;

      for($i = 0; $i < sizeof($zeugnisKlassen); $i++) {
          if($zeugnisKlassen[$i]->getKlasse()->getKlassenName() == $_REQUEST['klasse']) {
              $noteZeugnisKlasse = $zeugnisKlassen[$i];
              break;
          }
      }

      if($noteZeugnisKlasse == null) {
          die("FAIL");
      }

      $klasse = $noteZeugnisKlasse->getKlasse();

      // Zeugnisse der Klasse laden
      $schuelerAsvIDs = [];
      $schueler = klasse::getByName($noteZeugnisKlasse->getKlasse()->getKlassenName())->getSchueler();

      for($i = 0; $i < sizeof($schueler); $i++) $schuelerAsvIDs[] = $schueler[$i]->getAsvID();

      // Debugger::debugObject($schuelerAsvIDs,1);


      $exemplareSQL = DB::getDB()->query("SELECT * FROM noten_zeugnis_exemplar WHERE zeugnisID='" . $zeugnis->getID() . "' AND schuelerAsvID IN('" . implode("','" , $schuelerAsvIDs) . "')");
      $exemplare = [];
      while($e = DB::getDB()->fetch_array($exemplareSQL)) $exemplare[] = $e;


      $zip = new ZipArchive();
      $filename = "../data/temp/pdf_zeugnisse_export" . md5(rand()) . ".zip";

      if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
          die("cannot open --> $filename\n");
      }


      $commandFilePrintAll = "@echo off
echo Startet den Druck aller Zeugnisse. Es wird auf den Standarddrucker in Microsoft Word gedruckt.
pause\r\n";


      for($i = 0; $i < sizeof($schueler); $i++) {

          for($e = 0; $e < sizeof($exemplare); $e++) {
              if($exemplare[$e]['schuelerAsvID'] == $schueler[$i]->getAsvID()) {
                  $upload = FileUpload::getByID($exemplare[$e]['uploadID']);
                  if($upload != null) {
                      $zip->addFile($upload->getFilePath(), $upload->getFileName());
                        $commandFilePrintAll .= "echo Drucke \"" . $upload->getFileName() . "\r\n";
                      $commandFilePrintAll .= "start /WAIT winword \"" . utf8_decode($upload->getFileName()) . ".docx\" /mFilePrintDefault /mFileCloseOrExit\r\n";
                  }
              }
          }


      }

      $commandFilePrintAll .= "@echo Druck abgeschlossen.";

      // $upload = FileUpload::uploadTextFileContents("printzeugnisse_tamp", $commandFilePrintAll);

      // $zip->addFile($upload['uploadobject']->getFilePath(), "# Alle Zeugnisse drucken.bat");

      $zip->close();

      // Send File

      $file = $filename;

      header('Content-Description: File Transfer');
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename='.basename("Klasse" . $klasse->getKlassenName() . " - Zeugnisse.zip"));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);

      // unlink($file);
      exit(0);


  }

  /**
   *
   * @param NoteZeugnis $zeugnis
   */
  private function generateSingleZeugnis($zeugnis) {
      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $noteZeugnisKlasse = null;

      for($i = 0; $i < sizeof($zeugnisKlassen); $i++) {
          if($zeugnisKlassen[$i]->getKlasse()->getKlassenName() == $_REQUEST['klasse']) {
              $noteZeugnisKlasse = $zeugnisKlassen[$i];
              break;
          }
      }

      if($noteZeugnisKlasse == null) {
          die("FAIL");
      }

      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

      if($schueler == null) die("FAIL SCHÜLER UNBEKANNT.");

      $this->generateZeugnis($zeugnis, $schueler, $noteZeugnisKlasse);

      header("Location: index.php?page=NotenverwaltungZeugnisse&action=printZeugnis&zeugnisID=" . $zeugnis->getID() . "&mode=viewKlasse&klasse=" . $noteZeugnisKlasse->getKlasse()->getKlassenName());
  }


  /**
   *
   * @param NoteZeugnis $zeugnis
   */
  private function zeugnisDruckViewKlasse($zeugnis) {
      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $noteZeugnisKlasse = null;

      for($i = 0; $i < sizeof($zeugnisKlassen); $i++) {
          if($zeugnisKlassen[$i]->getKlasse()->getKlassenName() == $_REQUEST['klasse']) {
              $noteZeugnisKlasse = $zeugnisKlassen[$i];
              break;
          }
      }

      if($noteZeugnisKlasse == null) {
          die("FAIL");
      }

      $klasse = $noteZeugnisKlasse->getKlasse();

      // Zeugnisse der Klasse laden
      $schuelerAsvIDs = [];
      $schueler = klasse::getByName($noteZeugnisKlasse->getKlasse()->getKlassenName())->getSchueler();

      for($i = 0; $i < sizeof($schueler); $i++) $schuelerAsvIDs[] = $schueler[$i]->getAsvID();

      // Debugger::debugObject($schuelerAsvIDs,1);


      $exemplareSQL = DB::getDB()->query("SELECT * FROM noten_zeugnis_exemplar WHERE zeugnisID='" . $zeugnis->getID() . "' AND schuelerAsvID IN('" . implode("','" , $schuelerAsvIDs) . "')");
      $exemplare = [];
      while($e = DB::getDB()->fetch_array($exemplareSQL)) $exemplare[] = $e;


      $schuelerHTML = "";

      for($i = 0; $i < sizeof($schueler); $i++) {
          $schuelerHTML .= "<tr><td>" . $schueler[$i]->getCompleteSchuelerName() . "</td><td>";

          $found = false;

          for($e = 0; $e < sizeof($exemplare); $e++) {
              if($exemplare[$e]['schuelerAsvID'] == $schueler[$i]->getAsvID()) {
                  $upload = FileUpload::getByID($exemplare[$e]['uploadID']);
                  $erzeugt = functions::makeDateFromTimestamp($exemplare[$e]['createdTime']);

                  $schuelerHTML .= "<a href=\"" . $upload->getURLToFile() . "\"><i class=\"fa fa-file-word-o\"></i> Download</a><br />Erzeugt: $erzeugt";
                  $found = true;
              }
          }

          if(!$found) {
              $schuelerHTML .= "<i>Bisher nicht erzeugt</i>";
          }

          $schuelerHTML .= "</td><td><a href=\"index.php?page=NotenverwaltungZeugnisse&action=printZeugnis&zeugnisID=" . $zeugnis->getID() . "&klasse=" . urlencode($klasse->getKlassenName()) . "&mode=generateZeugnis&schuelerAsvID=" . urlencode($schueler[$i]->getAsvID()) . "\"><i class=\"fa fas fa-sync-alt\"></i> Zeugnis generieren</a><br />";


          $schuelerHTML .= "</tr>";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/zeugnisse/druck/klasse/index") . "\");");


  }

  /**
   *
   * @param NoteZeugnis $zeugnis
   * @param schueler $schueler
   * @param NoteZeugnisKlasse $zeugnisKlasse
   */
  private function generateZeugnis($zeugnis, $schueler, $zeugnisKlasse) {
      $bemerkung = NoteZeugnisBemerkung::getForSchueler($schueler, $zeugnis);

      $text1 = " ";
      $text2 = " ";
      $bestanden = "";

      $bestandenOberstufeMR = "";



      if($bemerkung != null) {
          $text1 = $bemerkung->getText1();
          $text2 = $bemerkung->getText2();

          if($text1 == "") $text1 = " ";
          if($text2 == "") $text2 = " ";

          if($bemerkung->vorrueckenAufProbe()) {
              if($schueler->getGeschlecht() == "w") {
                  $bestanden = "Die Schülerin erhält die vorläufige Erlaubnis zum Besuch der Jahrgangsstufe " . ($schueler->getKlassenObjekt()->getKlassenstufe()+1) . ".";
              }
              else {
                  $bestanden = "Der Schüler erhält die vorläufige Erlaubnis zum Besuch der Jahrgangsstufe " . ($schueler->getKlassenObjekt()->getKlassenstufe()+1) . ".";
              }
          }
          else if($bemerkung->klassenzielErreicht()) {
              if($schueler->getGeschlecht() == "w") {
                  $bestanden = "Die Erlaubnis zum Vorrücken in die nächsthöhere Jahrgangsstufe hat sie erhalten.";
              }
              else {
                  $bestanden = "Die Erlaubnis zum Vorrücken in die nächsthöhere Jahrgangsstufe hat er erhalten.";
              }
          }
          else {
              if($schueler->getGeschlecht() == "w") {
                  $bestanden = "Die Erlaubnis zum Vorrücken in die nächsthöhere Jahrgangsstufe hat sie nicht erhalten.";
              }
              else {
                  $bestanden = "Die Erlaubnis zum Vorrücken in die nächsthöhere Jahrgangsstufe hat er nicht erhalten.";
              }
          }


          if(schulinfo::isGymnasium() && $schueler->getKlassenObjekt()->getKlassenstufe() == 10 && $bemerkung->klassenzielErreicht()) {
              $bestandenOberstufeMR = "\n" . ($schueler->getGeschlecht() == 'm' ? "Der Schüler" : "Die Schülerin") . " ist damit zum Eintritt in die Qualifikationsphase der Oberstufe des Gymnasiums berechtigt; dies schließt den Nachweis eines mittleren Schulabschlusses ein.";
          }
      }

      $nachname = "";

      if($schueler->getNamensbestandteilVorgestellt() != "") $nachname .= $schueler->getNamensbestandteilVorgestellt() . " ";
      $nachname .= $schueler->getName();
      if($schueler->getNamensbestandteilNachgestellt() != "") $nachname .= " " . $schueler->getNamensbestandteilNachgestellt();

      $schuelerName = $schueler->getVornamen() . " " . $nachname;

      include_once("../framework/lib/phpword/vendor/autoload.php");

      // die(getcwd());

      $template = $zeugnis->getTemplate();
      if (!$template) {
          return false;
      }

      $templateProcessor = new TemplateProcessor(PATH_VORLAGEN.'notenverwaltung/zeugnisse/'.$template);

      $templateProcessor->setValue("{BEMERKUNG2}", $text2);

      $templateProcessor->setValue("{KLASSE}", $schueler->getKlasse());

      $templateProcessor->setValue("{SCHUELERNAME}", $schuelerName);


      // {DENSCHUELERSCHUELERIN}
      if($schueler->getGeschlecht() == "w") {
          $templateProcessor->setValue("{DENSCHUELERSCHUELERIN}", "die Schülerin");
      }
      else {
          $templateProcessor->setValue("{DENSCHUELERSCHUELERIN}", "den Schüler");
      }



      $templateProcessor->setValue("{GEBURTSDATUM}", $schueler->getGeburtstagAsNaturalDate());
      $templateProcessor->setValue("{GEBURTSORT}", $schueler->getGeburtsort());
      $templateProcessor->setValue("{SCHULJAHR}", DB::getSettings()->getValue("general-schuljahr"));

      if($schueler->getAusbildungsrichtung() != "GY") {

          $ausbildungsrichtung = "";

          if($schueler->getAusbildungsrichtung() == "GY_WSG-W_8") {
              $ausbildungsrichtung = "Wirtschaftswissenschaftlichen ";
          }

          if($schueler->getAusbildungsrichtung() == "GY_SG_8") {
              $ausbildungsrichtung = "Sprachlichen ";
          }

          if($schueler->getAusbildungsrichtung() == "GY_WWG_9") {
              $ausbildungsrichtung = "Wirtschaftswissenschaftlichen ";
          }

          if($schueler->getAusbildungsrichtung() == "GY_NG_9") {
              $ausbildungsrichtung = "Sprachlichen ";
          }

          $templateProcessor->setValue("{AUSBILDUNGSRICHTUNG}", $ausbildungsrichtung);

      }
      else {
          $templateProcessor->setValue("{AUSBILDUNGSRICHTUNG}", "");
      }

      // $templateProcessor->setValue("{AUSBILDUNGSRICHTUNG}", $zeugnisKlasse->getKlasse()->getAusbildungsrichtungen());

      $templateProcessor->setValue("{BEMERKUNG1}", $text1);

      $templateProcessor->setValue("{VOR}", $bestanden);
      $templateProcessor->setValue("{VOROBERSTUFEMR}", $bestandenOberstufeMR);

      $templateProcessor->setValue("{DATUM}", DateFunctions::getNaturalDateFromMySQLDate($zeugnisKlasse->getDatumAsSQLDate()));

      if(sizeof(schulinfo::getSchulleitungLehrerObjects()) > 0) {
          $ersteSL = schulinfo::getSchulleitungLehrerObjects()[0];
          if($ersteSL->getAsvID() != $zeugnisKlasse->getSchulleitung()->getAsvID()) {
              $unterschrift = "i.A. " . $zeugnisKlasse->getSchulleitung()->getZeugnisUnterschrift();
          }
          else {
              $unterschrift = $zeugnisKlasse->getSchulleitung()->getZeugnisUnterschrift();
          }

          $templateProcessor->setValue("{USL}", $unterschrift);
      }
      else {
          $templateProcessor->setValue("{USL}", $zeugnisKlasse->getSchulleitung()->getZeugnisUnterschrift());
      }

      $templateProcessor->setValue("{UKL}", $zeugnisKlasse->getKlassenleitung()->getZeugnisUnterschrift());

      $zeugnisNoten = NoteZeugnisNote::getZeugnisNotenForSchueler($zeugnis, $schueler);

      $noten = [];

      for($n = 1; $n <= 21; $n++) {
          $noten['{N' . $n . "}"] = '--------------';
      }

      // Debugger::debugObject($noten,1);


      $lfs = "";
      $grfs = "";
      $spfs = "";
      $efs = "";
      $ffs = "";
      $ra = "--";

      /** @var NoteZeugnisNote $lateinNote */
      $lateinNote = null;

      for($z = 0; $z < sizeof($zeugnisNoten); $z++) {
          $fach = $zeugnisNoten[$z]->getFach()->getKurzform();

          switch($fach) {
              case 'Ev':
                  $ra = "ev.";
                  $noten["{N1}"] = $zeugnisNoten[$z]->getWertText();
              break;



              case 'K':
                  $ra = "r.-k.";
                  $noten["{N1}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'D':
                  $noten["{N3}"] = $zeugnisNoten[$z]->getWertText();
              break;

              case 'B':
                  $noten["{N12}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'C':
                  $noten["{N11}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Ph':
                  $noten["{N10}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'G':
                  $noten["{N14}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Geo':
                  $noten["{N15}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'WIn':
                  $noten["{N21}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Inf':
                  $noten["{N9}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Ku':
                  $noten["{N18}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'M':
                  $noten["{N8}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Mu':
                  $noten["{N19}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'NuT':
                  $noten["{N13}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Sp':
                  $noten["{N5}"] = $zeugnisNoten[$z]->getWertText();
                  $spfs = $this->getFremdspracheNummer("Spanisch", $schueler);
                  break;

              case 'PuG':
                  $noten["{N17}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Sm':
                  $noten["{N20}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'Sw':
                  $noten["{N20}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'E':
                  $noten["{N6}"] = $zeugnisNoten[$z]->getWertText();
                  $efs = $this->getFremdspracheNummer("Englisch", $schueler);
                  break;


              case 'L':
                  $noten["{N4}"] = $zeugnisNoten[$z]->getWertText();
                  $lfs = $this->getFremdspracheNummer("Latein", $schueler);
                  $lateinNote = $zeugnisNoten[$z];
                  break;

              case 'F':
                  $noten["{N7}"] = $zeugnisNoten[$z]->getWertText();
                  $ffs = $this->getFremdspracheNummer("Französisch", $schueler);
                  break;

              case 'Eth':
                  $noten["{N2}"] = $zeugnisNoten[$z]->getWertText();
                  break;

              case 'WR':
                  $noten["{N16}"] = $zeugnisNoten[$z]->getWertText();
                  break;

          }
      }



      foreach ($noten as $k => $v) {
          $templateProcessor->setValue($k, $v);
      }

      $latinum = "";

      if(schulinfo::isGymnasium()) {
          if($lateinNote != null && $lateinNote->getWert() <= 4 && $lateinNote->getWert() > 0) {
              if($schueler->getKlassenObjekt()->getKlassenstufe() == 8) {
                  $latinum = "Dieses Zeugnis bestätigt Lateinkenntnisse.";
              }
              if($schueler->getKlassenObjekt()->getKlassenstufe() == 9) {
                  $latinum = "Dieses Zeugnis schließt gesicherte Kenntnisse in Latein ein.";
              }
              if($schueler->getKlassenObjekt()->getKlassenstufe() == 10) {
                  $latinum = "Dieses Zeugnis schließt das Latinum gemäß der Vereinbarung der Kultusministerkonferenz vom 22. September 2005 ein.";
              }
          }


      }



      $templateProcessor->setValue("{LFS}", $lfs);
      $templateProcessor->setValue("{GRFS}", $grfs);
      $templateProcessor->setValue("{EFS}", $efs);
      $templateProcessor->setValue("{FFS}", $ffs);
      $templateProcessor->setValue("{RA}", $ra);
      $templateProcessor->setValue("{LATINUM}", $latinum);

      $templateProcessor->setValue("{SPFS}", $spfs);

      $fileUpload = FileUpload::generateUploadID($zeugnis->getArt() . " - " . $schueler->getCompleteSchuelerName() . ".docx", "docx", true, false);


      $templateProcessor->saveAs($fileUpload['uploadobject']->getFilePath());

      DB::getDB()->query("INSERT INTO noten_zeugnis_exemplar (zeugnisID, schuelerAsvID, uploadID, createdTime) values('" . $zeugnis->getID() . "','" . $schueler->getAsvID() . "','" . $fileUpload['uploadobject']->getID() . "',UNIX_TIMESTAMP())


        ON DUPLICATE KEY UPDATE uploadID='" . $fileUpload['uploadobject']->getID() . "', createdTime=UNIX_TIMESTAMP()");
  }

  private function getFremdspracheNummer($fach, $schueler) {
      $data = DB::getDB()->query_first("SELECT * FROM schueler_fremdsprache WHERE schuelerAsvID='" . $schueler->getAsvID() . "' AND spracheFach='" . $fach . "'");
      return $data['spracheSortierung'] . ". Fremdsprache";
  }

  /**
   *
   * @param NoteZeugnis $zeugnis
   */
  private function zeugnisDruckIndex($zeugnis) {
      $zeugnisKlassen = $zeugnis->getZeugnisKlassen();

      $listKlassen = "";

      for($i = 0; $i < sizeof($zeugnisKlassen); $i++) {

          $listKlassen .= "<a href=\"index.php?page=NotenverwaltungZeugnisse&action=printZeugnis&zeugnisID=" . $zeugnis->getID() . "&mode=viewKlasse&klasse=" . urlencode($zeugnisKlassen[$i]->getKlasse()->getKlassenName()) . "\"><i class=\"fa fa-group\"></i> Klasse " . $zeugnisKlassen[$i]->getKlasse()->getKlassenName() . "</a><br />";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/zeugnisse/druck/index/index") . "\");");
  }

  private function addZeugnis() {
      $typ = $_POST['zeugnisTyp'];
      $name = $_POST['zeugnisName'];
      $zeugnisTemplate = $_POST['zeugnisTemplate'];

      DB::getDB()->query("INSERT INTO noten_zeugnisse (zeugnisName, zeugnisArt, zeugnisTemplate)
        values('" . DB::getDB()->escapeString($name) . "','" . DB::getDB()->escapeString($typ) . "','" . DB::getDB()->escapeString($zeugnisTemplate) . "')");
      $newID = DB::getDB()->insert_id();

      $klassen = klasse::getAllKlassen();

      for($i = 0; $i < sizeof($klassen); $i++) {
          if($_POST[$klassen[$i]->getKlassenName() . "_checked"] > 0) {
              $datum = DateFunctions::getMySQLDateFromNaturalDate($_POST[$klassen[$i]->getKlassenName() . "_datum"]);
              $notenschluss = DateFunctions::getMySQLDateFromNaturalDate($_POST[$klassen[$i]->getKlassenName() . "_notenSchluss"]);

              $kl = $_POST[$klassen[$i]->getKlassenName() . "_kl"];
              $sl = $_POST[$klassen[$i]->getKlassenName() . "_sl"];

              $klGez = $_POST[$klassen[$i]->getKlassenName() . "_kl_gez"];
              $slGez = $_POST[$klassen[$i]->getKlassenName() . "_sl_gez"];

              DB::getDB()->query("INSERT INTO noten_zeugnisse_klassen
                    (
                        zeugnisID,
                        zeugnisKlasse,
                        zeugnisDatum,
                        zeugnisNotenschluss,
                        zeugnisUnterschriftKlassenleitungAsvID,
                        zeugnisUnterschriftSchulleitungAsvID,
                        zeugnisUnterschriftKlassenleitungAsvIDGezeichnet,
                        zeugnisUnterschriftSchulleitungAsvIDGezeichnet
                    ) values (
                        '" . $newID . "',
                        '" . DB::getDB()->escapeString($klassen[$i]->getKlassenName()) . "',
                        '" . DB::getDB()->escapeString($datum) . "',
                        '" . DB::getDB()->escapeString($notenschluss) . "',
                        '" . DB::getDB()->escapeString($kl) . "',
                        '" . DB::getDB()->escapeString($sl) . "',
                        " . (int)DB::getDB()->escapeString($klGez) . ",
                        " . (int)DB::getDB()->escapeString($slGez) . "
                    )
                ");
          }
      }

      header("Location: index.php?page=NotenverwaltungZeugnisse");
      exit();
  }

  private function index() {
      $zeugnisse = NoteZeugnis::getAll();

      $zeugnisListe = "";
      for($i = 0; $i < sizeof($zeugnisse); $i++) {
          $zeugnisListe .= "<tr><td>" . $zeugnisse[$i]->getArtName() . "</td><td>";

          $klassen = $zeugnisse[$i]->getZeugnisKlassen();

          $klassenSelectOptions = [];

          for($k = 0; $k < sizeof($klassen); $k++) {
              $zeugnisListe .= "<b>" . $klassen[$k]->getKlasse()->getKlassenName() . "</b> - " . DateFunctions::getNaturalDateFromMySQLDate($klassen[$k]->getDatumAsSQLDate()) . " - Notenschluss: " . DateFunctions::getNaturalDateFromMySQLDate($klassen[$k]->getNotenschulussAsSQLDate()) . "<br />";

              $zeugnisListe .= "KL: " . ($klassen[$k]->getKlassenleitung() != null ? $klassen[$k]->getKlassenleitung()->getDisplayNameMitAmtsbezeichnung() : "n/a") . "<br />";
              $zeugnisListe .= "SL: " . ($klassen[$k]->getSchulleitung() != null ? $klassen[$k]->getSchulleitung()->getDisplayNameMitAmtsbezeichnung() : "n/a") . "<br />";

              $klassenSelectOptions[] = "<option value=\"" . $klassen[$k]->getID() . "\">" . $klassen[$k]->getKlasse()->getKlassenName() . "</option>";
          }


          $zeugnisListe .= "</td>";
          $zeugnisListe .= "<td>";

          $zeugnisListe .= "<div class='btn-group'><button type=\"buton\" class=\"btn btn-primary\" onclick=\"window.location.href='index.php?page=NotenverwaltungZeugnisse&action=printZeugnis&zeugnisID=" . $zeugnisse[$i]->getID() . "'\"><i class=\"fa fa-print\"></i> Zeugnisse drucken</button>";
          $zeugnisListe .= '<button type="button" class="btn btn-danger" onclick="confirmAction(\'Zeugnis wirklich löschen? (WARNUNG: Lösche alle Bermerkungen, Zeugnisnoten etc.)\',\'index.php?page=NotenverwaltungZeugnisse&action=deleteZeugnis&zeugnisID=' . $zeugnisse[$i]->getID() . '\');"><i class="fa fa-trash"></i></button></div>';

          $zeugnisListe .= "<hr>";

          $zeugnisListe .= "<p><b>Zeugnisnoten Unter- und Mittelstufe für ASV exportieren</b></p>";

          $zeugnisListe .= "<form action=\"index.php?page=NotenverwaltungZeugnisse&action=exportUnterMittelstufeToASV&zeugnisID=" . $zeugnisse[$i]->getID() . "\" method=\"post\">";

          $zeugnisListe .= "<p><select name='zeugnis_typ' required>
                    <option disabled>Zeugnisart</option>
                    <option value='01'>Zwischenzeugnis (Alle Schularten)</option>
                    <option value='25'>Jahreszeugnis (Alle Schularten)</option>
                    <option value='11'>AA1 Zeugnis</option>
                    <option value='12'>AA2 Zeugnis</option>
                    <option value='13'>AA3 Zeugnis</option>
                    <option value='14'>AA4 Zeugnis</option>
                    </select></p>
                    
          ";
          
          $zeugnisListe .= "<p><button type='submit' class='btn btn-default'><i class=\"fa fa-download\"></i> Export für ASV</button></p>";
          $zeugnisListe .= "<small>Hinweis: Datei danach mit 7ZIP und einem Passwort zippen, damit es in ASV importiert werden kann.";

          $zeugnisListe .= "<hr>";

          $zeugnisListe .= "<a href=\"index.php?page=NotenverwaltungZeugnisse&action=exportOberstufe&zeugnisID=" . $zeugnisse[$i]->getID() . "&aa=1\" class='btn btn-default'><i class=\"fa fa-download\"></i> Oberstufenexport für ASV AA1</a>";
          $zeugnisListe .= "<a href=\"index.php?page=NotenverwaltungZeugnisse&action=exportOberstufe&zeugnisID=" . $zeugnisse[$i]->getID() . "&aa=2\" class='btn btn-default'><i class=\"fa fa-download\"></i> Oberstufenexport für ASV AA2</a>";
          $zeugnisListe .= "<a href=\"index.php?page=NotenverwaltungZeugnisse&action=exportOberstufe&zeugnisID=" . $zeugnisse[$i]->getID() . "&aa=3\" class='btn btn-default'><i class=\"fa fa-download\"></i> Oberstufenexport für ASV AA3</a>";
          $zeugnisListe .= "<a href=\"index.php?page=NotenverwaltungZeugnisse&action=exportOberstufe&zeugnisID=" . $zeugnisse[$i]->getID() . "&aa=4\" class='btn btn-default'><i class=\"fa fa-download\"></i> Oberstufenexport für ASV AA4</a>";



          $zeugnisListe .= "</form>";


          $zeugnisListe .= "<small>Hinweis: In der Oberstufe wird die Sportnote normal aus großen und kleinen Leistungsnachweisen gebildet. Diese Gesamtnote wird als \"Schnitt kleine Leistungsnachweise\" in die ASV importiert, weil die großen Leistungsnachweise von der ASV ignoriert werden.";

          $zeugnisListe . "</td>";




          $zeugnisListe .= "</tr>";
      }



      $klassenHTML = "";

      $klassen = klasse::getAllKlassen();

      $schulleitung = schulinfo::getSchulleitungLehrerObjects();

      $SL = null;
      if(sizeof($schulleitung) > 0) {
          $SL = $schulleitung[0];
      }

      $SLOptions = $this->getTeacherSelectOptions($SL);

      for($i = 0; $i < sizeof($klassen); $i++) {
          $klassenHTML .= "<tr><td><input type=\"checkbox\" class=\"icheck\" name=\"" . $klassen[$i]->getKlassenName() . "_checked\" value=\"1\"></td>";

          $klassenHTML .= "<td>" . $klassen[$i]->getKlassenName() . "</td>";

          $klassenHTML .= "<td><input type=\"text\" class=\"dateSelectDatum\" name=\"" . $klassen[$i]->getKlassenName() . "_datum\"></td>";
          $klassenHTML .= "<td><input type=\"text\" class=\"dateSelectDatumNotenschluss\" name=\"" . $klassen[$i]->getKlassenName() . "_notenSchluss\"></td>";

          // Klassenleitung

          $klassenleitung = $klassen[$i]->getKlassenLeitung();

          $KL = null;
          if(sizeof($klassenleitung) > 0) $KL = $klassenleitung[0];


          $klassenHTML .= "<td><select name=\"" . $klassen[$i]->getKlassenName() . "_kl\" class=\"form-control\">" . $this->getTeacherSelectOptions($KL) . "</select>
                <label><input type=\"checkbox\" class=\"icheck\" name=\"" . $klassen[$i]->getKlassenName() . "_kl_gez\" value=\"1\"> i.A. drucken</label>
            </td>";

          // Schulleitung

          $klassenHTML .= "<td><select name=\"" . $klassen[$i]->getKlassenName() . "_sl\" class=\"form-control\">" . $SLOptions . "</select>
                <label><input type=\"checkbox\" class=\"icheck\" name=\"" . $klassen[$i]->getKlassenName() . "_sl_gez\" value=\"1\"> i.A. drucken</label>
            </td>";



          $klassenHTML .= "</tr>";
      }

      $optionsTemplate = '';
      $path = PATH_VORLAGEN.'notenverwaltung/zeugnisse/';
      if (is_dir($path)) {
          $files = array_diff(scandir($path), array('..', '.'));
          foreach ($files as $file) {
              $optionsTemplate .= '<option>'.$file.'</option>';
          }
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/zeugnisse/index") . "\");");
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

  private function zwischenbericht() {
      $checkBoxesKlassen = "";

      $klassen = klasse::getAllKlassen();

      for($i = 0; $i < sizeof($klassen); $i++) {
          $checkBoxesKlassen .= "<label><input type=\"checkbox\" name=\"klasse_" . $klassen[$i]->getKlassenName() . "\" value=\"1\" checked=\"checked\"> " . $klassen[$i]->getKlassenName() . "</label><br />";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/zeugnisse/zb") . "\");");
      
  }

  private function printZwischenbericht() {
      // Datum:
      $datum = $_REQUEST['datum'];

      $schulleitung = schulinfo::getSchulleitungLehrerObjects();
      if(sizeof($schulleitung) > 0) $schulleitung = $schulleitung[0];
      else new errorPage("Es ist keine Schulleitung in den Schulinformationen definiert.");

      $nameSchulleitung = $schulleitung->getDisplayNameMitAmtsbezeichnung();

      $titel = "<font size=\"12\">" . DB::getSettings()->getValue('schulinfo-name') . "</font><br />";
      if(DB::getSettings()->getValue('schulinfo-name-zusatz') != "") $titel .= "<font size=\"12\"><b>" . DB::getSettings()->getValue('schulinfo-name-zusatz') . "</b></font><br />";
      $titel .= DB::getSettings()->getValue('schulinfo-adresse1') . ", " . DB::getSettings()->getValue('schulinfo-plz') . " " . DB::getSettings()->getValue('schulinfo-ort') . "<br /><br />";

      $schulort = DB::getSettings()->getValue('schulinfo-ort');

      $klassen = klasse::getAllKlassen();

      $print = new PrintNormalPageA4WithoutHeader('Zwischenberichte');

      for($i = 0; $i < sizeof($klassen); $i++) {
          if($_POST['klasse_' . $klassen[$i]->getKlassenName()] > 0) {
              $schueler = $klassen[$i]->getSchueler();

              $klassenleitung = $klassen[$i]->getKlassenLeitung();
              if(sizeof($klassenleitung) > 0) $klassenleitung = $klassenleitung[0]->getDisplayNameMitAmtsbezeichnung();
              else $klassenleitung = "n/a";

              for($s = 0; $s < sizeof($schueler); $s++) {
                  $notenbogen = new Notenbogen($schueler[$s]);

                  $hasNA = $schueler[$s]->getNachteilsausgleich() != null;


                  $tabelle = $notenbogen->getNotentabelleZwischenbericht();

                  $schuelerName = $schueler[$s]->getCompleteSchuelerName();
                  $klasse = $klassen[$i]->getKlassenName();

                  $geburtsdatum = $schueler[$s]->getGeburtstagAsNaturalDate();

                  $absenzen = $notenbogen->getAbsenzen();

                  eval("\$html = \"" . DB::getTPL()->get("notenverwaltung/zeugnisse/druck/zwischenbericht") . "\";");

                  $print->setHTMLContent($html);

                  // if($s == 2) break;

              }

          }
      }

      $print->send();
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
