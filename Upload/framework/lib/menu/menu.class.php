<?php


/**
 * Menü der Seite
 * @author Christian
 *
 */
class menu {
  private $html = "";

  public function __construct($isAdmin = false, $isNotenverwaltung = false) {
    if($isAdmin) $this->adminMenu();
    elseif($isNotenverwaltung) $this->notenMenu();
    else $this->normalMenu();
  }

  private function notenMenu() {
    if(!DB::isLoggedIn()) return;

    $this->html .= "<li><div class=\"callout callout-danger\"><a href=\"index.php\"><i class=\"fa fa-arrow-left\"></i><span> Notenverwaltung verlassen</span></div></li>";
    $this->html .= $this->getMenuItem('NotenverwaltungIndex', 'Startseite der Notenverwaltung', 'fa fa-flask');

    if(DB::getSession()->isTeacher()) {
      $myUnterricht = SchuelerUnterricht::getUnterrichtForLehrer(DB::getSession()->getTeacherObject());


      $this->html .= $this->startDropDown(['NotenEingabe'], 'Noteneingabe', 'fa fas fa-pencil-alt');

      $unterrichtShown = [];

      for($i = 0; $i < sizeof($myUnterricht); $i++) {

          if(!in_array($myUnterricht[$i]->getID(), $unterrichtShown) && $myUnterricht[$i]->isPflichtunterricht()) {

              $unterrichtShown[] = $myUnterricht[$i]->getID();

              $koppelUnterricht = $myUnterricht[$i]->getKoppelUnterricht();
              for($k = 0; $k < sizeof($koppelUnterricht); $k++) $unterrichtShown[] = $koppelUnterricht[$k]->getID();


              $schueler = $myUnterricht[$i]->getSchueler();
            if(sizeof($schueler) > 0) {
              $this->html .= $this->getMenuItem('NotenEingabe', $myUnterricht[$i]->getAllKlassenAsList()  . ": " . $myUnterricht[$i]->getFach()->getKurzform() . " (" . $myUnterricht[$i]->getBezeichnung() . ")", 'fa fa-award ',['unterrichtID' => $myUnterricht[$i]->getID()]);

            }
          }
      }

      $this->html .= $this->endDropDown();

    }
    
    
    $zeugnisse = NoteZeugnis::getAll();
    
    $klassenMitKlassenleitung = [];
    
    
    
    if(DB::getSession()->isTeacher()) $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
    
    for($i = 0; $i < sizeof($zeugnisse); $i++) {
        
        $this->html .= $this->startDropDown(['NotenZeugnisMV','NotenWahlunterricht', 'NotenZeugnisKlassenleitung'], $zeugnisse[$i]->getName(), 'fa fa-certificate',  ['zeugnisID' => $zeugnisse[$i]->getID()]);
        
        $this->html .= $this->getMenuItem('NotenZeugnisMV', "Mitarbeit / Verhalten", 'fa fa-comments', ['zeugnisID' => $zeugnisse[$i]->getID()]);
        
        $this->html .= $this->getMenuItem('NotenWahlunterricht', "Wahlunterricht", 'fa fa-space-shuttle', ['zeugnisID' => $zeugnisse[$i]->getID()]);
        
        $this->html .= $this->getMenuItem('NotenZeugnisKlassenleitung', "Klassenleitung", 'fa fa-certificate', ['zeugnisID' => $zeugnisse[$i]->getID()]);
        
        $this->html .= $this->endDropDown();
    }
    



    if(DB::getSession()->isAdmin() || schulinfo::isSchulleitung(DB::getSession()->getUser())) {
        $this->html .= $this->startDropDown(['NotenverwaltungZeugnisse'], 'Schulleitung', 'fa fa-user-circle');

        $this->html .= $this->getMenuItem('NotenverwaltungZeugnisse', 'Zeugnisse', 'fa fa-certificate', []);
        $this->html .= $this->getMenuItem('NotenverwaltungZeugnisse', 'Notenbericht', 'fa fa-certificate', ['action' => 'zwischenbericht']);
        

        $this->html .= $this->endDropDown();
    }

    // Is Any Klassenleitung

    // $this->html .= $this->getMenuItem('NotenverwaltungRecoverZuordnung', 'Alte Noten zuordnen', 'fa fa-exclamation-triangle');

  }

