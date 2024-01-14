<?php



class extUsersDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-calendar"></i> Users';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            new errorPage('Kein Zugriff');
        }

        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/users",
                "acl" => $acl['rights']
            ]
        ]);


    }

    public function taskIcsFeed() {


        $userID = DB::getSession()->getUserID();

        //header("Location: rest.php/GetKalenderIcsFeed/".$userID);

        if (!$userID) {
            return false;
        }

        $calendar = new Eluceo\iCal\Component\Calendar('schule-intern');
        $calendar->setPublishedTTL('PT15M');

        $tz  = 'Europe/Amsterdam';
        $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);

        $kalenderIDs_array = [];
        $result_cal = DB::getDB()->query("SELECT kalenderID, kalenderName, kalenderAcl FROM kalender_allInOne WHERE kalenderPublic = 1 ");
        while($row = DB::getDB()->fetch_array($result_cal)) {
            if ( (int)$row['kalenderAcl'] ) {

                $acl = ACL::getAcl(DB::getSession()->getUser(), false, (int)$row['kalenderAcl'], $this->getAdminGroup() );

                //$acl = self::getAclByID( (int)$row['kalenderAcl'], true, $userID );
                if ($acl && $acl['rights']['read'] == 1 ) {
                    array_push($kalenderIDs_array, array(
                        "id" => intval($row['kalenderID']),
                        "name" => $row['kalenderName']
                    ));
                }
            }
        }

        if (count($kalenderIDs_array) > 0) {

            foreach ($kalenderIDs_array as $kalender) {

                $result = DB::getDB()->query("SELECT * FROM kalender_allInOne_eintrag WHERE kalenderID = ".$kalender['id']  );

                while($row = DB::getDB()->fetch_array($result)) {

                    $event = (new Eluceo\iCal\Component\Event())
                        ->setUseTimezone(true)
                        ->setUseUtc(true)
                        ->setSummary(DB::getDB()->decodeString($row['eintragTitel']))
                        ->setCategories([$kalender['name']])
                        ->setLocation( DB::getDB()->decodeString($row['eintragOrt']) )
                        ->setDescription( DB::getDB()->decodeString($row['eintragKommentar']) );

                    if ( intval( $row['eintragTimeStart'] ) <= 0) {
                        $event->setNoTime(true);
                        $event->setDtStart(new \DateTime($row['eintragDatumStart'], $dtz));
                    } else {
                        $event->setDtStart(new \DateTime($row['eintragDatumStart'].' '.$row['eintragTimeStart'], $dtz));
                    }

                    if ( intval( $row['eintragDatumEnde'] ) <= 0) {
                        $event->setNoTime(true);
                        $event->setDtEnd(new \DateTime($row['eintragDatumStart'], $dtz));
                    } else {
                        if ( intval( $row['eintragTimeEnde'] ) <= 0) {
                            $event->setDtEnd(new \DateTime($row['eintragDatumEnde'].' 00:00:01', $dtz));
                        } else {
                            $event->setDtEnd(new \DateTime($row['eintragDatumEnde'].' '.$row['eintragTimeEnde'], $dtz));
                        }
                    }

                    $calendar->addComponent($event);
                }

            }
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="feed.ics"');

        echo $calendar->render();
        exit;


    }

}
