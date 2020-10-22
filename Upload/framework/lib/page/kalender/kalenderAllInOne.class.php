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

      $ret = [];
      $result = DB::getDB()->query("SELECT * FROM kalender_allInOne_eintrag WHERE kalenderID = ".intval($kalenderID));
      while($row = DB::getDB()->fetch_array($result)) {
        
        $createdUser = new user(array('userID' => intval($row['eintragUserID']) ));

        $ret[] = [
          'id' => $row['kalenderID'].'-'.$row['eintragID'],
          'title' => DB::getDB()->decodeString($row['eintragTitel']),
          'from_date' => strtotime($row['eintragDatumStart'].' '.$row['eintragTimeStart']),
          'end_date' => strtotime($row['eintragDatumEnde'].' '.$row['eintragTimeEnde']),
          'location' => DB::getDB()->decodeString($row['eintragOrt']),
          'text' => DB::getDB()->decodeString($row['eintragKommentar']),
          'creation_date' => strtotime($row['eintragCreatedTime'])
        ];

      }
      
      // echo "<pre>";
      // print_r($ret);
      // echo "</pre>";


      //$feed = new ICSFeed2( $ret );

      // echo "<pre>";
      // print_r($feed);
      // echo "</pre>";

      //$feed->getFile('filename.ics');

      // header('Content-Type: text/calendar; charset=utf-8');
      // header('Content-Disposition: attachment; filename="cal.ics"');

      // $str = $feed->getString();
      // echo $str;

      
      // $feed = ICSFeed::getAndererKalenderFeed($this->kalenderID, DB::getUserID());

      // echo json_encode([
      //     'feedURL' => $feed->getURL(),
      // ]);



      // $event = (new Eluceo\iCal\Component\Event())
      // ->setDtStart( gmstrftime("%Y%m%dT%H%M00Z", $ret[0]['from_date'] ) )
      // ->setDtEnd( gmstrftime("%Y%m%dT%H%M00Z", $ret[0]['from_date'] ) )
      // //->setNoTime( $ret[0][''] )
      // ->setSummary( $ret[0]['title'] )
      // ->setLocation( $ret[0]['location'] )
      // ->setDescription( $ret[0]['text'] );


      use Eluceo\iCal\Domain\Entity\Calendar;
      use Eluceo\iCal\Domain\Entity\Event;
      use Eluceo\iCal\Domain\ValueObject\Alarm;
      use Eluceo\iCal\Domain\ValueObject\Attachment;
      use Eluceo\iCal\Domain\ValueObject\DateTime;
      use Eluceo\iCal\Domain\ValueObject\TimeSpan;
      use Eluceo\iCal\Domain\ValueObject\Uri;
      use Eluceo\iCal\Presentation\Factory\CalendarFactory;

      require_once __DIR__ . '/../vendor/autoload.php';

      // 1. Create Event domain entity
      $event = (new Eluceo\iCal\Domain\Entity\Event())
          ->setSummary('Christmas Eve')
          ->setDescription('Lorem Ipsum Dolor...')
          ->setOccurrence(
              new Eluceo\iCal\Domain\ValueObject\SingleDay(
                  new Eluceo\iCal\Domain\ValueObject\Date(
                      \DateTimeImmutable::createFromFormat('Y-m-d', '2030-12-24')
                  )
              )
          );

      // 2. Create Calendar domain entity
      $calendar = new Eluceo\iCal\Domain\Entity\Calendar([$event]);

      // 3. Transform domain entity into an iCalendar component
      $componentFactory = new Eluceo\iCal\Presentation\Factory\CalendarFactory();
      $calendarComponent = $componentFactory->createCalendar($calendar);

      // 4. Set headers
      header('Content-Type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="cal.ics"');

      // 5. Output
      echo $calendarComponent;



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

