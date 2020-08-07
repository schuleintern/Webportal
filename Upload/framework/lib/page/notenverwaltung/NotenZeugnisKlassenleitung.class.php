<?php

class NotenZeugnisKlassenleitung extends AbstractPage {

    /**
     * 
     * @var SchuelerUnterricht[]
     */
  private $unterrichte = [];
  

  /**
   * 
   * @var NoteZeugnis
   */
  private $zeugnis = null;
  
  
  /**
   * 
   * @var klasse
   */
  private $klasse = null;


  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }
    
    parent::__construct(['Notenverwaltung', 'Zeugnis', 'Klassenleitung'],false,false,true);

    if(!DB::isLoggedIn() || !DB::getSession()->isTeacher()) {
      new errorPage();
    }

    $zeugnisID = intval($_REQUEST['zeugnisID']);
    
    $this->zeugnis = NoteZeugnis::getByID($zeugnisID);
    
    if($this->zeugnis == null) {
        new errorPage('Kein gültiges Zeugnis angegeben!');
    }
    
    if($_REQUEST['klasse'] != "") {
        $this->klasse = klasse::getByName($_REQUEST['klasse']);
    }
    
  }

  public function execute() {
      switch($_REQUEST['action']) {      
          default:
              $this->index();
          break;
          
          case 'showGrade':
              $this->showGrade();
          break;

          case 'confirmZeugnisNote':
              $this->confirmZeugnisNote();
          break;
          
          case 'getNotenBogenJSON':
              $this->getNotenbogenJSON();
          break;
      }    
      
  }
  
  private function getNotenBogenJSON() {
      header("Content-type: text/json");
      
      
      $schueler = $this->klasse->getSchueler(true);
      
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
  
  /**
   * 
   * @param klasse $klasse
   */
  private function checkGradeAccess($klasse) {
      if($klasse == null) return false;
      
      if(DB::getSession()->isAdmin() || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isMember(self::getAdminGroup())) {
          $klassenMitKlassenleitung = klasse::getAllKlassen();          
      }
      else {
          $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
      }
      
      for($i = 0; $i < sizeof($klassenMitKlassenleitung); $i++) {
          if($klassenMitKlassenleitung[$i]->getKlassenName() == $klasse->getKlassenName()) return true;
      }
            
      return false;
  }

  private function confirmZeugnisNote() {
      if($this->klasse == null) new errorPage("Keine Klasse angegeben!");

      if(!$this->checkGradeAccess($this->klasse)) {
          new errorPage("Kein Zugriff!");
      }

      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
      $zeugnis = NoteZeugnis::getByID($_REQUEST['zeugnisID']);
      $fach = fach::getByKurzform($_REQUEST['fach']);

      $_REQUEST['noteKommentar'] .= "\r\n" . "Bestätigt durch Klassenleitung " . DB::getSession()->getUser()->getTeacherObject()->getKuerzel();

      if($_REQUEST['deleteIt'] > 0) {
          NoteZeugnisNote::deleteNoteForSchuelerAndFach($schueler, $zeugnis, $fach);
      }
      else {
          NoteZeugnisNote::setNoteForSchuelerAndZeugnisAndFach($schueler, $zeugnis, $fach, $_REQUEST['notenWert'], $_REQUEST['noteKommentar'], $_REQUEST['normalCalcNote'] != $_REQUEST['notenWert']);
      }

      header("Location: index.php?page=NotenZeugnisKlassenleitung&zeugnisID=" . $zeugnis->getID() . "&action=showGrade&klasse=" . $schueler->getKlasse() . "&selectSchueler=" . $schueler->getAsvID());
      exit(0);

  }
  
  private function showGrade() {

      if($this->klasse == null) new errorPage();

      if(!$this->checkGradeAccess($this->klasse)) {
          new errorPage("Kein Zugriff!");
      }
      
      
      if(DB::getSession()->isAdmin() || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isMember(self::getAdminGroup())) {
          $klassenMitKlassenleitung = klasse::getAllKlassen();
      }
      else {
          $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
      }
      
      
      $gradeChangeAble = sizeof($klassenMitKlassenleitung) > 1;
      
      $gradeSelect = "";
      
      for($i = 0; $i < sizeof($klassenMitKlassenleitung); $i++) {
          $gradeSelect .= "<option value=\"" . $klassenMitKlassenleitung[$i]->getKlassenName() . "\"" . (($this->klasse->getKlassenName() == $klassenMitKlassenleitung[$i]->getKlassenName()) ? ("selected") : ("")) . ">" . $klassenMitKlassenleitung[$i]->getKlassenName() . "</option>";
      }
      
      
      $schueler = $this->klasse->getSchueler(true);
      
      $activeSchuler = null;
      
      if($_REQUEST['selectSchueler'] != "") {
          for($i = 0; $i < sizeof($schueler); $i++) {
              if($schueler[$i]->getAsvID() == $_REQUEST['selectSchueler']) {
                  $activeSchuler = $schueler[$i];
                  break;
              }
          }
      }
      
      if($activeSchuler == null) {
          $activeSchuler = $schueler[0];
      }
      
      
      if($activeSchuler == null) new errorPage();
      
      if($_REQUEST['mode'] == 'saveBemerkung') {
          NoteZeugnisBemerkung::setText1($_REQUEST['text1'], $activeSchuler, $this->zeugnis);
          NoteZeugnisBemerkung::setText2($_REQUEST['text2'], $activeSchuler, $this->zeugnis);

          if($_POST['klassenziel'] == 1) {
              NoteZeugnisBemerkung::setKlassenzielErreicht(true, $activeSchuler, $this->zeugnis);
          }
          elseif($_POST['klassenziel'] == 2) {
              NoteZeugnisBemerkung::setVorrueckenAufProbe(true, $activeSchuler, $this->zeugnis);
          }
          else {
              NoteZeugnisBemerkung::setKlassenzielErreicht(false, $activeSchuler, $this->zeugnis);
          }

          header("Location: index.php?page=NotenZeugnisKlassenleitung&zeugnisID=" . $this->zeugnis->getID() . "&action=showGrade&klasse=" . $this->klasse->getKlassenName() . "&selectSchueler=" . $activeSchuler->getAsvID() . "&saved=1");
          exit();
      }
      
      $mvTabelle = "";
      
      $schuelerUnterricht = SchuelerUnterricht::getUnterrichtForSchueler($activeSchuler);
      
      $mvTabelle .= "<tr><th>&nbsp;</th>";
      for($i = 0; $i < sizeof($schuelerUnterricht); $i++) {
          $mvTabelle .= "<th>" .  $schuelerUnterricht[$i]->getFach()->getKurzform() . " (" . $schuelerUnterricht[$i]->getLehrer()->getKuerzel() . ")</th>";
      }
      $mvTabelle .= "<th>&Oslash;</th>";
      $mvTabelle .= "</tr>";
      
      $mvTabelle .= "<tr><td><b>Mitarbeit</b></td>";
      
      $summe = 0;
      $anzahl = 0;
      
      for($i = 0; $i < sizeof($schuelerUnterricht); $i++) {
          $mvNote = MV::getByUnterrichtAndSchueler($schuelerUnterricht[$i], $activeSchuler, $this->zeugnis);
          
          
          if($mvNote != null && $mvNote->getMNote() > 0) {
              $color = Note::getNotenColor($mvNote->getMNote());
              $mvTabelle .= "<td><font color=\"$color\" size=\"+2\">" . $mvNote->getMNote() . "</td>";
              $summe += $mvNote->getMNote();
              $anzahl++;
          }
          else if($mvNote != null && $mvNote->getMNote() == 0) {
              $mvTabelle .= "<td>k.A.</td>";
          }
          else {
              $mvTabelle .= "<td>--</td>";
          }
      }
      
      $mNote = 0;
      
      if($summe > 0) {
          $mNote = NotenCalculcator::NoteRunden($summe / $anzahl);
          $mvTabelle .= "<td><font size=\"+2\"><b>" . number_format($mNote,2,",",".")  . "</b></td>";
      }
      else {
          $mvTabelle .= "<td>--</td>";
      }
      
      $mvTabelle .= "</tr>";
      
      
      $mvTabelle .= "<tr><td><b>Verhalten</b></td>";
      
      $summe = 0;
      $anzahl = 0;
      
      for($i = 0; $i < sizeof($schuelerUnterricht); $i++) {
          $mvNote = MV::getByUnterrichtAndSchueler($schuelerUnterricht[$i], $activeSchuler, $this->zeugnis);
          
          
          if($mvNote != null && $mvNote->getVNote() > 0) {
              $color = Note::getNotenColor($mvNote->getVNote());
              $mvTabelle .= "<td><font color=\"$color\" size=\"+2\">" . $mvNote->getVNote() . "</td>";
              $summe += $mvNote->getVNote();
              $anzahl++;
          }
          else if($mvNote != null && $mvNote->getVNote() == 0) {
              $mvTabelle .= "<td>k.A.</td>";
          }
          else {
              $mvTabelle .= "<td>--</td>";
          }
      }
      
      $vNote = 0;
      
      if($summe > 0) {
          $vNote = NotenCalculcator::NoteRunden($summe / $anzahl);
          $mvTabelle .= "<td><font size=\"+2\"><b>" . number_format($vNote,2,",",".")  . "</b></td>";
      }
      else {
          $mvTabelle .= "<td>--</td>";
      }
      
      $mvTabelle .= "</tr>";
      
      
      $mvTabelle .= "<tr><td><b>Kommentar</b></td>";
      
      $summe = 0;
      $anzahl = 0;
      
      for($i = 0; $i < sizeof($schuelerUnterricht); $i++) {
          $mvNote = MV::getByUnterrichtAndSchueler($schuelerUnterricht[$i], $activeSchuler, $this->zeugnis);
          
          
          if($mvNote != null && $mvNote->getKommentar() != "") {
              $mvTabelle .= '<td><button type="button" class="btn btn-xs" data-toggle="tooltip" data-html="true" title="' . $mvNote->getKommentar() . '"><i class="fa fa-file"></i></button></td>';
          }
              
          else {
              $mvTabelle .= "<td>--</td>";
          }
      }
      
      $mvTabelle .= "</tr>";
      
      
      
      // Zeugnisnoten

      // Alle Noten laden
      $notenbogen = new Notenbogen($activeSchuler);
      $unterrichtMitNoten = $notenbogen->getUnterricht();
      $unterrichtMitNotenNoten = $notenbogen->getUnterrichtsNoten();

      $notenTabelle .= "<tr>";

      for($i = 0; $i < sizeof($unterrichtMitNoten); $i++) {
          $notenTabelle .= "<td style='text-align: center'><b>" . $unterrichtMitNoten[$i]->getFach()->getKurzform() . "</b></td>";
      }

      $notenTabelle .= "</tr>";
      $notenTabelle .= "<tr>";

      for($i = 0; $i < sizeof($unterrichtMitNoten); $i++) {
            $notenTabelle .= "<td style='text-align: center'>&oslash;  " . $unterrichtMitNotenNoten[$i]->getSchnittFormated() . "</td>";
      }

      $notenTabelle .= "</tr>";



      $zeugnisNoten = NoteZeugnisNote::getZeugnisNotenForSchueler($this->zeugnis, $activeSchuler);

      
      $anzahlFuenf = 0;
      $anzahlSechs = 0;
      
      $notenTabelle .= "<tr>";

      for($u = 0; $u < sizeof($unterrichtMitNoten); $u++) {

          $found = false;

          for ($i = 0; $i < sizeof($zeugnisNoten); $i++) {
              if($zeugnisNoten[$i]->getFach()->getKurzform() == $unterrichtMitNoten[$u]->getFach()->getKurzform()) {
                  $found = true;
                  if ($zeugnisNoten[$i]->getWert() > 0) {
                      $farbe = Note::getNotenColor($zeugnisNoten[$i]->getWert());

                      if ($zeugnisNoten[$i]->getWert() == 5) {
                          $anzahlFuenf++;
                      }

                      if ($zeugnisNoten[$i]->getWert() == 6) {
                          $anzahlSechs++;
                      }

                      $notenTabelle .= "<td style='text-align: center'><font color=\"" . $farbe . "\" size=\"+2\">" . $zeugnisNoten[$i]->getWert() . "</font></td>";
                  } else {
                      $notenTabelle .= "<td style='text-align: center'>--</td>";
                  }
                  break;
              }
          }

          if(!$found) {
              $notenTabelle .= "<td style='text-align: center; color: #ff0000; font-size: 14pt'>--<br /><i class='fa fa-exclamation-circle'></i></td>";
          }
      }

      
      $notenTabelle .= "</tr>";

      $notenTabelle .= "<tr>";

      for($u = 0; $u < sizeof($unterrichtMitNoten); $u++) {

          $zeugnisNote = "";
          $kommentar = "";

          for ($i = 0; $i < sizeof($zeugnisNoten); $i++) {
              if ($zeugnisNoten[$i]->getFach()->getKurzform() == $unterrichtMitNoten[$u]->getFach()->getKurzform()) {
                  $zeugnisNote = $zeugnisNoten[$i]->getWert();
                  $kommentar = $zeugnisNoten[$i]->getPaedBegruendung();
              }
          }

          $schnitt = $unterrichtMitNotenNoten[$u]->getSchnitt();
          $schnittFormated = $unterrichtMitNotenNoten[$u]->getSchnittFormated();

          $notenTabelle .= "<td><button class='btn btn-default btn-xs btn-block' onclick=\"
                confirmNote(
                    '{$activeSchuler->getAsvID()}',
                    '{$this->zeugnis->getID()}',
                    '{$unterrichtMitNoten[$u]->getFach()->getKurzform()}',
                    '" . addslashes(str_replace("\r","",str_replace("\n","",$kommentar))) . "',
                    '{$zeugnisNote}',
                    '" . round($schnitt, 0, PHP_ROUND_HALF_DOWN) . "',
                    '$schnittFormated')\"><i class='fa fa-check'></i></button>";

      }

      $notenTabelle .= "</tr>";




      // /Zeugnisnoten
      
      
      // Nachteilsausgleich
      
      $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($activeSchuler);
      
      $hasNa = false;
      if($na != null) {
          $hasNa = true;
          $naText = $na->getInfoString();
      }
      
      // /Nachteilsausgleich
      
      
      // Wahlfächer
            
      
      $wahlfaecher = NoteWahlfachNote::getForSchueler($activeSchuler, $this->zeugnis);
      
      $wahlfachtabelle = "<tr>";
      for($i = 0; $i < sizeof($wahlfaecher); $i++) {
          $wahlfachtabelle .= "<th>" . $wahlfaecher[$i]->getWahlfach()->getBezeichnung() . " (" . $wahlfaecher[$i]->getWahlfach()->getUnterricht()->getLehrer()->getKuerzel() . ")</th>";
      }
      $wahlfachtabelle .= "</tr><tr>";
      for($i = 0; $i < sizeof($wahlfaecher); $i++) {
          $wahlfachtabelle .= "<td>" . $wahlfaecher[$i]->getErfolgText() . "</td>";
      }
      $wahlfachtabelle .= "</tr>";
      
      
      if(sizeof($wahlfaecher) == 0) $wahlfachtabelle .= "<tr><th>keine</th></tr>";
      
      // /Wahlfächer
      
      
      // Bemerkung
      
      $bemerkung = NoteZeugnisBemerkung::getForSchueler($activeSchuler, $this->zeugnis);
      
      
      $text1 = "";
      if($bemerkung != null) {
          $text1 = $bemerkung->getText1();
      }
      else {
          $text1 = NoteZeugnisBemerkung::getDefaultText1($activeSchuler,$this->zeugnis);
      }
      
      $text2 = "";
      if($bemerkung != null) {
          $text2 = $bemerkung->getText2();
      }
      else {
          $text2 = NoteZeugnisBemerkung::getDefaultText2($activeSchuler,$this->zeugnis);
      }
      
      $vNote = round($vNote, 0, PHP_ROUND_HALF_DOWN);
      $mNote = round($mNote, 0, PHP_ROUND_HALF_DOWN);
      
      // Textbausteine
      
      $buttonsBausteine = "";
      
      $bemerkungGruppen = NoteBemerkungGruppe::getAll();
      
      for($i = 0; $i < sizeof($bemerkungGruppen); $i++) {
          $buttonsBausteine .= "<div class=\"form-group\"><label>" . $bemerkungGruppen[$i]->getName() . "</label>";
          
          $buttonsBausteine .= "<select class=\"form-control\" onchange=\"addText($('#text1'),this.id);\" id=\"bem_" . $bemerkungGruppen[$i]->getID() . "\">";
          
          $bausteine = $bemerkungGruppen[$i]->getTexte();
          

          
          for($b = 0; $b < sizeof($bausteine); $b++) {
              
              
              $hinweis = "";
              
              if($bemerkungGruppen[$i]->isMitarbeit() && $bausteine[$b]->getNote() == $mNote) {
                  $hinweis = "X ";
              }
              
              if($bemerkungGruppen[$i]->isVerhalten() && $bausteine[$b]->getNote() == $vNote) {
                  $hinweis = "X ";
              }
              
              $buttonsBausteine .= "<option value=\"" . addslashes(htmlspecialchars($bausteine[$b]->getTextForSchueler($activeSchuler))) . " \">" . $hinweis . $bausteine[$b]->getNote() . ": " . htmlspecialchars($bausteine[$b]->getTextForSchueler($activeSchuler)) . "</option>";
          }
          
          $buttonsBausteine .= "</select></div>";
      }
      
      $selectedErreicht = "";
      $selectedNichtErreicht = "";
      
      if($bemerkung != null) {
          if($bemerkung->klassenzielErreicht()) $selectedErreicht = " selected";
          elseif($bemerkung->vorrueckenAufProbe()) $selectedVorrueckenAufProbe = " selected";
          else $selectecNichtErreicht = " selected";
      }
      
      
      $schuelerTabelle = "";
      
      for($i = 0; $i < sizeof($schueler); $i++) {
          $active = "";
          
          if($schueler[$i]->getAsvID() == $activeSchuler->getAsvID()) $active = " style=\"background-color: #CECECE\"";
          
          $schuelerTabelle .= "<tr><td style='text-align: left; width: 20px'>" . ($i+1) . "</td><td$active><a href=\"index.php?page=NotenZeugnisKlassenleitung&zeugnisID=" . $this->zeugnis->getID() . "&action=showGrade&klasse=" . $this->klasse->getKlassenName() . "&selectSchueler=" . $schueler[$i]->getAsvID() . "\">" . $schueler[$i]->getCompleteSchuelerName() . "</a></td>

                <td style='text-align: right; width: 20px'>";

          if($schueler[$i]->getGeschlecht() == "w") $schuelerTabelle .= "<i class='fa fa-female'></i>";
          if($schueler[$i]->getGeschlecht() == "m") $schuelerTabelle .= "<i class='fa fa-male'></i>";

          $schuelerTabelle .= "</td>


                </tr>";
      }


      // Niveaustufen
      $niveaustufen = new NotenFremdsprachenNiveaustufen($activeSchuler->getKlassenObjekt()->getKlassenstufe(), $zeugnisNoten);

      $niveauText = $niveaustufen->getText();

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/klassenleitung/zeugnis") . "\");");
      
  }
  
  
  public function index() {
      // Klassen anzeigen
      
      if(DB::getSession()->isAdmin() || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isMember(self::getAdminGroup())) {
          $klassenMitKlassenleitung = klasse::getAllKlassen();
      }
      else {
          $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
      }
      
      
      if(sizeof($klassenMitKlassenleitung) > 0) {
          header("Location: index.php?page=NotenZeugnisKlassenleitung&zeugnisID=" . $this->zeugnis->getID() . "&action=showGrade&klasse=" . $klassenMitKlassenleitung[0]->getKlassenName());
          exit(0);
      }
      else {
          echo($this->header);
          echo("Keine Klassenleitung");
          echo($this->footer);
          exit(0);
      }
      
      
  }
  
  public static function hasSettings() {
    return false;
  }

  public static function getSettingsDescription() {
    return [];
  }

  public static function getSiteDisplayName() {
    return 'Notenverwaltung - Zeugnis Klassenleitung';
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
