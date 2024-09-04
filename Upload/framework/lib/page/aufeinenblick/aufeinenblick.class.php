<?php



class aufeinenblick extends AbstractPage {

  private $mySettings = array(
      "aufeinenblinkSettingsID" => 0,
      "aufeinenblickHourCanceltoday" => 15,
      "aufeinenblickShowVplan" => 1,
      "aufeinenblickShowCalendar" => 1,
      "aufeinenblickShowStundenplan" => 0
  );

  protected $helpPage = "";

  public function __construct() {


    if ( DB::getSettings()->getValue("aufeinenblick-off") ) {
      header("Location: index.php?page=dashboard", true, 302);
    }

    parent::__construct ( array (
        "Auf einen Blick"
    ) );

    $this->checkLogin();

    $stundenplanACL = stundenplan::userHasAccess(DB::getSession()->getUser());
    $stundenplanURL = 'index.php?page=stundenplan';
    if (DB::getSettings()->getBoolean('ext-stundenplan-global')) {
      $stundenplanURL = 'index.php?page=ext_stundenplan';
    }



    if ( $_REQUEST['action'] == '' ) {

        if(!DB::getSession()->isTeacher() && !DB::getSession()->isPupil() && !DB::getSession()->isEltern()) {

          $HTML = "";

          $upload = DB::getSettings()->getUpload('aufeinenblick-img');
          $headline = DB::getSettings()->getValue('aufeinenblick-headline');
          $text = DB::getSettings()->getValue('aufeinenblick-text');
          if ($upload || $headline || $text ) {
            $HTML .= '<div class="box"><div class="box-body">';
            if($headline != null) {
              $HTML .= "<h2>".nl2br($headline)."</h2>";
            }
            if($upload != null) {
              $HTML .= "<br><img src='index.php?page=aufeinenblick&action=aufeinenblickImg' />";
            }
            $text = DB::getSettings()->getValue('aufeinenblick-text');
            if($text != null) {
              $HTML .= "<h4>".nl2br($text)."</h4>";
            }
            $HTML .= '</div></div>';
          }

          eval("DB::getTPL()->out(\"" . DB::getTPL()->get("index") . "\");");

          PAGE::kill(true);

          //exit(0);
        }
    }
    $this->loadMySettings();
  }

  /**
   * Auf einen Blick für nicht Lehrer
   */
  private function aufEinenBlickNotSLE() {

  }

  private function loadMySettings() {
    $mySettings = DB::getDB()->query_first("SELECT * FROM aufeinenblick_settings WHERE aufeinenblickUserID='" . DB::getUserID() . "'");

    if($mySettings['aufeinenblickSettingsID'] > 0) {
      $this->mySettings = $mySettings;
    }

  }

