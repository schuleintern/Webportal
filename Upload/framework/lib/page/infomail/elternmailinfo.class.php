<?php



class elternmailinfo extends AbstractPage {

  private $isMailAdmin = false;

  public function __construct() {

    $this->needLicense = false;

    parent::__construct ( array (
      "Infomail - Rückläufe"
    ) );

    $this->checkLogin();

    $this->isMailAdmin = DB::getSession()->isMember("Webportal_Elternmail");

    if(DB::getSession()->isAdmin()) $this->isMailAdmin = true;

    if(!DB::getSession()->isTeacher() && !$this->isMailAdmin) new errorPage("Leider kein Zugriff!");


  }

  public function execute() {
    if($_GET['gradeEltern'] != "" || $_GET['gradeSchueler'] !="") {
      $this->showKlasse();
    }
    else if($_GET['teachers'] != 1 && $_GET['group'] == "") {
      // Mails anzeigen
      // Alls Mails anzeigen (Liste)
      
    	
      
    	
      // Als Admin auf die Liste der gesendeten Mails weiter leiten
      if($this->isMailAdmin) {
        header("Location: index.php?page=elternmailsender&mode=sent");
        die();
      }
      
      
      $meineKlassen = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();

      // Mails suchen, die Zugriff für die Klassenleitungen haben und an Klassen von mir gingen.


      $listeKlassen = array();
      for($i = 0; $i < sizeof($meineKlassen); $i++) {
        $listeKlassen[] = $meineKlassen[$i]->getKlassenName();
      }

      if(sizeof($listeKlassen) > 0) {
        $mails = DB::getDB()->query("SELECT DISTINCT elternmailID, schuelerKlasse, mailTitle, mailTime, mailRequireConfirmation FROM elternmail_mails JOIN schueler ON elternmailSchuelerAsvID=schuelerAsvID JOIN elternmail ON elternmail.mailID=elternmail_mails.elternmailID WHERE schuelerKlasse IN ('" . implode("','", $listeKlassen) . "')");

        $mailData = array();
        while($mail = DB::getDB()->fetch_array($mails)) {
          $mailData[] = $mail;
        }

        if(sizeof($mailData) == 0) {
          eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/info/nomaildata") . "\");");
          PAGE::kill(true);
		    	//exit(0);
        }

        $mailHTML = "";
        for($i = 0; $i < sizeof($mailData); $i++) {
          $mailHTML .= "<tr><td>" . $mailData[$i]['mailTitle'] . "</td>";
          $mailHTML .= "<td>" . $mailData[$i]['schuelerKlasse'] . "</td>";
          $mailHTML .= "<td>" . functions::makeDateFromTimestamp($mailData[$i]['mailTime']) . "</td>";
          if($mailData[$i]['mailRequireConfirmation'] > 0) $mailHTML .= "<td><a href=\"index.php?page=elternmailinfo&gradeEltern=" . $mailData[$i]['schuelerKlasse'] . "&mailID=" . $mailData[$i]['elternmailID'] . "\">Rückläufe ansehen</a></td>";
          else $mailHTML .= "<td>Diese Mail fordert keine Lesebestätigung.</td>";
        }

        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/info/letters") . "\");");
        PAGE::kill(true);
		  	//exit(0);

      }
      else {
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/info/noklassenleitung") . "\");");
        PAGE::kill(true);
		  	//exit(0);
      }


    }
    else {
      $this->showGruppe();
    }
  }

  private function showGruppe() {
    if(!$this->isMailAdmin) {
      die("Kein Zugriff!");
    }

    $mailID = intval($_REQUEST['mailID']);

    if(isset($_REQUEST['mailID']) && $_REQUEST['mailID'] > 0) {
      $mail = DB::getDB()->query_first("SELECT * FROM elternmail WHERE mailID='" . DB::getDB()->escapeString($mailID) . "'");

      if($mail['mailID'] > 0) {
        // Mail vorhanden
      }
      else {
        new errorPage("Mail ungültig!");
      }
    }
    else {
      new errorPage("Mail ungültig!");
    }

    $mailData = array();

    if($_GET['group'] != "") {

      $mails = DB::getDB()->query("SELECT * FROM elternmail_groups NATURAL JOIN users JOIN elternmail_mails ON mailUserID=userID WHERE elternmailID='$mailID' AND groupName LIKE '" . DB::getDB()->escapeString($_GET['group']) . "' ORDER BY userName ASC, userLastName ASC, userFirstName ASC");
      while($m = DB::getDB()->fetch_array($mails)) {
        $mailData[] = $m;
      }

      $groupName = $_GET['group'];
    }
    else if($_GET['teachers'] == 1) {
      $mails = DB::getDB()->query("SELECT * FROM lehrer JOIN users ON lehrerUserID=userID JOIN elternmail_mails ON mailUserID=userID WHERE elternmailID='$mailID' ORDER BY userName ASC, userLastName ASC, userFirstName ASC");
      while($m = DB::getDB()->fetch_array($mails)) {
      $mailData[] = $m;
      }
    }

   $tableData = "";

    for($i = 0; $i < sizeof($mailData); $i++) {
      $tableData .= "<tr><td>" . ($i+1) . "</td><td>" . $mailData[$i]['userFirstName'] . " " . $mailData[$i]['userLastName'] . "</td><td>";
      if($mailData[$i]['mailConfirmed'] > 0) {
        $tableData .= "<font color=\"green\">Empfang bestätigt am " . Functions::makeDateFromTimestamp($mailData[$i]['mailConfirmed']) . "</font>";
      }
      else {
        $tableData .= "<font color=\"red\">Empfang noch nicht bestätigt</font>";
      }
    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/info/group") . "\");");
    PAGE::kill(true);
			//exit(0);

  }

  private function showKlasse() {
    // Rückläufe anzeigen

    // Klasse anzeigen
    $mailID = $_REQUEST['mailID'];

    if(isset($_REQUEST['mailID']) && $_REQUEST['mailID'] > 0) {
      $mail = DB::getDB()->query_first("SELECT * FROM elternmail WHERE mailID='" . DB::getDB()->escapeString($mailID) . "'");

      if($mail['mailID'] > 0) {
        if(!$this->isMailAdmin) {
          if($mail['mailAccessKlassenleitung'] > 0) {
            // Alles OK
          }
          else {
            new errorPage("Kein Zugriff auf diese Mails für Klassenleitungen");
            die();
          }
        }

        if($mail['mailRequireConfirmation'] == 0) {
          eval("echo(\"" . DB::getTPL()->get("elternmail/info/no_confirmation_required") . "\");");
          PAGE::kill(true);
          //exit(0);
        }

        // Rücklauf für Klasse anzeigen

        if($_GET['gradeEltern'] != "") {

          $klasse = DB::getDB()->escapeString($_GET['gradeEltern']);
          $grade = klasse::getByName($klasse);

          $sql = "SELECT * FROM elternmail_mails LEFT JOIN schueler ON elternmailSchuelerAsvID=schuelerAsvID WHERE schuelerKlasse LIKE '" . $klasse . "' AND elternmailID='" . $mailID . "' ORDER BY schuelerName ASC, schuelerRufname ASC";

        }
        else if($_GET['gradeSchueler'] != "") {

          $klasse = DB::getDB()->escapeString($_GET['gradeSchueler']);
          $grade = klasse::getByName($klasse);

          $sql = "SELECT * FROM elternmail_mails LEFT JOIN users ON mailUserID=userID LEFT JOIN schueler ON schuelerUserID=userID WHERE schuelerKlasse LIKE '" . $klasse . "' AND elternmailID='" . $mailID . "' ORDER BY schuelerName ASC, schuelerRufname ASC";

        }

        if($grade != null) {
          $schueler = $grade->getSchueler();

          $mails = DB::getDB()->query($sql);

          $mailData = array();
          while($m = DB::getDB()->fetch_array($mails)) {
            $mailData[] = $m;
          }

          $tableData = "";

          for($i = 0; $i < sizeof($schueler); $i++) {
            $tableData .= "<tr><td>" . ($i + 1) . "<td>" . $schueler[$i]->getCompleteSchuelerName() . "</td>";
            $tableData .= "<td>";

            $nr = 0;
            for($m = 0; $m < sizeof($mailData); $m++) {
              if($mailData[$m]['schuelerAsvID'] == $schueler[$i]->getAsvID()) {
                $nr++;
                $tableData .= "Empfänger " . $nr . ": ";
                

                if($mailData[$m]['mailConfirmed'] > 0) {
                  $tableData .= " <font color=\"green\">Bestätigt am " . functions::makeDateFromTimestamp($mailData[$m]['mailConfirmed']) . "</font>";
                }
                else $tableData .= " <font color=\"red\">Noch nicht bestätigt!</font>";

                $tableData .= "<br />";
              }
            }

            $tableData .= "</td>";

          }

          eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/info/klasse") . "\");");
          PAGE::kill(true);
		    	//exit(0);

        }
        else {
          new errorPage("Ungültige Klasse angegeben!");
        }

      }
      else {
        new errorPage("Die angegebene Mail existiert nicht!");
        die();
      }

    }

  }

  public static function hasSettings() {
    return false;
  }

  /**
   * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
   * @return array(String, String)
   * array(
   * 	   array(
   * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
   *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
   *      'titel' => "Titel der Beschreibung",
   *      'text' => "Text der Beschreibung"
   *     )
   *     ,
   *     .
   *     .
   *     .
   *  )
   */
  public static function getSettingsDescription() {
    return array();
  }


  public static function getSiteDisplayName() {
    return 'Elternmail: Information über Rückläufe';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array();

  }

}

?>
