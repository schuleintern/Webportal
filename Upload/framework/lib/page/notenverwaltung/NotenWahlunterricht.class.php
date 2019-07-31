<?php

class NotenWahlunterricht extends AbstractPage {

    /**
     * 
     * @var SchuelerUnterricht[]
     */
  private $unterrichte = [];
  
  /**
   * 
   * @var NoteWahlfach[]
   */
  private $wahlFaecher = [];

  /**
   * 
   * @var NoteZeugnis
   */
  private $zeugnis = null;


  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }


    parent::__construct(['Notenverwaltung', 'Zeugnis', 'Wahlunterricht'],false,false,true);

    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

    $zeugnisID = intval($_REQUEST['zeugnisID']);
    
    $this->zeugnis = NoteZeugnis::getByID($zeugnisID);
    
    if($this->zeugnis == null) {
        new errorPage('Kein gültiges Zeugnis angegeben!');
    }
    
    $this->unterrichte = SchuelerUnterricht::getWahlunterricht(DB::getSession()->getTeacherObject(), true);
    $this->wahlFaecher = NoteWahlfach::getAllWahlfachForTeacher(DB::getSession()->getTeacherObject(), $this->zeugnis);
  }

  public function execute() {
      switch($_REQUEST['action']) {
          case 'addUnterricht':
              $unterricht = SchuelerUnterricht::getByID($_REQUEST['unterrichtID']);
              if($unterricht != null) {
                  $found = false;
                  
                  for($w = 0; $w < sizeof($this->wahlFaecher); $w++) {
                      if($this->wahlFaecher[$w]->getUnterricht()->getID() == $unterricht->getID()) $found = true;
                  }
                  
                  if(!$found) {
                      NoteWahlfach::addUnterrichtAsWahlfachForZeugnis($unterricht, $this->zeugnis, $_POST['bezeichnung']);
                  }
              }
              
              header("Location: index.php?page=NotenWahlunterricht&zeugnisID=" . $this->zeugnis->getID());
              exit(0);
              
          break;
          
          case 'saveNoten':
              $this->saveNoten();
          break;
      
          default:
              $this->index();
          break;
      }    
      
  }
  
  private function saveNoten() {      
      for($i = 0; $i < sizeof($this->wahlFaecher); $i++) {
          if($this->wahlFaecher[$i]->getID() ==  $_REQUEST['wahlfachID']) {
              
              $schueler = $this->wahlFaecher[$i]->getUnterricht()->getSchueler();
              
              for($s = 0; $s < sizeof($schueler); $s++) {
                  NoteWahlfachNote::setNoteForSchueler($schueler[$s], $_POST['note_' . $schueler[$s]->getAsvID()], $this->wahlFaecher[$i]);
              }
              header("Location: index.php?page=NotenWahlunterricht&zeugnisID=" . $this->zeugnis->getID() . "&wahlunterrichtID=" . $this->wahlFaecher[$i]->getID() . "&notenSaved=1");
              
              exit(0);
              
          }
      }
      
      new errorPage("Unterricht unbekannt.");
  }
  
  
  private function index() {
      
      $optionsUnterricht = "";
      
      for($i = 0; $i < sizeof($this->unterrichte); $i++) {
          
          $found = false;
          for($w = 0; $w < sizeof($this->wahlFaecher); $w++) {
              if($this->wahlFaecher[$w]->getUnterricht()->getID() == $this->unterrichte[$i]->getID()) $found = true;
          }
          
          if(!$found) {
            if($this->unterrichte[$i]->isWahlunterricht()) {
                $optionsUnterricht .= "<option value=\"" . $this->unterrichte[$i]->getID() . "\">" . $this->unterrichte[$i]->getBezeichnung() . "</option>";
            }
          }
      }
      
           
      $tabs = "";
      
      $currentWahlunterricht = null;
      
      if(sizeof($this->wahlFaecher) > 0) {
          if($_REQUEST['wahlunterrichtID'] == "") {
              $currentWahlunterricht = $this->wahlFaecher[0];
          }
          else {
              for($i = 0; $i < sizeof($this->wahlFaecher); $i++) {
                  if($this->wahlFaecher[$i]->getID() == $_REQUEST['wahlunterrichtID']) {
                      $currentWahlunterricht = $this->wahlFaecher[$i];
                  }
              }
              
              if($currentWahlunterricht == null) {
                  $currentWahlunterricht = $this->wahlFaecher[0];
              }
          }
      }
      
      for($i = 0; $i < sizeof($this->wahlFaecher); $i++) {
          $tabs .= "<li" . ($this->wahlFaecher[$i]->getID() == $currentWahlunterricht->getID() ? (" class=\"active\"") : ("")) . "><a href=\"index.php?page=NotenWahlunterricht&zeugnisID=" . $this->zeugnis->getID() . "&wahlunterrichtID=" . $this->wahlFaecher[$i]->getID() . "\"><i class=\"fa fa-space-shuttle\"></i> " . $this->wahlFaecher[$i]->getBezeichnung() . "</a></li>";
      }
      
      
      if($currentWahlunterricht != null) {
          $schuelerHTML = "";
          
          $schueler = $currentWahlunterricht->getUnterricht()->getSchueler();
          
          
          for($i = 0; $i < sizeof($schueler); $i++) {
              $schuelerHTML .= "<tr><td>" . $schueler[$i]->getCompleteSchuelerName() . " (Klasse " . $schueler[$i]->getKlasse() . ")</td>";
              $schuelerHTML .= "<td>";
              
              $note = $currentWahlunterricht->getNoteForSchueler($schueler[$i]);
              
              $notenWert = 4;
              
              if($note != null) {
                $notenWert = $note->getNote();    
              }
              
              $schuelerHTML .= "<select name=\"note_" . $schueler[$i]->getAsvID() . "\" class=\"form-control\">";
              
              $schuelerHTML .= "<option value=\"1\"" . (($notenWert == 1) ? "selected" : "") . ">Sehr großer Erfolg (Note 1)</option>";
              $schuelerHTML .= "<option value=\"2\"" . (($notenWert == 2) ? "selected" : "") . ">großer Erfolg (Note 2)</option>";
              $schuelerHTML .= "<option value=\"3\"" . (($notenWert == 3) ? "selected" : "") . ">Erfolg (Note 3)</option>";
              $schuelerHTML .= "<option value=\"4\"" . (($notenWert == 4) ? "selected" : "") . ">(nur teilgenommen) (Note 4)</option>";
              $schuelerHTML .= "<option value=\"5\"" . (($notenWert == 5) ? "selected" : "") . ">(Nicht im Zeugnis) (Note 5 bzw. Schüler nicht angetreten.)</option>";
              
              
              $schuelerHTML .= "</select></td></tr>";
              
              
              
          }
          
          
      }
      
      
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/wahlfaecher/index") . "\");");
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