  public function execute() {


    if($_REQUEST['action'] == 'aufeinenblickImg') {
      $upload = DB::getSettings()->getUpload('aufeinenblick-img');
      if($upload != null) $upload->sendFile();
      exit;
    }


    if($_GET['mode'] == "settings") {
      // Einstellungen

      $hourSelect = "";

      for($i = 1; $i < 24; $i++) {
        $hourSelect .= "<option value=\"" . $i . "\"" . (($this->mySettings['aufeinenblickHourCanceltoday'] == $i) ? (" selected=\"selected\"") : ("")) . "\">" . $i . ":00 Uhr</option>\n";
      }

      $vplanShowSelect = "<option value=\"1\"" . (($this->mySettings['aufeinenblickShowVplan'] > 0) ? (" selected=\"selected\"") : ("")) . ">Ausklappen</option>";
      $vplanShowSelect .= "<option value=\"0\"" . (($this->mySettings['aufeinenblickShowVplan'] == 0) ? (" selected=\"selected\"") : ("")) . ">Einklappen</option>";

      $stundenplanShowSelect = "<option value=\"1\"" . (($this->mySettings['aufeinenblickShowStundenplan'] > 0) ? (" selected=\"selected\"") : ("")) . ">Ausklappen</option>";
      $stundenplanShowSelect .= "<option value=\"0\"" . (($this->mySettings['aufeinenblickShowStundenplan'] == 0) ? (" selected=\"selected\"") : ("")) . ">Einklappen</option>";

      $calendarShowSelect = "<option value=\"1\"" . (($this->mySettings['aufeinenblickShowCalendar'] > 0) ? (" selected=\"selected\"") : ("")) . ">Ausklappen</option>";
      $calendarShowSelect .= "<option value=\"0\"" . (($this->mySettings['aufeinenblickShowCalendar'] == 0) ? (" selected=\"selected\"") : ("")) . ">Einklappen</option>";


      if($_GET['save'] > 0) {
        // Abspeichern
        for($i = 1; $i < 24; $i++) {
          if($_POST['canceltoday'] ==  $i) {
            $this->mySettings['aufeinenblickHourCanceltoday'] = $i;
            break;
          }
        }

        if($_POST['vplanShow'] > 0) {
          $this->mySettings['aufeinenblickShowVplan'] = 1;
        }
        else {
          $this->mySettings['aufeinenblickShowVplan'] = 0;
        }

        if($_POST['calendarShow'] > 0) {
          $this->mySettings['aufeinenblickShowCalendar'] = 1;
        }
        else {
          $this->mySettings['aufeinenblickShowCalendar'] = 0;
        }

        if($_POST['stundenplanShow'] > 0) {
          $this->mySettings['aufeinenblickShowStundenplan'] = 1;
        }
        else {
          $this->mySettings['aufeinenblickShowStundenplan'] = 0;
        }

        if($this->mySettings['aufeinenblickSettingsID'] > 0) {
          // Update
          DB::getDB()->query("UPDATE aufeinenblick_settings SET
            aufeinenblickHourCanceltoday = {$this->mySettings['aufeinenblickHourCanceltoday']},
            aufeinenblickShowVplan = {$this->mySettings['aufeinenblickShowVplan']},
            aufeinenblickShowCalendar = {$this->mySettings['aufeinenblickShowCalendar']},
            aufeinenblickShowStundenplan = {$this->mySettings['aufeinenblickShowStundenplan']}
          WHERE
            aufeinenblickSettingsID={$this->mySettings['aufeinenblickSettingsID']}
          ");
        }
        else {
          DB::getDB()->query("INSERT INTO aufeinenblick_settings
            (aufeinenblickUserID,
            aufeinenblickHourCanceltoday,
            aufeinenblickShowVplan,
            aufeinenblickShowCalendar,
            aufeinenblickShowStundenplan)
              values
            (
              " . DB::getSession()->getUserID() . ",
              " . $this->mySettings['aufeinenblickHourCanceltoday'] . ",
              " . $this->mySettings['aufeinenblickShowVplan'] . ",
              " . $this->mySettings['aufeinenblickShowCalendar'] . ",
              " . $this->mySettings['aufeinenblickShowStundenplan'] . "
            )
          ");
        }

        header("Location: index.php");
        exit(0);
      }


      eval("echo(\"" . DB::getTPL()->get("aufeinenblick/settings/index") . "\");");

      PAGE::kill(true);
      //exit(0);
    }
    //TODO: wirft ein fehler | from chris apr 2021
    //Stundenplan::getCurrentStunde();

    $stundenplanACL = stundenplan::userHasAccess(DB::getSession()->getUser());
    $stundenplanURL = 'index.php?page=stundenplan';
    if (DB::getSettings()->getBoolean('ext-stundenplan-global')) {
      $stundenplanURL = 'index.php?page=ext_stundenplan';
    }


    // Stundenplan heute laden

    // $stundenplan = stundenplandata::getCurrentStundenplan();

    // if($stundenplan == null) {
    // eval("echo(\"" . DB::getTPL()->get("aufeinenblick/nocurrentstundenplan") . "\");");
    // PAGE::kill(true);
    // exit(0);
    // }

    // 0: Sonntag ... 6: Samstag
    // Im Stundenplan: 0: Montag
    $dayOfWeek = date ( "w" );
    $dayOfWeekFinal = date ( "w" );

    if ($dayOfWeek == 0)
      $dayOfWeek = 6;
    else
      $dayOfWeek --;

    $dayOfWeekOne = 0;
    $dayOfWeekTwo = 0;

    $messageNextDay = "";

    if ($dayOfWeek > 4) {
      // Samstag oder Sonntag
      $dayOfWeekOne = 0;
      $dayOfWeekTwo = 1;

      if ($dayOfWeek == 5) {
        // Samstag
        $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 2 DAY) AS DATUM" );
        $dateDay1 = $date ['DATUM'];

        $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 3 DAY) AS DATUM" );
        $dateDay2 = $date ['DATUM'];
      }

      if ($dayOfWeek == 6) {
        // Samstag
        $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 1 DAY) AS DATUM" );
        $dateDay1 = $date ['DATUM'];

        $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 2 DAY) AS DATUM" );
        $dateDay2 = $date ['DATUM'];
      }
    } else {
      if (date ( "H" ) < $this->mySettings['aufeinenblickHourCanceltoday']) {

        // Vor 16 Uhr normal machen.
        $dayOfWeekOne = $dayOfWeek;

        $date = DB::getDB ()->query_first ( "SELECT CURDATE() AS DATUM" );
        $dateDay1 = $date ['DATUM'];

        if (($dayOfWeek + 1) > 4) {
          $dayOfWeekTwo = 0;

          if (($dayOfWeek + 1) == 5) {
            // Samstag
            $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 3 DAY) AS DATUM" );
          } else {
            // Sonntag
            $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 2 DAY) AS DATUM" );
          }

          $dateDay2 = $date ['DATUM'];
        } else {
          $date = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 1 DAY) AS DATUM" );
          $dateDay2 = $date ['DATUM'];

          $dayOfWeekTwo = $dayOfWeek + 1;
        }
      } else {
        $messageNextDay = "<div class=\"callout callout-info\">Ab " . $this->mySettings['aufeinenblickHourCanceltoday'] . ":00 Uhr werden die nächsten zwei Tage angezeigt. <small>Die Uhrzeit kann über \"Diese Seite anpassen\" eingestellt werden.</small></div>";

        $dayOfWeekOne = $dayOfWeek + 1;

        if ($dayOfWeekOne > 4) { // Am Freitag ist der nächste Tag Samstag, also: Montag und Dienstag
          $dayOfWeekOne = 0;
          $dayOfWeekTwo = 1;

          $dateDay1 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 3 DAY) AS DATUM" );
          $dateDay2 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 4 DAY) AS DATUM" );
          $dateDay1 = $dateDay1 ['DATUM'];
          $dateDay2 = $dateDay2 ['DATUM'];
        } elseif ($dayOfWeekOne == 4) { // Donnerstag
          $dayOfWeekTwo = 0;
          $dateDay1 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 1 DAY) AS DATUM" );
          $dateDay2 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 4 DAY) AS DATUM" );
          $dateDay1 = $dateDay1 ['DATUM'];
          $dateDay2 = $dateDay2 ['DATUM'];
        } else {
          $dayOfWeekTwo = $dayOfWeekOne + 1;
          $dateDay1 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 1 DAY) AS DATUM" );
          $dateDay2 = DB::getDB ()->query_first ( "SELECT DATE_ADD(CURDATE(),INTERVAL 2 DAY) AS DATUM" );
          $dateDay1 = $dateDay1 ['DATUM'];
          $dateDay2 = $dateDay2 ['DATUM'];
        }
      }
    }

    // Ferien?

    $ferien1 = DB::getDB ()->query_first ( "SELECT * FROM kalender_ferien WHERE ferienStart <= '$dateDay1' AND ferienEnde >= '$dateDay1'" );
    $ferien2 = DB::getDB ()->query_first ( "SELECT * FROM kalender_ferien WHERE ferienStart <= '$dateDay2' AND ferienEnde >= '$dateDay2'" );

    if ($ferien1 ['ferienID'] > 0) {
      // Beide Tage
      $dateDay1 = DB::getDB ()->query_first ( "SELECT DATE_ADD((SELECT ferienEnde FROM kalender_ferien WHERE ferienID='" . $ferien1 ['ferienID'] . "' LIMIT 1),INTERVAL 1 DAY) AS DATUM" );
      $dateDay1 = $dateDay1 ['DATUM'];

      $dateDay2 = DB::getDB ()->query_first ( "SELECT DATE_ADD((SELECT ferienEnde FROM kalender_ferien WHERE ferienID='" . $ferien1 ['ferienID'] . "' LIMIT 1),INTERVAL 2 DAY) AS DATUM" );
      $dateDay2 = $dateDay2 ['DATUM'];

      list ( $year, $month, $day ) = explode ( "-", $dateDay1 );
      $dayOfWeekOne = date ( "w", mktime ( 23, 10, 10, $month, $day, $year ) );

      if ($dayOfWeekOne == 0)
        $dayOfWeekOne = 6;
      else
        $dayOfWeekOne --;

      list ( $year, $month, $day ) = explode ( "-", $dateDay2 );
      $dayOfWeekTwo = date ( "w", mktime ( 23, 10, 10, $month, $day, $year ) );

      if ($dayOfWeekTwo == 0)
        $dayOfWeekTwo = 6;
      else
        $dayOfWeekTwo --;
    } elseif ($ferien2 ['ferienID'] > 0) {
      $dateDay2 = DB::getDB ()->query_first ( "SELECT DATE_ADD((SELECT ferienEnde FROM kalender_ferien WHERE ferienID='" . $ferien2 ['ferienID'] . "' LIMIT 1),INTERVAL 1 DAY) AS DATUM" );
      $dateDay2 = $dateDay2 ['DATUM'];

      // Wochentag bestimmen
      list ( $year, $month, $day ) = explode ( "-", $dateDay2 );
      $dayOfWeekTwo = date ( "w", mktime ( 23, 10, 10, $month, $day, $year ) );

      if ($dayOfWeekTwo == 0)
        $dayOfWeekTwo = 6;
      else
        $dayOfWeekTwo --;
    }

    $HTML = "";

    $upload = DB::getSettings()->getUpload('aufeinenblick-img');
    $headline = DB::getSettings()->getValue('aufeinenblick-headline');
    $text = DB::getSettings()->getValue('aufeinenblick-text');
    if ($upload || $headline || $text ) {
      $HTML .= '<div class="box"><div class="box-body">';
      if($headline != null) {
        $HTML .= "<h2>".nl2br($headline)."</h2>";
      }
      if($upload != null) {
        $HTML .= "<br><img src='index.php?page=aufeinenblick&action=aufeinenblickImg' />";
      }
      $text = DB::getSettings()->getValue('aufeinenblick-text');
      if($text != null) {
        $HTML .= "<h4>".nl2br($text)."</h4>";
      }
      $HTML .= '</div></div>';
    }



    if (DB::getSession ()->isTeacher ()) {

      $stundenplanLehrer = stundenplandata::getStundenplanAtDate ( $dateDay1 );
      $stundenplanLehrer = $stundenplanLehrer->getPlan ( array (
          "teacher",
          DB::getSession ()->getTeacherObject()->getKuerzel()
      ) );

      $stundenplanDay1 = $stundenplanLehrer [$dayOfWeekOne];

      $HTML .= $this->getToday ( array (
          $stundenplanDay1
      ), "Lehrer", $dateDay1, $dayOfWeekOne, array (
          DB::getSession ()->getTeacherObject()->getKuerzel()
      ) );

      $stundenplanLehrer = stundenplandata::getStundenplanAtDate ( $dateDay2 );
      $stundenplanLehrer = $stundenplanLehrer->getPlan ( array (
          "teacher",
          DB::getSession ()->getTeacherObject()->getKuerzel()
      ) );
      $stundenplanDay2 = $stundenplanLehrer [$dayOfWeekTwo];
      $HTML .= $this->getToday ( array (
          $stundenplanDay2
      ), "Lehrer", $dateDay2, $dayOfWeekTwo, array (
          DB::getSession ()->getTeacherObject()->getKuerzel()
      ) );
    } else {
      if(DB::getSession()->isPupil()) $grades = array(DB::getSession()->getSchuelerObject()->getKlasse());
      else if(DB::getSession()->isEltern()) $grades = DB::getSession()->getElternObject()->getKlassenAsArray();

      $plaeneDay1 = array ();
      $plaeneDay2 = array ();

      for($i = 0; $i < sizeof ( $grades ); $i ++) {

        $stPlan = stundenplandata::getStundenplanAtDate ( $dateDay1 );

        $stPlan = $stPlan->getPlan ( array (
            "grade",
            $grades [$i] . "%"
        ) );

        $plaeneDay1 [] = $stPlan [$dayOfWeekOne];

        $stPlan = stundenplandata::getStundenplanAtDate ( $dateDay2 );

        $stPlan = $stPlan->getPlan ( array (
            "grade",
            $grades [$i] . "%"
        ) );

        $plaeneDay2 [] = $stPlan [$dayOfWeekTwo];
      }

      if(!is_array($grades)) $grades = array();

      $HTML .= $this->getToday ( (array)$plaeneDay1, implode(", ",$grades), $dateDay1, $dayOfWeekOne, $grades );

      $HTML .= $this->getToday ( (array)$plaeneDay2, implode(", ",$grades), $dateDay2, $dayOfWeekTwo, $grades );
    }

    // eval("\$indexStatus = \"".DB::getTPL()->get("index_loggedin")."\";");


    $showKlassentagebuchButton = false;

    if($this->isActive("klassentagebuch")) {
      if(DB::getSession()->isEltern() && DB::getSettings()->getBoolean('klassentagebuch-eltern-klassentagebuch'))
        $showKlassentagebuchButton = true;

      else if(DB::getSession()->isTeacher())
        $showKlassentagebuchButton = true;
      else if(DB::getSession()->isPupil() && DB::getSettings()->getBoolean('klassentagebuch-schueler-klassentagebuch'))
        $showKlassentagebuchButton = true;

      else if(DB::getSession()->isMember('Webportal_Klassentagebuch_Lesen'))
        $showKlassentagebuchButton = true;

      if(DB::getSettings()->getBoolean('klassentagebuch-lehrertagebuch') && DB::getSession()->isTeacher())
        $showLehrerTagebuchButton = true;
    }

    $nachrichtenURL = 'index.php?page=MessageInbox';
    if (DB::getSettings()->getBoolean('extInbox-global-messageSystem')) {
      $nachrichtenURL = 'index.php?page=ext_inbox';
    }

    eval ( "echo(\"" . DB::getTPL ()->get ( "aufeinenblick/index" ) . "\");" );
  }

  private function getToday($plan, $title, $datum, $dayOfWeek, $planTitles) {
    $datumTermine = $datum;

    $datumShow = functions::getDayName ( $dayOfWeek ) . ", " . functions::getFormatedDateFromSQLDate ( $datum );

    // $currentStundenplanID = stundenplandata::getCurrentStundenplanID();

    $maxCells = array();




    for($i = 0; $i < sizeof ( (array)$plan ); $i ++) {
      for($s = 0; $s < sizeof ( (array)$plan [$i] ); $s ++) {

        if ( sizeof( (array)$plan[$i][$s] ) > $maxCells[$i]) {
          $maxCells[$i] = sizeof ( (array)$plan [$i] [$s] );
        }
      }
    }

    $stundenplanHTML = "<table class=\"table table-striped\">";
    $stundenplanHTML .= "<tr><th width=\"5%\">Stunde</th>";

    for($i = 0; $i < sizeof ( $plan ); $i ++) {
      $stundenplanHTML .= "<th colspan=\"{$maxCells[$i]}\">" . functions::getDayName ( $dayOfWeek ) . "<br />" . $planTitles [$i] . "</th>";
    }

    $stundenplanHTML .= "</tr>";

    for($i = 0; $i < sizeof ( (array)$plan [0] ); $i ++) {
      $stundenplanHTML .= "<tr><td>" . ($i + 1) . "</td>";

      for($p = 0; $p < sizeof ( (array)$plan ); $p ++) {

        $usedCols = 0;
        for($s = 0; $s < sizeof ( (array)$plan [$p] [$i] ); $s ++) {
          $usedCols ++;

          if ($s == (sizeof ( (array)$plan [$p] [$i] ) - 1)) {
            $colspan = $maxCells [$p] - $usedCols + 1;
          } else
            $colspan = 1;

          $stundenplanHTML .= "<td  width=\"10\" colspan=\"$colspan\"><b>" . $plan [$p] [$i] [$s] ['subject'] . "</b> - " . $plan [$p] [$i] [$s] ['grade'] . "<br />";

          $stundenplanHTML .= $plan [$p] [$i] [$s] ['room'] . " - " . $plan [$p] [$i] [$s] ['teacher'] . "</td>";
        }

        if ($usedCols == 0)
          $stundenplanHTML .= "<td colspan=\"{$maxCells[$p]}\">&nbsp;<br />&nbsp;</td>";
      }

      $stundenplanHTML .= "</tr>";
    }

    /**
     * for($i = 0; $i < sizeof($plan[0]); $i++) {
     * $stundenplanHTML .
     * = "<th>" . ($i+1) . ". Stunde</th>";
     * }
     * $stundenplanHTML .= "</tr>";
     *
     * for($p = 0; $p < sizeof($plan); $p++) {
     *
     * $stundenplanHTML .= "<tr>";
     *
     * $stundenplanHTML .= "<td><b>" . functions::getDayName($dayOfWeek) . "</b><br />" . $planTitles[$p] . "</td>";
     *
     * for($i = 0; $i < sizeof($plan[$p]); $i++) {
     * $colsUsed = 0;
     * $stundenplanHTML .= "<td>";
     *
     * if(sizeof($plan[$i] == 0)) $stundenplanHTML .= "&nbsp;";
     *
     * for($s = 0; $s < sizeof($plan[$p][$i]); $s++) {
     *
     * if($s > 0) $stundenplanHTML .= "<hr noshade>";
     * $stundenplanHTML .= $plan[$p][$i][$s]['subject'] . " - " . $plan[$p][$i][$s]['grade'] . "<br />";
     *
     * $stundenplanHTML .= $plan[$p][$i][$s]['room'] . " - " . $plan[$p][$i][$s]['teacher'];
     *
     *
     * }
     * $stundenplanHTML .= "</td>";
     * }
     *
     * $stundenplanHTML .= "</tr></td>";
     * }
     */

    $stundenplanHTML .= "</table>";

    // Vertretungsplan

    $planFound = false;
    $plaene = DB::getDB ()->query ( "SELECT * FROM vplan WHERE " . ((DB::getSession ()->isTeacher ()) ? (" vplanName LIKE 'lehrer%'") : (" vplanName LIKE 'schueler%'")) );
    while ( $plan = DB::getDB ()->fetch_array ( $plaene ) ) {
      $lastUpdate = $plan ['vplanUpdate'];

      $date = $plan ['vplanDate'];
      $date = explode ( ", ", $date );

      list ( $day, $month, $year ) = explode ( ".", $date [1] );

      list ( $datumYear, $datumMonth, $datumDay ) = explode ( "-", $datum );

      if ($day == $datumDay && $month == $datumMonth && $year == $datumYear) {
        // Plan gefunden
        $planFound = true;

        // Eigene Einträge suchen

        if (DB::getSession ()->isTeacher ()) {
          if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
            if(DB::getGlobalSettings()->stundenplanSoftwareVersion == "2" || DB::getGlobalSettings()->stundenplanSoftwareVersion == "1") {

              $search = DB::getSession ()->getTeacherObject()->getKuerzel();


              $search = ">" . strtolower ( $search );
            }
            else {

              $search = DB::getSession ()->getTeacherObject()->getName();


              $search = ">" . strtolower ( $search );
            }


            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"><tr><th>Vertretung</th><th>Stunde</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</tr>\r\n</th></tr>";

          }

          if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
            $search = DB::getSession ()->getTeacherObject()->getKuerzel();
            $search = ">" . strtolower ( $search );

            $header = "<table class=\"mon_list\" style=\"width:50%\">";
            // $header .= '<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>';
            $header .= explode("\n", $plan ['vplanContent'])[1];
          }

          if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
            $search = DB::getSession()->getTeacherObject()->getName();

            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Lehrer</th><th>Std.</th><th>Klasse</th><th>Fach</th><th>Raum</th><th>für</th><th>Bemerkung</th></tr>";


          }


        } else if(DB::getSession()->isPupil() ) {
          $search = DB::getSession ()->getPupilObject()->getKlasse();
          $search = strtolower ( $search );
          $searchText = strtoupper ( $searchText );

          if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
            $header = "<table class=\"mon_list\" style=\"width:50%\">";
            $header .= explode("\n", $plan ['vplanContent'])[1];
            // $header .= '<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>';

          }

          if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"><tr><th>Vertretung</th><th>Stunde</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</tr>\r\n</th></tr>";
          }

          if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
            $searchText = strtoupper( $searchText );
            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Klasse</th><th>Std.</th><th>Lehrer/Fach</th><th>vertr. durch</th><th>Fach</th><th>Raum</th><th>Bemerkung</th></tr>";


          }
        }
        else if(DB::getSession()->isEltern()) {

          if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
            $header = "<table class=\"mon_list\" style=\"width:50%\">";
            $header .= explode("\n", $plan ['vplanContent'])[1];
            // $header .= '<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>';

          }

          if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"><tr><th>Vertretung</th><th>Stunde</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</tr>\r\n</th></tr>";
          }


          if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
            $searchText = strtoupper( $searchText );
            $header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"><tr><th>Klasse</th><th>Vertretung</th><th>Stunde</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</th></tr>";


          }

          $search = DB::getSession()->getElternObject()->getKlassenAsArray();
          // Multi - Klassen
          $multisearch = true;

          if ($multisearch)
            $searchText = implode ( ", ", $search );
          else
            $searchText = $search;
        }


        $content = $plan ['vplanContent'];

        $update = "(Letzte Aktualisierung: " . $plan ['vplanUpdate'] . ")";

        $ownData = array();

        $cont = explode ( "\n", str_replace ( "\r", "", $content ) );
        for($c = 0; $c < sizeof ( $cont ); $c ++) {
          if ($multisearch) {
            for($s = 0; $s < sizeof ( $search ); $s ++) {
              if (strpos ( strtolower ( $cont [$c] ), strtolower($search [$s]) ) > 0) {
                $ownData [] = $cont [$c];
              }
            }
          } else {
            if (strpos ( strtolower ( $cont [$c] ), strtolower($search) ) > 0) {
              $ownData [] = $cont [$c];
            }
          }
        }

        /*
         * 				if(!$deleteOwnDataTable && !$this->noSearch ) {
          for($c = 0; $c < sizeof($cont); $c++) {
            if($multisearch) {
              for($s = 0; $s < sizeof($search); $s++) {
                if(strpos(strtolower($cont[$c]), strtolower($search[$s])) > 0) {
                  $ownData[] = $cont[$c];
                }
              }
            }
            else {
              if(strpos(strtolower($cont[$c]), strtolower($search)) > 0) {
                $ownData[] = $cont[$c];
              }
            }
          }

        }
         */

        if (sizeof ( $ownData ) == 0) {
          $ownData = "<tr><td align=\"center\" colspan=\"10\">-- Keine --</td></tr>";
        } else {
          $ownData = implode ( "\r\n", $ownData );
        }

        $ownData .= "</table><br />";

        $vertretungsplanHTML = $header . $ownData . str_replace ( "width=\"100%\"", "", $plan ['vplanInfo'] );
      }

      if (! $planFound) {
        $vertretungsplanHTML = "<p class=\"text-red\">Für diesen Tag ist noch kein Vertretungsplan verfügbar.</p>";
        $update = "";
      }

      // Klassenkalender

    }
    $eventSources = [];



    $lnwHTML = "";

    if (DB::getSession ()->isTeacher ()) {
      $lehrer = DB::getSession ()->getTeacherObject()->getKuerzel();

      $classes = klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());

      for($i = 0; $i < sizeof($classes); $i++) {
        $eventSources[] = 'index.php?page=klassenkalender&grade=' . urlencode($classes[$i]->getKlassenName()) . '&action=getJSONData&showGrade=1';
      }

    } else {

      $grades = [];

      if(DB::getSession()->isEltern()) {
        $grades = DB::getSession()->getElternObject()->getKlassenObjectsAsArray();
      }
      else if(DB::getSession()->isPupil()) {
        $grades = [DB::getSession()->getPupilObject()->getKlassenObjekt()];
      }

      for($i = 0; $i < sizeof($grades); $i++) {
        $eventSources[] = 'index.php?page=klassenkalender&grade=' . urlencode($grades[$i]->getKlassenName()) . '&action=getJSONData&showGrade=1';
      }
    }

    // Andere Kalender

    $andereKalender = andereKalender::getKalenderWithAccess();

    for($i = 0; $i < sizeof($andereKalender); $i++) {
      $eventSources[] = 'index.php?page=andereKalender&kalenderID=' . $andereKalender[$i]['kalenderID']. '&action=getJSONData';
    }

    // Externe Kalender

    $externeKalender = extKalender::getKalenderWithAccess();

    for($i = 0; $i < sizeof($externeKalender); $i++) {
      $eventSources[] = 'index.php?page=extKalender&kalenderID=' . $externeKalender[$i]['kalenderID']. '&action=getJSONData';
    }


    $calFeeds = "";

    $calFeeds = implode("','",$eventSources);

    if($calFeeds != "") $calFeeds = "'" . $calFeeds . "'";

    $vplanCollapse = (($this->mySettings['aufeinenblickShowVplan'] == 0) ? (" collapsed-box") : (""));
    $calendarCollapse = (($this->mySettings['aufeinenblickShowCalendar'] == 0) ? (" collapsed-box") : (""));
    $stundenplanCollapse = (($this->mySettings['aufeinenblickShowStundenplan'] == 0) ? (" collapsed-box") : (""));

    $faVplanIcon = (($this->mySettings['aufeinenblickShowVplan'] == 0) ? ("fa-plus") : ("fa-minus"));
    $faCalendarIcon = (($this->mySettings['aufeinenblickShowCalendar'] == 0) ? ("fa-plus") : ("fa-minus"));
    $faStundenplanIcon = (($this->mySettings['aufeinenblickShowStundenplan'] == 0) ? ("fa-plus") : ("fa-minus"));


    eval ( "\$html = \"" . DB::getTPL ()->get ( "aufeinenblick/heutemorgen" ) . "\";" );


    return $html;
  }

  public static function hasSettings() {
    return true;
  }

  public static function getSiteDisplayName() {
    return "Auf einen Blick";
  }

  public static function getSettingsDescription() {
    return array(
        [
            'name' => 'aufeinenblick-img',
            'typ' => 'BILD',
            'titel' => 'Einleitung- Bild',
            'text' => ''
        ],
        [
            'name' => 'aufeinenblick-headline',
            'typ' => 'ZEILE',
            'titel' => 'Einleitung - Überschrift',
            'text' => ''
        ],
        [
            'name' => 'aufeinenblick-text',
            'typ' => 'TEXT',
            'titel' => 'Einleitung - Text',
            'text' => ''
        ]
    );
  }

  public static function getUserGroups() {
    return array();
  }

  public static function siteIsAlwaysActive() {
    return true;
  }

  public static function hasAdmin() {
    return true;
  }

  public static function getAdminGroup() {
    return 'Webportal_Aufeinenblick_admin';
  }

  public static function displayAdministration($selfURL) {
    return "";
  }

  public static function getAdminMenuGroup() {
    return 'Seiteneinstellungen';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-file';
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-calendar-check';
  }

}

?>