  private function adminMenu() {

    if(!DB::isLoggedIn()) return;
    


    $modulAdminHTML = "";


    if($_REQUEST['page'] == 'administrationmodule' && $this->isActive($_REQUEST['module'])) {
        $modulAdminHTML .= "<li class='btn btn-danger'><a href=\"index.php?page=index\"><i class=\"fa fa-arrow-left\"></i><span> Administration verlassen</span></li>";
    }
    else {
        $modulAdminHTML .= "<li><div class=\"callout callout-danger\"><a href=\"index.php\"><i class=\"fa fa-arrow-left\"></i><span> Administration verlassen</span></div></li>";

    }



    if(DB::getSession()->isAdmin()) $modulAdminHTML .= $this->getMenuItem('administration', 'Info / Statistik', 'fa fa-check');



    if(DB::getSession()->isAdmin()) $modulAdminHTML .= $this->getMenuItem('administrationactivatepages', 'Modulstatus', 'fas fa-toggle-on');


    $displayActions = [];

    /**
     *
     * @var AbstractPage[] $actions
     */
    $actions = requesthandler::getAllowedActions();
    for($i = 0; $i < sizeof($actions); $i++) {
      if($actions[$i]::hasAdmin()) {
        $view = false;

        if(sizeof($actions[$i]::onlyForSchool()) > 0) {
          $view = in_array(DB::getGlobalSettings()->schulnummer, $actions[$i]::onlyForSchool());
        }
        else $view = true;

        if($view && (DB::getSession()->isAdmin() || in_array($actions[$i]::getAdminGroup(), DB::getSession()->getGroupNames()))) {
          if(!is_array($displayActions[$actions[$i]::getAdminMenuGroup()])) {
            $displayActions[$actions[$i]::getAdminMenuGroup()] = [
              $actions[$i]
            ];
          }
          else {
            $displayActions[$actions[$i]::getAdminMenuGroup()][] = $actions[$i];
          }
        }
      }
    }


    $unsorted = [];
    $sorted = [];

    foreach($displayActions as $kg => $pages) {
      $unsorted[] = $kg;
    }

    sort($unsorted);

    for($i = 0; $i < sizeof($unsorted); $i++) {
      $sorted[$unsorted[$i]] = $displayActions[$unsorted[$i]];
    }

    $displayActions = $sorted;




    // Debugger::debugObject($sorted,true);

    foreach($displayActions as $kg => $pages) {

      sort($pages);

      if($kg != 'NULL') {
        $modulAdminHTML .= $this->startDropDown(['administrationmodule'], $kg, $pages[0]::getAdminMenuGroupIcon(), ['module' => $pages]);
      }

      // Debugger::debugObject($pages);

      for($i = 0; $i < sizeof($pages); $i++) {
        $modulAdminHTML .= $this->getMenuItem('administrationmodule', $pages[$i]::getSiteDisplayName(), $pages[$i]::getAdminMenuIcon(), ['module' => $pages[$i]]);
      }

      if($kg != 'NULL') {
        $modulAdminHTML .= $this->endDropDown();
      }

    }

    // Debugger::debugObject(null,1);

    $modulAdminHTML .= "<li><br /><br /></li>";
    

    $this->html .= $modulAdminHTML;


  }
  
  private function getTrenner($text) {
      return '<li class="header"><b>' . $text . '</b></li>';
  }

  
  private function normalMenu() {
    if(!DB::isLoggedIn()) return;
    
    $currentStundenplan = stundenplandata::getCurrentStundenplan();

    if($currentStundenplan == null) {
      $this->html .= $this->getMenuItem("index", "Kein Stundenplan vorhanden!", "fa fa-exclamation-triangle");

      if(DB::isLoggedIn() && DB::getSession()->isAnyAdmin()) {
        $this->html .= $this->getMenuItem("administration", "Administration", "fa fa-cogs");
      }


      return;
    }
       
    
    if($this->isActive('kondolenzbuch')) {
        $this->html .= $this->getMenuItem('kondolenzbuch', "Kondolenzbuch", 'fa fa-book');
    }
    
    
    $this->aktuelles();
    $this->informationen();
    $this->lehrerAnwendungen();
    $this->verwaltung();
    $this->userAccount();
    $this->unterricht();
    
    $this->unsorted();
    
    
    
    
    
    
    if(DB::isLoggedIn() && DB::getSession()->isAnyAdmin()) {
        $this->html .= $this->getTrenner('<i class="fa fa-cogs"></i> Administration');
        $html .= $this->getMenuItem("administration", "Administration", "fa fa-cogs");
    }
    
    
    $html .= $this->getMenuItem("impressum", "Impressum / Datenschutz", "fa fa-info-circle");
    
    
    $html .= "<li><br /><br /></li>";
    
    
    $this->html .= $html;
    
    
    
  }
  
