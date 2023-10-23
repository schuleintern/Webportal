<?php

class index extends AbstractPage {

  public function __construct() {
    parent::__construct ( array (
      "Auf einen Blick"
    ) );

    $this->checkLogin();

  }

  public function execute() {

    if($_REQUEST['action'] == "closeFremdlogin") {
        $fremdlogin = Fremdlogin::getMyFremdlogin();

        $result = [
            'success' => false,
            'message' => ''
        ];

        if(DB::getSession()->isDebugSession()) {
            $result['message'] = 'Sie können den Hinweis für den Benutzer nicht in der Debug Session schließen!';
        }

        else if($fremdlogin != null) {
            $fremdlogin->delete();
            $result['success'] = true;
        }

        header("Content-type: text/json");
        echo json_encode($result);
        exit(0);
    }

    if($this->isActive("klassentagebuch")) {
        if(DB::getSession()->isTeacher() && !DB::getSettings()->getBoolean('klassentagebuch-klassentagebuch-abschalten') && stundenplandata::getCurrentStundenplan() != null) {
            $currentStunde = stundenplan::getCurrentStunde();

            if($currentStunde > 0)  {
                $aktuelleStunden = stundenplandata::getCurrentStundenplan()->getStundenAtDayAndStundeForTeacher(date("N"), $currentStunde, DB::getSession()->getTeacherObject()->getKuerzel());
            }
            else $aktuelleStunden = [];

            if(sizeof($aktuelleStunden) > 0) {
                header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $aktuelleStunden[0]['grade']);
                exit(0);
            }
        }
    }

    if(DB::$mySettings['startPage'] != "") header("Location: index.php?page=" . DB::$mySettings['startPage']);
    else header("Location: index.php?page=aufeinenblick");

    exit();
  }

  public static function getSettingsDescription() {
    $settings = array();

    $settings[] = array(
      'name' => 'general-wartungsmodus',
      'typ' => 'BOOLEAN',
      'titel' => "Wartungsmodus aktiv?",
      'text' => 'Im Wartungsmodus können nur Administratoren die Seite nutzen. Alle anderen sehen einen Hinweis auf die Wartungsarbeiten.'
    );
    $settings[] = array(
      'name' => 'general-wartungsmodus-text',
      'typ' => 'TEXT',
      'titel' => "Text für die Seite: Wartungsmodus",
      'text' => ''
    );

    $settings[] = array(
      'name' => 'general-internmodus',
      'typ' => 'BOOLEAN',
      'titel' => "Verwaltungsmodus aktiv?",
      'text' => 'Im Verwaltungsmodus können nur Administratoren, Lehrer und Mitarbeiter die Seite nutzen. Eltern und Schüler sehen einen Hinweis auf die Wartungsarbeiten.'
    );


    /*$settings[] = array(
        'name' => 'general-homepage',
        'typ' => 'ZEILE',
        'titel' => "Homepage",
        'text' => 'Link zur Homepage der Schule. (Mit http:// oder https://)'
    );*/

    return $settings;
  }

  public static function getSiteDisplayName() {
    return "Schuljahr / Wartungsmodus";
  }

  public static function hasSettings() {
    return true;
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
    return 'Webportal_Admin_General_Settings';
  }

  public static function getAdminMenuGroup() {
    return 'Allgemeine Einstellungen';
  }

  public static function displayAdministration($selfURL) {
    /**
     *
    $settings[] = array(
        'name' => 'general-schuljahr',
        'typ' => 'ZEILE',
        'titel' => "Aktuelles Schuljahr",
        'text' => 'Aktuelles Schuljahr im Format 2015/16'
    );
     */

    if($_GET['action'] == 'doChange') {
      $newDate = $_POST['ersterSchultag'];

      if(!DateFunctions::isNaturalDate($newDate)) {
        new errorPage("Ungültiges Datum");
      }

      $newDate = DateFunctions::getMySQLDateFromNaturalDate($newDate);

      DB::getSettings()->setValue('general-schuljahr', $_REQUEST['neuesSchuljahr']);

      $alleSeiten = requesthandler::getAllowedActions();

      $htmlActions = '';

      for($i = 0; $i < sizeof($alleSeiten); $i++) {
        if($alleSeiten[$i]::getActionSchuljahreswechsel() != '' & AbstractPage::isActive($alleSeiten[$i])) {
          $alleSeiten[$i]::doSchuljahreswechsel($newDate);
        }
      }

      header("Location: $selfURL&success=1");
      exit(0);
    }

    $alleSeiten = requesthandler::getAllowedActions();

    $htmlActions = '';

    for($i = 0; $i < sizeof($alleSeiten); $i++) {
      if($alleSeiten[$i]::getActionSchuljahreswechsel() != '' & AbstractPage::isActive($alleSeiten[$i])) {
        $htmlActions .= "<tr><td>" . $alleSeiten[$i]::getSiteDisplayName() . "</td><td>" . $alleSeiten[$i]::getActionSchuljahreswechsel() . "</td></tr>";
      }
    }

    $html = '';

    eval("\$html = \"" . DB::getTPL()->get("administration/schuljahreswechsel/index") ."\";");

    return $html;
  }
}

?>
