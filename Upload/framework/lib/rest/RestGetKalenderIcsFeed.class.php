<?php

class RestGetKalenderIcsFeed extends AbstractRest {
    protected $statusCode = 200;

    public function execute($input, $request) {



        $userID = (int)$request[1];
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

                $acl = self::getAclByID( (int)$row['kalenderAcl'], true, $userID );


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

    public function getAllowedMethod() {
        return 'GET';
    }

    protected function malformedRequest() {
        $this->statusCode = 400;
    }

    /**
     * Überprüft, ob ein Modul eine System Authentifizierung benötigt. (z.B. zum Abfragen aller Schülerdaten)
     * @return boolean
     */
    public function needsSystemAuth() {
        return true;
    }

    public function needsUserAuth() {
        return false;
    }

    public function aclModuleName() {
        return 'kalenderAllInOne-ICSFeed';
    }

    public static function getAdminGroup() {
        return 'Webportal_Kalender_allInOne_Admin';
    }

}

?>