  private function aktuelles() {
          
    
       

    $html .= $this->getMenuItem("aufeinenblick", " Auf einen Blick", "fa fa-calendar-check");


    if(DB::isLoggedIn() && $this->isActive("vplan") && (DB::getSession()->isTeacher() || DB::getSettings()->getValue("vplan-schueleractive") != 0)) {
        $html .= $this->getMenuItem("vplan", "Vertretungsplan", "fa fa-retweet");
    }
    
    
    if($this->isActive("klassenkalender") || $this->isActive('extKalender') || $this->isActive('andereKalender')) {
        $html .= $this->startDropDown(['klassenkalender','extKalender','andereKalender','terminuebersicht'], "Kalender", "fa fa-calendar");
        
        
        if($this->isActive("terminuebersicht")) {
            
            $html .= $this->getMenuItem('terminuebersicht', "Terminübersicht", 'fa fa-calendar');
            
            
        }
        // Externe Kalender
        
        $externeKalender = extKalender::getKalenderWithAccess();
        
        for($i = 0; $i < sizeof($externeKalender); $i++) {
            $html .= $this->getMenuItem('extKalender', $externeKalender[$i]['kalenderName'], 'fa fa-calendar',['kalenderID' => $externeKalender[$i]['kalenderID']]);
        }
        
        
        
        // Andere Kalender
        
        $andereKalender = andereKalender::getKalenderWithAccess();
        
        for($i = 0; $i < sizeof($andereKalender); $i++) {
            $html .= $this->getMenuItem('andereKalender', $andereKalender[$i]['kalenderName'], 'fa fa-calendar',['kalenderID' => $andereKalender[$i]['kalenderID']]);
            
        }
        
        
        if($this->isActive("klassenkalender")) {
            if(DB::getSession()->isAdmin() && !DB::getSession()->isTeacher() || DB::getSession()->isMember('Webportal_Klassenkalender')) {
                $html .= $this->getMenuItem("klassenkalender", "Alle Klassen", "fa fa-users", ['grade' => 'all_grades']);
            }
            
            if(DB::getSession()->isTeacher() ) {
                
                $html .= $this->getMenuItem("klassenkalender", "Meine Klassen", "fa fas fa-users", ['grade' => 'allMyGrades']);
                $html .= $this->getMenuItem("klassenkalender", "Alle Klassen", "fa fa-users", ['grade' => 'all_grades']);
                $html .= $this->getMenuItem("klassenkalender", "Von mir eingetragen", "fa fa-users", ['grade' => 'allMyTermine']);
                
                $grades = klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());
                
                $htmlMyGrades = "";
                
                for($i = 0; $i < sizeof($grades); $i++) {
                    $htmlMyGrades .= $this->getMenuItem("klassenkalender", $grades[$i]->getKlassenName(), "fa fa-child", ['grade' => ($grades[$i]->getKlassenName())]);
                }
                
                if($htmlMyGrades != "") {
                    $html .= $this->startDropDown(['klassenkalender'], "Meine Klassen", "fa fa-users");
                    
                    $html .= $htmlMyGrades;
                    
                    $html .= $this->endDropDown();
                }
                
                if(DB::getSettings()->getBoolean('klassenkalender-fachbetreueransicht')) {
                
                    $htmlFachschaftsleitung = "";
                    
                    $faecher = fach::getMyFachschaftsleitungFaecher(DB::getSession()->getTeacherObject());
                    
                    for($i = 0; $i < sizeof($faecher); $i++) {
                        $htmlFachschaftsleitung .= $this->getMenuItem("klassenkalender", $faecher[$i]->getLangform(), "fas fa-briefcase", ['grade' => 'fachbetreuer', 'fachASDID' => urlencode($faecher[$i]->getASDID())]);
                    }
                    
                    if($htmlFachschaftsleitung != "") {
                        $html .= $this->startDropDown(['klassenkalender'], "Fachbetreuung", "fas fa-briefcase", ['fachASDID' => ['ISPRESENT']]);
                        
                        $html .= $htmlFachschaftsleitung;
                        
                        $html .= $this->endDropDown();
                    }
                
                }
                
            }
            
            else {
                
                if(DB::getSession()->isPupil()) {
                    $grades = [DB::getSession()->getSchuelerObject()->getKlassenObjekt()];
                }
                else if(DB::getSession()->isEltern()) {
                    $grades = [];
                    
                    $grades = DB::getSession()->getElternObject()->getKlassenObjectsAsArray();
                }
                else $grades = array();
                
                for($i = 0; $i < sizeof($grades); $i++) {
                    // Klassen aus Stundenplan suchen:
                    $klasse = $grades[$i];
                    if($klasse != null) {
                        $html .= $this->getMenuItem("klassenkalender", $klasse->getKlassenName(), "fa fa-child", ['grade' => $klasse->getKlassenName()]);
                    }
                }
            }
        }
        
        $html .= $this->endDropDown();
    }
    
    
    if($this->isActive('ffbumfrage') && (DB::getSession()->isPupil() || DB::getSession()->isEltern())){
        $html .= $this->getMenuItem('ffbumfrage', 'Umfrage', "fa fa-question-circle");
    }
    
    
    if($html != "") {
        $this->html .= $this->getTrenner('<i class="fa fa-clock"></i> Aktuelles</a>');
        $this->html .= $html;
    }
    
  }
  
  private function informationen() {
      
      $currentStundenplan = stundenplandata::getCurrentStundenplan();

      $this->html .= $this->getTrenner('<i class="fa fa-info-circle"></i> Informationen</i>');
          
    // Stundenplan ist immer aktiv
    if(DB::getSession()->isTeacher() || DB::getSession()->isMember("Webportal_Stundenplananzeige")) $html .= $this->getMenuItem("stundenplan", "Stundenplan", "fa fa-table");

    if(DB::getSession()->isEltern()) {

      $klassen = DB::getSession()->getElternObject()->getKlassenAsArray();

      for($i = 0; $i < sizeof($klassen); $i++) {
        $html .= $this->getMenuItem("stundenplan", "Stundenplan " . $klassen[$i], "fa fa-table", ['grade' => $klassen[$i]]);
      }
    }

    if(DB::getSession()->isPupil()) {
      $klassen = $currentStundenplan->getAllMyPossibleGrades(DB::getSession()->getSchuelerObject()->getKlasse());
      for($i = 0; $i < sizeof($klassen); $i++) {
          $html .= $this->getMenuItem("stundenplan", "Stundenplan " . $klassen[$i], "fa fa-table", ['grade' => $klassen[$i]]);
      }
    }
    
    if($this->isActive('dokumente')) {
        $kgs = dokumenteKategorie::getAllWithMyAccess();
        
        if(sizeof($kgs) > 0) {
            $html .= $this->startDropDown(['dokumente'], "Dokumente und Formulare", "fa fa-file");
            
            for($i = 0; $i < sizeof($kgs); $i++) {
                $html .= $this->getMenuItem("dokumente", $kgs[$i]->getName(), "fa fa-file", ['sectionID' => $kgs[$i]->getID()]);
            }
            
            $html .= $this->endDropDown();
        }
    }
    
    
    if( $this->isActive("mensaSpeiseplan") )  {
      
      $html .= $this->getMenuItem("mensaSpeiseplan", "Speiseplan", "fa fas fa-utensils");
    }


    if(!DB::getSession()->isEltern() &&
        
        ($this->isActive("office365") || $this->isActive("homeuseprogram") || $this->isActive("office365info")
            
            
            
            
            )
        
        ) {
            
            $html .= $this->startDropDown(['office365','homeuseprogram'], "Software / Lizenzen", "fa fa-download");
            

            if($this->isActive("office365users") && (DB::getSession()->isTeacher() || DB::getSession()->isPupil())) {
                $html .= $this->getMenuItem("office365users", "Office 365 Account", "fa fa-file-word");
            }
            
            
            if($this->isActive("office365info") && (DB::getSession()->isTeacher() || DB::getSession()->isPupil())) {
                $html .= $this->getMenuItem("office365info", "Office 365 Login", "fa fa-file-word");
            }
            
            
            
            if($this->isActive("homeuseprogram") && DB::getSession()->isTeacher()) {
                $html .= $this->getMenuItem("homeuseprogram", "Home Use Program (Office)", "fa fa-file-word");
            }

            
            $html .= $this->endDropDown();
        }
        
        
        $html .= $this->getMenuItem("schulinfo", "Schulinformationen", "fa fa-info-circle");
        
        
        if($html != "") {
            
            $this->html .= $html;
        }
  }
  
  private function lehrerAnwendungen() {
      

    


    if(DB::getGlobalSettings()->hasNotenverwaltung && DB::getSession()->isTeacher()) {
      $html .= $this->getMenuItem('NotenverwaltungIndex', "Zur Notenverwaltung", "fa fa-flask");
    }

    
    if(DB::isLoggedIn() && $this->isActive("klassenlisten") && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember("Webportal_Klassenlisten_Sehen") || DB::getSession()->isMember("Schuelerinfo_Sehen") || DB::getSession()->isMember('Webportal_Elternmail'))) {
        $pages = array("klassenlisten", 'schuelerinfo','AngemeldeteEltern');
        
        $html .= $this->startDropDown($pages, "Schülerverwaltung", "fa fa-child");
        
        if($this->isActive("schuelerinfo") && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember("Schuelerinfo_Sehen")))  $html .= $this->getMenuItem("schuelerinfo", "Schülerinformationen", "fa fa-info");
        if($this->isActive("klassenlisten") && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember("Schuelerinfo_Sehen")))  $html .= $this->getMenuItem("klassenlisten", "Klassenlisten", "fa fa-list");
        if($this->isActive("ganztags") && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember("Schuelerinfo_Sehen")))  $html .= $this->getMenuItem("ganztags", "Ganztags", "fa fa-list");
        if(DB::getSession()->isAdmin() || DB::getSession()->isMember('Webportal_Elternmail')) $html .= $this->getMenuItem("AngemeldeteEltern", "Angemeldete Eltern", "fa fa-list");
        
        $html .= $this->endDropDown();
    }
    

    if($this->isActive("respizienz") && respizienz::userHasAccess(DB::getSession()->getUser())) {

        $html .= $this->startDropDown(['respizienz'], DB::getSettings()->getValue("resp-name"), 'fa fa-briefcase');


        $html .= $this->getMenuItem("respizienz", "Fachlehrer", "fa fa-briefcase", ['mode' => '']);

        if(DB::getSession()->isTeacher() && DB::getSession()->getTeacherObject()->isFachschaftsleitung()
            && DB::getSettings()->getBoolean("resp-activate-fb")
        )
            $html .= $this->getMenuItem("respizienz", "Fachschaftsleitung", "fa fa-briefcase", ['mode' => 'fachbetreuer']);

        if(DB::getSession()->isTeacher() && DB::getSession()->getTeacherObject()->isSchulleitung()
                    && DB::getSettings()->getBoolean("resp-activate-sl")
        )
            $html .= $this->getMenuItem("respizienz", "Schulleitung", "fa fa-briefcase", ['mode' => 'schulleitung']);


        $html .= $this->endDropDown();
    }
    
    if(DB::isLoggedIn() && $this->isActive("beobachtungsbogen") && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin())) {
        $pages = array("beobachtungsbogen","beobachtungsbogenadmin","beobachtungsbogenklassenleitung");
        
        $html .= $this->startDropDown($pages, "Pädagogische Würdigung", "fa fa-balance-scale");
        
        if($this->isActive("beobachtungsbogen")) $html .= '<li' . (($_REQUEST['page'] == "beobachtungsbogen")?(" class=\"active\""):("")) . '><a href="index.php?page=beobachtungsbogen"><i class="fa fa-balance-scale"></i> Beobachtungen eingeben</a></li>';
        if($this->isActive("beobachtungsbogenklassenleitung")) $html .= '<li' . (($_REQUEST['page'] == "beobachtungsbogenklassenleitung")?(" class=\"active\""):("")) . '><a href="index.php?page=beobachtungsbogenklassenleitung"><i class="fa fa-cogs"></i> Klassenleitung</a></li>';
        
        
        if(DB::getSession()->isMember("Webportal_Leistungsbericht_Admin"))
            if($this->isActive("beobachtungsbogenadmin")) $html .= '<li' . (($_REQUEST['page'] == "beobachtungsbogenadmin")?(" class=\"active\""):("")) . '><a href="index.php?page=beobachtungsbogenadmin"><i class="fa fa-cogs"></i> Administration</a></li>';
            
            $html .= $this->endDropDown();
    }
    
    
    if(DB::isLoggedIn() && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin()) && $this->isActive("laufzettel")) {
        
        
        if(DB::getSession()->isTeacher()) $zuBestaetigen = DB::getDB()->query_first("SELECT COUNT(laufzettelID) AS zubestaetigen FROM laufzettel WHERE laufzettelDatum >= CURDATE() AND laufzettelID IN (SELECT laufzettelID FROM laufzettel_stunden WHERE laufzettelLehrer LIKE '" . DB::getSession()->getTeacherObject()->getKuerzel() . "' AND laufzettelZustimmung=0)");
        
        $html .= $this->startDropDown(['laufzettel'], 'Laufzettel', 'fa fa-user-check',[],$zuBestaetigen['zubestaetigen']);
        
        
        $html .= $this->getMenuItem('laufzettel', 'Zu bestätigen', 'fa fa-check', ['mode' => 'myLaufzettel'], $zuBestaetigen['zubestaetigen']);
        $html .= $this->getMenuItem('laufzettel', 'Laufzettel anlegen', 'fa fa-plus', ['mode' => 'addLaufzettel']);
        $html .= $this->getMenuItem('laufzettel', 'Meine Laufzettel', 'fa fa-male', ['mode' => 'myOwnLaufzettel']);
        
        if(DB::getSettings()->getBoolean('laufzettel-elektronische-genehmigung-schulleitung')) {
            if(schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->getUser()->isMember('Webportal_Laufzettel_Schulleitung')) {
                $html .= $this->getMenuItem('laufzettel', 'Schulleitung', 'fa fa-hand-o-up', ['mode' => 'schulleitung']);
                
            }
        }
        
        
        /*
         $html .= '<li' . (($_REQUEST['page'] == "laufzettel" && $_GET['mode'] == "addLaufzettel")?(" class=\"active\""):("")) . '><a href="index.php?page=laufzettel&mode=addLaufzettel"><i class="fa fa-plus"></i> Laufzettel erstellen</a></li>';
         $html .= '<li' . (($_REQUEST['page'] == "laufzettel" && $_GET['mode'] == "myOwnLaufzettel")?(" class=\"active\""):("")) . '><a href="index.php?page=laufzettel&mode=myOwnLaufzettel"><i class="fa fa-file-text"></i> Meine Laufzettel</a></li>';
         
         $html .= '</ul>
         </li>';*/
        
        $html  .= $this->endDropDown();
        
    }
    
    
    if(DB::isLoggedIn() && $this->isActive("ausleihe") && ausleihe::hasCurrentUserAccess() != NULL) {

      $html .= $this->getMenuItem("ausleihe", "Reservierungen", "fa fa-check-square");

    }
    
    if(DB::isLoggedIn() && (DB::getSession()->isTeacher() || DB::getSession()->isAdmin()) && $this->isActive("projektverwaltung")) {
        // Projektverwaltung
        
        $projektVerwaltungShow = "";
        
        $meineKlassen = array ();
        
        if (DB::getSession()->getUser()->isMember("Webportal_Projektverwaltung_Admin") || DB::getSession()->isAdmin()) {
            $meineKlassen = grade::getAllGradesAtLevel ( 9 );
        } else {
            $grs = DB::getDB ()->query( "SELECT * FROM projekt_lehrer2grade WHERE lehrerUserID='" . DB::getSession ()->getData("userID") . "'" );
            
            while ( $g = DB::getDB ()->fetch_array ( $grs ) ) {
                $meineKlassen [] = $g ['gradeName'];
            }
        }
        
        if(sizeof($meineKlassen) > 0) {
            
            for($i = 0; $i < sizeof($meineKlassen); $i++) {
                $projektVerwaltungShow .= '<li' . (($_REQUEST['page'] == "projektverwaltung" && $_GET['action'] == "grade" && $_GET['gradeName'] == $meineKlassen[$i])?(" class=\"active\""):("")) . '><a href="index.php?page=projektverwaltung&action=grade&gradeName=' . $meineKlassen[$i] . '"><i class="fa fa-briefcase"></i> Projekte Klasse ' . $meineKlassen[$i] . '</a></li>' . "\n";
                
            }
        }

        if (DB::getSession()->getUser()->isMember("Webportal_Projektverwaltung_Admin") || DB::getSession()->isAdmin()) {
            $projektVerwaltungShow .= '<li' . (($_REQUEST['page'] == "projektverwaltung" && $_GET['action'] == "admin")?(" class=\"active\""):("")) . '><a href="index.php?page=projektverwaltung&action=admin"><i class="fa fa-briefcase"></i> Projektverwaltung Admin</a></li>';
        }
        
        
        if($projektVerwaltungShow != "") {
            $pages = array("projektverwaltung");
            $html .= '<li class="' . ((in_array($_REQUEST['page'],$pages)) ? ("active ") : ("")) . 'treeview">
                <a href="#">
                  <i class="fa fa-briefcase"></i> <span>Projektverwaltung (9. Kl.)</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">';
            
            $html .= $projektVerwaltungShow;
            
            
            $html .= '</ul>
          </li>';
        }
    }
    
       

    
    
   // if(DB::isLoggedIn() && $this->isActive("AllInkMail") && DB::getSession()->isTeacher()) {
   //     $html .= '<li' . (($_REQUEST['page'] == "AllInkMail")?(" class=\"active\""):("")) . '><a href="index.php?page=AllInkMail"><i class="fa fa-envelope"></i> Mail Account</a></li>';
   //
   // }
    
    
    if($html != "") {
        $this->html .= $this->getTrenner('<i class="fa fa-graduation-cap"></i> Lehreranwendungen');
        
        $this->html .= $html;
    }
    
    
  }
  
  private function verwaltung() {   
    
    
    

    if(DB::isLoggedIn() && $this->isActive("elternsprechtag") && (DB::getSession()->isEltern() || DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember(elternsprechtag::getAdminGroup()))) {
        $html .= $this->getMenuItem("elternsprechtag", "Elternsprechtag", "fa fa-sign-language");
    }
    
    
    if($this->isActive("klassentagebuch")) {
        
        $tagebuchHTML = "";
        
        
        
        if(!DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten") && DB::getSession()->isEltern() && DB::getSettings()->getBoolean('klassentagebuch-eltern-klassentagebuch'))
            $tagebuchHTML .= $this->getMenuItem("klassentagebuch", "Klassentagebuch", "fa fa-book");
            
            else if(DB::getSession()->isTeacher())
                $tagebuchHTML .= $this->getMenuItem("klassentagebuch", "Klassentagebuch", "fa fa-book", ['mode' => 'showGrade']);
                
                else if(!DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten") && DB::getSession()->isPupil() && DB::getSettings()->getBoolean('klassentagebuch-schueler-klassentagebuch'))
                    $tagebuchHTML .= $this->getMenuItem("klassentagebuch", "Klassentagebuch", "fa fa-book", ['mode' => 'showGrade']);
                    
                    else if(!DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten") && DB::getSession()->isMember('Webportal_Klassentagebuch_Lesen'))
                        $tagebuchHTML .= $this->getMenuItem("klassentagebuch", "Klassentagebuch", "fa fa-book");
                        
                        
                        if(DB::getSettings()->getBoolean('klassentagebuch-lehrertagebuch') && DB::getSession()->isTeacher())
                            $tagebuchHTML .= $this->getMenuItem("klassentagebuch", "Lehrertagebuch", "fa fa-book", ['mode' => 'lehrerTagebuch']);
                            
                            
                            if(DB::getSession()->isTeacher() && $this->isActive('klassentagebuchauswertung')) {
                                
                                $fehlend = DB::getDB()->query_first("SELECT COUNT(fehlID) FROM klassentagebuch_fehl WHERE fehlLehrer='" . DB::getSession()->getTeacherObject()->getKuerzel() . "'");
                                
                                $tagebuchHTML .= $this->getMenuItem('klassentagebuchauswertung', 'Fehlende Einträge', 'fa fa-check', ['mode' => ''], $fehlend[0]);
                                
                                $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
                                
                                if(sizeof($klassenMitKlassenleitung) > 0) {
                                    
                                    $klassenNamen = [];
                                    
                                    for($i = 0; $i < sizeof($klassenMitKlassenleitung); $i++) {
                                        $klassenNamen[] = $klassenMitKlassenleitung[$i]->getKlassenName();
                                    }
                                    
                                    
                                    $fehlend = DB::getDB()->query_first("SELECT COUNT(fehlID) FROM klassentagebuch_fehl WHERE fehlKlasse IN ('" . implode("','", $klassenNamen) . "')");
                                    
                                    $tagebuchHTML .= $this->getMenuItem('klassentagebuchauswertung', 'Fehlende Einträge (KL)', 'fa fa-check', ['mode' => 'klassenleitung'], $fehlend[0]);
                                    
                                }
                                
                                if(DB::getSession()->isMember(klassentagebuch::getAdminGroup()) || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isAdmin()) {
                                    $fehlend = DB::getDB()->query_first("SELECT COUNT(fehlID) FROM klassentagebuch_fehl");
                                    
                                    
                                    $tagebuchHTML .= $this->getMenuItem('klassentagebuchauswertung', 'Schulleitung', 'fa fa-check', ['mode' => 'schulleitung'], $fehlend[0]);
                                    $tagebuchHTML .= $this->getMenuItem('klassentagebuchauswertung', 'PDF Export', 'fa fa-file-pdf', ['mode' => 'pdfexport']);
                                }
                            }
                            
                            if($tagebuchHTML != "") {
                                $html .= $this->startDropDown(['klassentagebuch','klassentagebuchauswertung'], 'Klassentagebuch', 'fa fa-book');
                                
                                $html .= $tagebuchHTML;
                                
                                $html .= $this->endDropDown();
                                
                            }
    }
    
    $ausweisHTML = "";
    
    if(DB::isLoggedIn() && $this->isActive("Ausweis")) {
        if(
            (DB::getSession()->isEltern() && DB::getSettings()->getBoolean("ausweis-schuelerausweis-eltern")) ||
            (DB::getSession()->isPupil() && DB::getSettings()->getBoolean("ausweis-schuelerausweis-schueler")) ||
            (DB::getSession()->isTeacher() && DB::getSettings()->getBoolean("ausweis-schuelerausweis-lehrer")) ||
            DB::getSession()->isMember('Webportal_Ausweis_Schueler_Antrag') ||
            DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
            DB::getSession()->isAdmin()
         ) {
            $ausweisHTML .= $this->getMenuItem("Ausweis", "Schülerausweis", "fa fa-child", ['action' => 'myAusweise','type' => 'SCHUELER']);
        }
        

        if(
            (DB::getSession()->isTeacher() && DB::getSettings()->getBoolean("ausweis-lehrerausweis-lehrer")) ||
            DB::getSession()->isMember('Webportal_Ausweis_Lehrer_Antrag') ||
            DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
            DB::getSession()->isAdmin()
            ) {
                $ausweisHTML .= $this->getMenuItem("Ausweis", "Lehrerausweis", "fa fa-female", ['action' => 'myAusweise','type' => 'LEHRER']);
            }
            
      if(
            DB::getSession()->isMember('Webportal_Ausweis_Mitarbeiter_Antrag') ||
            DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
            DB::getSession()->isAdmin()
         ) {
            $ausweisHTML .= $this->getMenuItem("Ausweis", "Mitarbeiterausweis", "fa fa-female", ['action' => 'myAusweise','type' => 'MITARBEITER']);
      }
      
      if(
          (DB::getSession()->isEltern() && DB::getSettings()->getBoolean("ausweis-gastausweis-eltern")) ||
          (DB::getSession()->isPupil() && DB::getSettings()->getBoolean("ausweis-gastausweis-schueler")) ||
          (DB::getSession()->isTeacher() && DB::getSettings()->getBoolean("ausweis-gastausweis-lehrer")) ||
          DB::getSession()->isMember('Webportal_Ausweis_Gast_Antrag') ||
          DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
          DB::getSession()->isAdmin()
          ) {
              $ausweisHTML .= $this->getMenuItem("Ausweis", "Gastausweis", "fa fa-question", ['action' => 'myAusweise','type' => 'GAST']);
          }
          
      if(
              DB::getSession()->isMember('Webportal_Ausweis_Genehmigen') ||
              DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
              DB::getSession()->isAdmin()
              ) {
                  $genehmigen = AbstractAusweis::getAusweiseToApprove();
          $ausweisHTML .= $this->getMenuItem("Ausweis", "Anträge Genehmigen", "fa fa-check", ['action' => 'approve'], sizeof($genehmigen));
      }
      
      if(
          DB::getSession()->isMember('Webportal_Ausweis_Drucken') ||
          DB::getSession()->isMember(Ausweis::getAdminGroup()) ||
          DB::getSession()->isAdmin()
          ) {
              $drucken = AbstractAusweis::getAusweiseToPrint();
              
              $ausweisHTML .= $this->getMenuItem("Ausweis", "Ausweise drucken", "fa fa-print", ['action' => 'print'], sizeof($drucken));
          }
          
    }
    
    if($ausweisHTML != "") {
        
        $html .= $this->startDropDown(['Ausweis'], "Ausweis", "fa fa-address-card");
        
        $html .= $ausweisHTML;
        
        $html .= $this->endDropDown();
    }


    $absenzen = "";

    if($this->isActive("absenzensekretariat") && absenzensekretariat::userHasAccess(DB::getSession()->getUser())) {

      $pages = ['absenzensekretariat', 'absenzenberichte','absenzenstatistik'];
      if(!DB::getSession()->isTeacher()) $pages[] = "absenzenlehrer";

      $absenzen .= $this->startDropDown($pages, "Absenzen Sekretariat", "fa fa-bed");
      $absenzen .= $this->getMenuItem("absenzensekretariat", "Hauptansicht", "fa fa-bed",['mode' => '']);
      $absenzen .= $this->getMenuItem("absenzenberichte", "Berichte", "fa fa-print");
      $absenzen .= $this->getMenuItem("absenzenstatistik", "Statistik", "fa fas fa-chart-pie");
      $absenzen .= $this->getMenuItem("absenzensekretariat", "Sammelbeurlaubung", "fa fa-bed",['mode' => 'sammelbeurlaubung']);
      $absenzen .= $this->getMenuItem("absenzensekretariat", "Periodische Beurlaubung", "fa fa-bed",['mode' => 'periodischeBeurlaubung']);
      $absenzen .= $this->getMenuItem("absenzensekretariat", "fpA Zeiten", "fa fa-wrench",['mode' => 'klassenanwesenheit']);
      // if(!DB::getSession()->isTeacher()) $absenzen .= $this->getMenuItem("absenzenlehrer", "Entschuldigungen überprüfen", "fa fa-check");
      $absenzen .= $this->endDropDown();
    }


    if($this->isActive("absenzenlehrer") && absenzenlehrer::userHasAccess(DB::getSession()->getUser())) {
      $absenzen .= $this->startDropDown(['absenzenlehrer'], "Absenzen Lehrer", "fa fa-bed");

      if(DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || DB::getSession()->isMember(absenzenlehrer::getAdminGroup())) $absenzen .= $this->getMenuItem("absenzenlehrer", "Klassenleiteransicht", "fa fa-check", ['mode' => '']);
      $absenzen .= $this->getMenuItem("absenzenlehrer", "Alle Absenzen einsehen", "fa fa-eye", ['mode' => 'showTotal']);

      $absenzen .= $this->endDropDown();
    }


    if($this->isActive("krankmeldung") && krankmeldung::userHasAccess(DB::getSession()->getUser())) {
      $absenzen .= $this->getMenuItem("krankmeldung", "Krankmeldung", "fa fa-bed");
    }

    if($this->isActive("beurlaubungantrag") && beurlaubungantrag::userHasAccess(DB::getSession()->getUser())) {
        $absenzen .= $this->getMenuItem("beurlaubungantrag", "Beurlaubungsantrag", "fa fa-sun");
    }
    

    if($this->isActive("absenzenschueler") && (DB::getSession()->isPupil() || DB::getSession()->isEltern())) {
      $absenzen .= $this->getMenuItem("absenzenschueler", "Meine Absenzen", "fa fa-bed");
    }
    
    if($this->isActive("WLanTickets") && (!DB::getSession()->isEltern())) {
        $absenzen .= $this->getMenuItem("WLanTickets", "W-Lan Zugang", "fa fa-wifi");
    }


    $html .= $absenzen;
    
    
    
    if(DB::isLoggedIn() && $this->isActive('schulbuecher') && (DB::getSession()->isAdmin() || DB::getSession()->isMember("Webportal_Schulbuch_Admin") || DB::getSession()->isTeacher() || DB::getSession()->isEltern() || DB::getSession()->isPupil())) {
        $pages = array('schulbuecher');
        
        
        $buecherHTML= $this->getMenuItem('schulbuecher', 'Meine Schulbücher', 'fa fa-book', ['mode' => '']);
        
        $hasOther = false;
        
        if(schulbuecher::userCanRueckgabe(DB::getSession()->getUser()) || schulbuecher::userCanAusleihe(DB::getSession()->getUser())) {
            $hasOther = true;
            $buecherHTML .= $this->getMenuItem('schulbuecher', 'Info / Abfrage', 'fa fa-briefcase', ['mode' => 'management']);
        }
        
        if(schulbuecher::userCanAusleihe(DB::getSession()->getUser())) {
            $hasOther = true;
            $buecherHTML .= $this->getMenuItem('schulbuecher', 'Ausleihe', 'fa fa-arrow-up', ['mode' => 'ausleihe']);
        }
        if(schulbuecher::userCanRueckgabe(DB::getSession()->getUser())) {
            $buecherHTML .= $this->getMenuItem('schulbuecher', 'Rückgabe', 'fa fa-arrow-down', ['mode' => 'rueckgabe']);
            $hasOther = true;
        }
        if(schulbuecher::userCanBestand(DB::getSession()->getUser())) {
            $buecherHTML .= $this->getMenuItem('schulbuecher', 'Bestand', 'fa fa-arrows-alt', ['mode' => 'bestand']);
            $hasOther = true;
        }
        
        if($hasOther) {
            $html .= $this->startDropDown($pages, "Schulbücher", 'fa fa-book');
            
            $html .= $buecherHTML;
            
            $html .= $this->endDropDown();
        }
        else {
            $html .= $buecherHTML;
        }
        
        
    }
    
    

    
    
    if($html != "") {
        $this->html .= $this->getTrenner('<i class="fa fas fa-pencil-alt-square"></i> Verwaltung</i>');
        $this->html .= $html;
    }
    
  }
  
  private function userAccount() {
   
      // Benutzer, die kein Passwort ändern dürfen (nicht Sync!) nichts anzeigen
      if(!DB::getSession()->getUser()->userCanChangePassword()) {
          return;
      }
    
   $this->html .= $this->getTrenner('<i class="fa fa-user"></i> Benutzeraccount / Nachrichten');


   $this->html .= $this->startDropDown(['userprofile','userprofilepassword','userprofileuserimage','userprofileemail','userprofileuserimage','userprofilemylogins', "TwoFactor"], "Benutzerprofil", "fa fa-user");

   $this->html .= $this->getMenuItem('userprofile','Benutzerprofil','fa fa-user');

   if(userprofilepassword::userHasAccess(DB::getSession()->getUser()))
       $this->html .= $this->getMenuItem('userprofilepassword','Kennwort ändern','fa fa-key');
   
       
   if(TwoFactor::is2FAActive()) $this->html .= $this->getMenuItem('TwoFactor','Zweifaktor','fa fa-key');
         
       
   $this->html .= $this->getMenuItem('userprofileuserimage','Benutzerbild','fa fa-image');
   $this->html .= $this->getMenuItem('userprofilemylogins','Meine Logins','fa fa-key');


   $this->html .= $this->endDropDown();

   $this->html .= $this->getMenuItem('MessageInbox', "Nachrichten", "fa fa-envelope");


  }
  
  private function unterricht() {

      $html = "";


      if($this->isActive("Lerntutoren") && (DB::getSession()->isPupil() || DB::getSession()->isEltern() || DB::getSession()->isTeacher())) {
          $html .= $this->getMenuItem("Lerntutoren", "Lerntutoren", "fa fa-graduation-cap");
      }

    if((($this->isActive("mebis") || $this->isActive("database"))&& (DB::getSession()->isTeacher() || DB::getSession()->isPupil()))) {
      $html .= $this->startDropDown(['mebis','database'], "Unterrichtstools", "fa fa-cubes");

      if($this->isActive("mebis") && (DB::getSession()->isTeacher() || (DB::getSession()->isPupil() && DB::getSettings()->getBoolean('mebis-schueler')))) {
        $html .= $this->getMenuItem("mebis", "Mebis Account", "fa fa-compass");
      }

     $html .= $this->endDropDown();
    }

    if($html != "") {
        
        $this->html .= $this->getTrenner('<i class="fa fa-graduation-cap"></i> Unterricht</i>');
        $this->html .= $html;
    }


  }
  
  private function unsorted() {


  }

  public function getHTML() {
    return $this->html;
  }

  /**
   *
   * @param unknown $page
   * @param unknown $title
   * @param unknown $icon
   * @param String[] $addParams
   * @return string
   */
  private function getMenuItem($page, $title, $icon, $addParams = [], $infoNumber = 0) {
    $isActive = false;

    $addParamString = "";
    if(sizeof($addParams) == 0) {
      if($_REQUEST['page'] == $page) $isActive = true;
    }
    else {
      foreach ($addParams as $name => $value) {
        $addParamString .= "&";
        $addParamString .= $name . "=" . urlencode($value);
        if($_REQUEST[$name] == $value) $isActive = true;
        else $isActive = false;
      }

      if($_REQUEST['page'] == $page && $isActive) $isActive = true;
      else $isActive = false;
    }

    return '<li' . (($isActive)?(" class=\"active\""):("")) . '><a href="index.php?page=' . $page . $addParamString . '"><i class="' . $icon . '"></i><span> ' . $title . '</span>' . (($infoNumber > 0) ? ('            <span class="pull-right-container">
              <span class="label label-primary pull-right">' . $infoNumber . '</span>
            </span>') : ('')) . '</a></li>';
  }

  private function startDropDown($pages, $title, $icon, $addParams = [], $infoNumber = 0) {

      $active = false;

    if(sizeof($addParams) > 0) {      
      foreach ($addParams as $name => $value) {
          if(is_array($value) && in_array($_REQUEST['page'],$pages)) {
            if($value[0] == 'ISPRESENT' && $_REQUEST[$name] != "") {
                $active = true;
            }
            else $active = in_array($_REQUEST[$name], $value);
        }
      }
    }
    else {
        $active = in_array($_REQUEST['page'],$pages);
        
    }

    return '<li class="' . (($active) ? ("active ") : ("")) . 'treeview">
              <a href="#">
                <i class="' . $icon . '"></i> <span>' . $title . '</span> ' . (($infoNumber > 0) ? (' <span class="pull-right-container">
              <span class="label label-primary">' . $infoNumber . '</span>
            </span>') : ('')) . ' <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">';
  }

  public function endDropDown() {
    return '</ul></li>';
  }

  public function isActive($page) {
    return AbstractPage::isActive($page);
  }

  public static function siteIsAlwaysActive() {
    return true;
  }

}



?>
