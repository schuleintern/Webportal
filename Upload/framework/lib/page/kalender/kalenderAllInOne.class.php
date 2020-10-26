<?php

/**
 * API Kalender
 *
 * @author Christian Marienfeld
 *
 */
class kalenderAllInOne extends AbstractPage {

  protected $title = "API Kalender";

  private $kalender = [];

  public function __construct() {

    parent::__construct(["Kalender"]);
    $this->checkLogin();


  }

  public function execute() {

    $acl = json_encode( $this->getAcl() );
    //$userID = DB::getSession()->getUserID();
    

    if ( $_REQUEST['action'] == 'icsfeed') {

      
      $kalenderID = '1';

      $calendar = new Eluceo\iCal\Component\Calendar('schule-intern');
      $calendar->setPublishedTTL('PT15M');
      
      $tz  = 'Europe/Amsterdam';
	    $dtz = new \DateTimeZone($tz);
      date_default_timezone_set($tz);
      
	    /* 
        // 2. Create timezone rule object for Daylight Saving Time
        $vTimezoneRuleDst = new Eluceo\iCal\Component\TimezoneRule(new Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
        $vTimezoneRuleDst->setTzName('CEST');
        $vTimezoneRuleDst->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz));
        $vTimezoneRuleDst->setTzOffsetFrom('+0100');
        $vTimezoneRuleDst->setTzOffsetTo('+0200');
        $dstRecurrenceRule = new RecurrenceRule();
        $dstRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $dstRecurrenceRule->setByMonth(3);
        $dstRecurrenceRule->setByDay('-1SU');
        $vTimezoneRuleDst->setRecurrenceRule($dstRecurrenceRule);

        // 3. Create timezone rule object for Standard Time
        $vTimezoneRuleStd = new Eluceo\iCal\Component\TimezoneRule(new Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
        $vTimezoneRuleStd->setTzName('CET');
        $vTimezoneRuleStd->setDtStart(new \DateTime('1996-10-27 03:00:00', $dtz));
        $vTimezoneRuleStd->setTzOffsetFrom('+0200');
        $vTimezoneRuleStd->setTzOffsetTo('+0100');
        $stdRecurrenceRule = new RecurrenceRule();
        $stdRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $stdRecurrenceRule->setByMonth(10);
        $stdRecurrenceRule->setByDay('-1SU');
        $vTimezoneRuleStd->setRecurrenceRule($stdRecurrenceRule);


        // 4. Create timezone definition and add rules
        $vTimezone = new Eluceo\iCal\Component\Timezone($tz);
        $vTimezone->addComponent($vTimezoneRuleDst);
        $vTimezone->addComponent($vTimezoneRuleStd);
        $vCalendar->setTimezone($vTimezone);
      */
      
      $ret = [];
      $result = DB::getDB()->query("SELECT * FROM kalender_allInOne_eintrag WHERE kalenderID = ".intval($kalenderID));
      while($row = DB::getDB()->fetch_array($result)) {

        $event = (new Eluceo\iCal\Component\Event())
        ->setUseTimezone(true)
        ->setUseUtc(false)
        ->setSummary(DB::getDB()->decodeString($row['eintragTitel']))
        ->setNoTime(false)
        ->setDtStart(new \DateTime($row['eintragDatumStart'].' '.$row['eintragTimeStart'], $dtz))
        ->setDtEnd(new \DateTime($row['eintragDatumEnde'].' '.$row['eintragTimeEnde'], $dtz))
        ->setLocation( DB::getDB()->decodeString($row['eintragOrt']) )
        ->setDescription( DB::getDB()->decodeString($row['eintragKommentar']) );

        $calendar->addComponent($event);
      

      }
      
 
      header('Content-Type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="cal.ics"');
      echo $calendar->render();
      exit;
    }


    eval("echo(\"" . DB::getTPL()->get("kalender/kalenderAllInOne"). "\");");

  }

  



  public static function hasSettings() {
    return false;
  }


  public static function getSettingsDescription() {
    return [];
  }


  /*
   * 	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
   *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
   *      'titel' => "Titel der Beschreibung",
   *      'text' => "Text der Beschreibung"
   */


  public static function getSiteDisplayName(){
    return "Kalender AllInOne (Beta)";
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return [];
  }

  public static function hasAdmin() {
    return true;
  }

  public static function getAdminMenuGroup() {
    return 'Kalender';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-calendar';
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-male';
  }

  public static function getAdminGroup() {
    return 'Webportal_Kalender_allInOne_Admin';
  }


  public static function displayAdministration($selfURL) {


    if($_REQUEST['action'] == 'edit') {

      if (!$_POST['data']) {
        return false;
      }

      $data = json_decode($_POST['data']);
      
      if (!$data) {
        return false;
      }

      foreach($data as $item) {
        if ( $item->kalenderName ) {

          $item->kalenderAcl->aclModuleClassParent = self::aclModuleName();


          $return = ACL::setAcl( (array)$item->kalenderAcl );
          if (is_array($return) && $return['error']) {
            return $return;
          }

          if ( $item->kalenderID != 0 ) {
            $dbRow = DB::getDB()->query_first("SELECT kalenderID FROM kalender_allInOne WHERE kalenderID = " . intval($item->kalenderID) . "");
            if ($dbRow['kalenderID'] && $item->kalenderName != 'DELETE') {
              DB::getDB()->query("UPDATE kalender_allInOne SET
                kalenderName = '".DB::getDB()->escapeString($item->kalenderName)."',
                kalenderColor = '".DB::getDB()->escapeString($item->kalenderColor)."',
                kalenderSort = '".DB::getDB()->escapeString($item->kalenderSort)."',
                kalenderPreSelect = '".DB::getDB()->escapeString($item->kalenderPreSelect)."',
                kalenderFerien = '".DB::getDB()->escapeString($item->kalenderFerien)."',
                kalenderAcl = ".$return['aclID']."
                WHERE kalenderID = " . intval($item->kalenderID) . ";");
                
            } else if ($item->delete == 1 && $item->kalenderName == 'DELETE') {
              DB::getDB()->query("DELETE FROM kalender_allInOne WHERE kalenderID = ". intval($item->kalenderID));
            }
          } else {
            DB::getDB()->query("INSERT INTO kalender_allInOne (kalenderID, kalenderName, kalenderColor, kalenderSort, kalenderPreSelect, kalenderFerien, kalenderAcl ) values(
            '" . DB::getDB()->escapeString($item->kalenderID) . "',
            '" . DB::getDB()->escapeString($item->kalenderName) . "',
            '" . DB::getDB()->escapeString($item->kalenderColor) . "',
            '" . DB::getDB()->escapeString($item->kalenderSort) . "',
            '" . DB::getDB()->escapeString($item->kalenderPreSelect) . "',
            '" . DB::getDB()->escapeString($item->kalenderFerien) . "',
            ".$return['aclID']."
            )");
          }

        }
      }


			header("Location: $selfURL");
			exit();
    }



    $html = '';
		eval("\$html = \"" . DB::getTPL()->get("kalender/admin/kalenderAllInOne") . "\";");
		return $html;
  }
}

