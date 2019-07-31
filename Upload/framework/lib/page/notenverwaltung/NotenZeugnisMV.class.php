<?php

class NotenZeugnisMV extends AbstractPage {

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


  public function __construct() {

    if(!DB::getGlobalSettings()->hasNotenverwaltung) {
      die("Notenverwaltung nicht lizenziert.");
    }


    parent::__construct(['Notenverwaltung', 'Zeugnis', 'Mitarbeit / Verhalten'],false,false,true);

    if(!DB::getSession()->isTeacher()) {
      new errorPage();
    }

    $zeugnisID = intval($_REQUEST['zeugnisID']);
    
    $this->zeugnis = NoteZeugnis::getByID($zeugnisID);
    
    if($this->zeugnis == null) {
        new errorPage('Kein gültiges Zeugnis angegeben!');
    }
    
    $this->unterrichte = SchuelerUnterricht::getUnterrichtForLehrer(DB::getSession()->getTeacherObject(), true);    
  }

  public function execute() {
      switch($_REQUEST['action']) {
          case 'saveNoten':
              $this->saveNoten();
          break;
      
          default:
              $this->index();
          break;
      }    
      
  }
  
  private function saveNoten() {
      $showUnterricht = $_REQUEST['unterrichtID'];
      
      $uObject = null;

          for($i = 0; $i < sizeof($this->unterrichte); $i++) {
              if($this->unterrichte[$i]->getID() == $showUnterricht) {
                  $uObject = $this->unterrichte[$i];
                  break;
              }
          }
      
      if($uObject == null) {
          new errorPage('Keine Unterrichte vorhanden.');
      }
      
      $schueler = $uObject->getSchueler();
      
      $mvFach = MVFach::getByUnterrichtID($uObject, $this->zeugnis->getID());
      
      for($i = 0; $i < sizeof($schueler); $i++) {
          
          $mNote = 0;
          
          if($_POST['m_' . $schueler[$i]->getAsvID()] >= 0 && $_POST['m_' . $schueler[$i]->getAsvID()] <= 6) {
              $mNote = $_POST['m_' . $schueler[$i]->getAsvID()];
          }
          
          $vNote = 0;
          
          if($_POST['v_' . $schueler[$i]->getAsvID()] >= 0 && $_POST['v_' . $schueler[$i]->getAsvID()] <= 6) {
              $vNote = $_POST['v_' . $schueler[$i]->getAsvID()];
          }
          
          
          $kommentar = $_POST['k_' . $schueler[$i]->getAsvID()];
          
          $mvFach->setNoteForSchueler($schueler[$i], $mNote, $vNote, $kommentar);
      }
      
      header("Location: index.php?page=NotenZeugnisMV&zeugnisID=" . $this->zeugnis->getID() . "&unterrichtID=" . $uObject->getID());
      exit(0);
      
      
  }
  
  private function index() {
      $showUnterricht = $_REQUEST['unterrichtID'];
     
      $uObject = null;
      
      if($showUnterricht == '') {
          $uObject = $this->unterrichte[0];
      }
      else {
          for($i = 0; $i < sizeof($this->unterrichte); $i++) {
              if($this->unterrichte[$i]->getID() == $showUnterricht) {
                  $uObject = $this->unterrichte[$i];
                  break;
              }
          }
      }   
      
      if($uObject == null) {
          new errorPage('Keine Unterrichte vorhanden.');
      }
      
      
      $tabs = "";
      
      for($i = 0; $i < sizeof($this->unterrichte); $i++) {
          if($this->unterrichte[$i]->isPflichtunterricht()) {
              
              $check = "<font color=\"red\"><i class=\"fa fa-ban\"></i></font> ";
    
              $mvNoten = MVFach::getByUnterrichtID($this->unterrichte[$i], $this->zeugnis->getID());
              
              if($mvNoten != null && $mvNoten->isAllSet()) $check = "<font color=\"green\"><i class=\"fa fa-check\"></i></font> ";
              
              $tabs .= "<li " . (($this->unterrichte[$i]->getID() == $uObject->getID()) ? (" class=\"active\"") : ("")) . "><a href=\"index.php?page=NotenZeugnisMV&zeugnisID=" . $this->zeugnis->getID() . "&unterrichtID=" . $this->unterrichte[$i]->getID() . "\">$check <i class=\"fa fa-group\"></i> " . $this->unterrichte[$i]->getFach()->getKurzform() . " (" . $this->unterrichte[$i]->getAllKlassenAsList() . ")</a></li>";
          
          }
      }
      
      
      
      $notenMV = MVFach::getByUnterrichtID($uObject, $this->zeugnis->getID());
      
//       Debugger::debugObject($notenMV,1);
      
      $schueler = $uObject->getSchueler();
      $htmlSchueler = "";
      
      $tabIndexM = 1;
      $tabIndexV = sizeof($schueler) + 1;
      $tabIndexK = sizeof($schueler)*2 + 1;
      
      for($i = 0; $i < sizeof($schueler); $i++) {
          $htmlSchueler .= "<tr><td>" . $schueler[$i]->getCompleteSchuelerName() . "</td>";
          
          $mvNote = $notenMV->getNoteForSchueler($schueler[$i]);
          
          $presetM = "";
          $presetV = "";
          $presetKommentar = "";
          
          if($mvNote != null) {
              $presetM = $mvNote->getMNote();
              $presetV = $mvNote->getVNote();
              $presetKommentar = $mvNote->getKommentar();
          }
          
          $tabIndex++;
          
          $htmlSchueler .= "<td><input type=\"text\" id=\"input" . $tabIndexM . "\" tabindex=\"" . $tabIndexM . "\" onkeyup=\"jumpNextTabIndex(" . $tabIndexM . ")\" placeholder=\"1..6 oder 0\" class=\"form form-control\" name=\"m_" . $schueler[$i]->getAsvID() . "\" value=\"" . $presetM . "\" pattern=\"[0-6]\"></td>";
          $htmlSchueler .= "<td><input type=\"text\" id=\"input" . $tabIndexV . "\" tabindex=\"" . $tabIndexV . "\" onkeyup=\"jumpNextTabIndex(" . $tabIndexV . ")\" placeholder=\"1..6 oder 0\" class=\"form form-control\" name=\"v_" . $schueler[$i]->getAsvID() . "\" value=\"" . $presetV . "\" pattern=\"[0-6]\"></td>";
          
          $htmlSchueler .= "<td><input type=\"text\" tabindex=\"" . $tabIndexK . "\" placeholder=\"z.B. 1 Verweis\" class=\"form form-control\" name=\"k_" . $schueler[$i]->getAsvID() . "\" value=\"" . $presetKommentar . "\"></td>";
          
          $htmlSchueler .= "</tr>";
          
          
          $tabIndexM++;
          $tabIndexV++;
          
      }
      
      
      $htmlSchueler .= "<tr><td><i>Klassenschnitt</i></td><td>" . $notenMV->getMSchnitt() . "</td><td>" . $notenMV->getVSchnitt() . "</td></tr>";
      
      
     eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/mv/unterricht/index") . "\");");
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
