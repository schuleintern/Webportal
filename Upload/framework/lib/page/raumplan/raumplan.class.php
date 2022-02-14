<?php


class raumplan extends AbstractPage
{


    public function __construct()
    {

        parent::__construct(array("Raumplan"));

        $this->checkLogin();


    }

    public function execute()
    {
        if ($_REQUEST['action'] == 'saveStunde') {
/*
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";

            echo $_POST['room'];*/

            $currentPlanID = stundenplandata::getCurrentStundenplanID();

            if (!($currentPlanID > 0)) {
                echo json_encode(array('error' => true, 'errorMsg' => 'Es wurde kein Stundenplan angegeben.'), true);
                exit;
            }
            if (!$_POST['klasse']
                || !$_POST['lehrer']
                || !$_POST['room']
                || !$_POST['stunde']
                || !$_POST['date']
                || !$_POST['fach']) {
                echo json_encode(array('error' => true, 'errorMsg' => 'Missing Data'), true);
                exit;
            }

            if (!DB::getDB()->query("INSERT INTO raumplan_stunden
				(
					stundenplanID,
					stundeKlasse,
					stundeLehrer,
					stundeFach,
					stundeRaum,
					stundeDatum,
				    stundeStunde
				) 
				values(
					'" . DB::getDB()->escapeString($currentPlanID) . "',
					'" . DB::getDB()->escapeString($_POST['klasse']) . "',
					'" . DB::getDB()->escapeString($_POST['lehrer']) . "',
					'" . DB::getDB()->escapeString($_POST['fach']) . "',
					'" . DB::getDB()->escapeString($_POST['room']) . "',
					'" . DB::getDB()->escapeString($_POST['date']) . "',
					'" . DB::getDB()->escapeString($_POST['stunde']) . "'
				)
		    ")) {
                echo json_encode(array('error' => true, 'errorMsg' => 'Fehler beim Hinzufügen!'), true);
                exit;
            }

            echo json_encode(array('insert' => true));

            exit;
        }




        //$getRoom = 'A010';

        $settingsRooms = DB::getSettings()->getValue('raumplan-rooms');

        $getRoom = $_GET['room'];

        if (!$getRoom) {
            $getRoom = json_decode($settingsRooms)[0];
        }
        if (!$getRoom) {
            new errorPage("Leider wurde kein Raum gewählt!");
        }

        //echo $getRoom;


        $currentPlanID = stundenplandata::getCurrentStundenplanID();
        if (!($currentPlanID > 0)) {
            new errorPage("Leider steht aktuell kein Stundenplan zur Verfügung!");
        }
        $stundenplan = new stundenplandata($currentPlanID);


        $showDays = array(
            'Mo' => 1,
            'Di' => 1,
            'Mi' => 1,
            'Do' => 1,
            'Fr' => 1,
            'Sa' => 0,
            'So' => 0
        );


        if ($_REQUEST['action'] == 'getWeek') {

            if (!$_GET['bis']) {
                die('missing data');
            }
            if (!$_GET['von']) {
                die('missing data');
            }
            if (!$_GET['room']) {
                die('missing data');
            }

            $week = [
                $_GET['von'],
                $_GET['von'] + (1 * 86400),
                $_GET['von'] + (2 * 86400),
                $_GET['von'] + (3 * 86400),
                $_GET['von'] + (4 * 86400)
            ];
            //print_r($week);
            //setlocale(LC_TIME, "de_DE.utf8");
            //date_default_timezone_set("Europe/Berlin");

            /*$tage = [
                "Mon" => "Mo"
            ];*/

            $plan = array();
            for ($s = 0; $s < stundenplandata::getMaxStunden(); $s++) {

                $plan[] = array();

                $i = 0;
                foreach ($week as $date) {
                    $plan[$s][] = array("day" => [date('Y-m-d', $week[$i])]);
                    $i++;
                }
            }
            //$room = 'A010';
            //echo $getRoom;

            $sql = "stundeRaum LIKE '" . DB::getDB()->escapeString($_GET['room']) . "'";

            $stundenData = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $stundenplan->getID() . "' AND $sql");
            while ($s = DB::getDB()->fetch_array($stundenData)) {
                $stunde = array(
                    "teacher" => $s['stundeLehrer'],
                    "room" => $s['stundeRaum'],
                    "grade" => $s['stundeKlasse'],
                    "subject" => $s['stundeFach']
                );

                $plan[$s['stundeStunde'] - 1][$s['stundeTag'] - 1][] = $stunde;

            }

            $a = 0;
            foreach ($week as $date) {

                $datum = date('Y-m-d', $date);

                $sql = ' stundeDatum = "'.$datum.'" AND stundeRaum LIKE "'.DB::getDB()->escapeString($_GET['room']).'"';
                $raumplanData = DB::getDB()->query("SELECT * FROM raumplan_stunden WHERE stundenplanID='" . $stundenplan->getID() . "' AND $sql");
                while ($s = DB::getDB()->fetch_array($raumplanData)) {
                    $stunde = array(
                        "teacher" => $s['stundeLehrer'],
                        "room" => $s['stundeRaum'],
                        "grade" => $s['stundeKlasse'],
                        "subject" => $s['stundeFach'],
                        "state" => 'unique'
                    );

                    $plan[$s['stundeStunde'] - 1][ $a ][] = $stunde;
                    //print_r($s);

                }
                $a++;
            }


            /*   echo '<pre>';
               print_r($plan);
               echo '</pre>';
               exit;*/


            echo json_encode($plan);


            /*
                        $result = DB::getDB()->query("SELECT a.*
                            FROM mensa_speiseplan as a
                            WHERE a.date >= '".$von."' AND a.date <= '".$bis."'" );
                        */
            /*
                        $booked_own = [];
                        $booked_db = DB::getDB()->query("SELECT a.*
                                FROM mensa_order as a
                                WHERE a.userID = " . intval(DB::getUserID()) . "");
                        while ($order = DB::getDB()->fetch_array($booked_db)) {
                            $booked_own[] = $order['speiseplanID'];
                        }
                        */
            /*
                        echo json_encode([
                            'error' => true,
                            'msg' => 'Es konnte kein Essen geladen werden!'
                        ]);
                        */
            exit;
        }




        //print_r($settingsRooms);

        $acl = $this->getAcl();
        $acl['rights']['read'] = 1;
        $acl['rights']['write'] = 1;
        $acl = json_encode($acl);


        $prevDays = DB::getSettings()->getValue("raumplan-days") ;
        if (!$prevDays) {
            $prevDays = 0;
        }
        $showDays = json_encode($showDays);

        eval("echo(\"" . DB::getTPL()->get("raumplan/index") . "\");");

    }


    public static function hasSettings()
    {
        return true;
    }

    public static function getSettingsDescription()
    {
        //return array();

        $settings = array(
            array(
                'name' => "raumplan-days",
                'typ' => "NUMMER",
                'titel' => "Wie viele Tage vorher muss gebucht werden?",
                'text' => "Default: 1"
            )
        );
        return $settings;

    }


    public static function getSiteDisplayName()
    {
        return 'Raumplan (deprecated)';
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups()
    {
        return array();

    }


    public static function hasAdmin()
    {
        return true;
    }

    public static function getAdminGroup()
    {
        //return false;
        return 'Webportal_Raumplan';
    }

    public static function getAdminMenuGroup()
    {
        return 'Raumplan';
    }

    public static function getAdminMenuGroupIcon()
    {
        return 'fa fa-door-open';
    }

    public static function getAdminMenuIcon()
    {
        return 'fa fa-door-open';
    }


    public static function displayAdministration($selfURL)
    {

        $currentPlanID = stundenplandata::getCurrentStundenplanID();
        if (!($currentPlanID > 0)) {
            new errorPage("Leider steht aktuell kein Stundenplan zur Verfügung!");
        }
        $stundenplan = new stundenplandata($currentPlanID);

        $rooms = $stundenplan->getAll('room');

        if ($_REQUEST['action'] == 'saveRooms') {

            //echo 'jo';
            //echo $_POST['room_D070'];

            $json = [];

            foreach ($rooms as $room) {
                //$ret .= '<li><input type="checkbox" name="room_'.$room.'">' . $room.'</li>';

                if ($_POST['room_' . $room]) {
                    $json[] = $room;
                }
            }


            DB::getSettings()->setValue('raumplan-rooms', json_encode($json));

            //exit;
        }


        /*echo '<pre>';
        print_r();
        echo '</pre>';*/

        $settingsRooms = json_decode(DB::getSettings()->getValue('raumplan-rooms'));

        $ret = '';

        //$rooms = $stundenplan->getAll('room');
        foreach ($rooms as $room) {
            $check = '';
            if (in_array($room, $settingsRooms)) {
                $check = 'checked="checked"';
            }
            $ret .= '<li><input type="checkbox" name="room_' . $room . '" ' . $check . '>' . $room . '</li>';
        }

        $html = '';
        eval("\$html = \"" . DB::getTPL()->get("raumplan/admin/index") . "\";");
        return $html;

    }


    public static function hasCurrentUserAccess()
    {

        $access = false;

        if (DB::getSession()->isTeacher()) {
            $access = true;
        }

        if (DB::getSession()->isAdmin()) {
            $access = true;
        }

        return $access;
    }
}


?>