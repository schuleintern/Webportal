<?php


use DateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\MultiDay;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;


use DateInterval;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Alarm;
use Eluceo\iCal\Domain\ValueObject\Attachment;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
new Eluceo\iCal\Domain\ValueObject\Date;


class geticsfeed extends AbstractPage {

    private $startDate = "";
    private $endDate = "";

    public function __construct() {

    }

    public function execute() {
        $feed = ICSFeed::getByIDAndKeys($_REQUEST['a'],$_REQUEST['b'],$_REQUEST['c']);

        if($feed == null) {
            http_response_code(203);
            echo("no access");
            exit(0);
        }

        $timeNow = time();

        $timeStart = $timeNow - (100 * 24 * 60 * 60); // 100 Tage vorher
        $timeEnd = $timeNow + (365 * 24 * 60 * 60);

        $this->startDate = date("Y-m-d",$timeStart);
        $this->endDate = date("Y-m-d",$timeEnd);


        if($feed->isKlassenkalender()) {
            $this->sendKlassenkalender($feed);
            exit(0);
        }

        if($feed->isAndererKalender()) {
            $this->sendAndererKalender($feed);
            exit();
        }

        if($feed->isExternerKalender()) {
            $this->sendExternerKalender($feed);
        }

    }

    /**
     *
     * @param ICSFeed $feed
     */
    private function sendAndererKalender($feed) {
        $data = json_decode($feed->getFeedData());

        // 100 Tage vorher abfragen
        $time = DateFunctions::getUnixTimeFromMySQLDate(DateFunctions::getTodayAsSQLDate());
        $time1 = $time - (100 * 24 * 3600);
        $time2 = $time + (365 * 24 * 3600);
        $kalenderTermine = AndererKalenderTermin::getAll($data, DateFunctions::getMySQLDateFromUnixTimeStamp($time1), DateFunctions::getMySQLDateFromUnixTimeStamp($time2));
        $events = array_filter(array_map(fn($e) => $e->transform(), $kalenderTermine));

        $calendar = new Calendar($events);
        $calendar->setPublishedTTL(new DateInterval('PT1H'));
        ICSFeed::sendICSFeed($calendar);
        exit;
    }

    /**
     *
     * @param ICSFeed $feed
     */
    private function sendExternerKalender($feed) {
        $data = json_decode($feed->getFeedData());

        // 100 Tage vorher abfragen
        $time = DateFunctions::getUnixTimeFromMySQLDate(DateFunctions::getTodayAsSQLDate());
        $time1 = $time - (100 * 24 * 3600);
        $time2 = $time + (365 * 24 * 3600);
        $kalenderTermine = ExtKalenderTermin::getAll($data, DateFunctions::getMySQLDateFromUnixTimeStamp($time1), DateFunctions::getMySQLDateFromUnixTimeStamp($time2));
        $events = array_filter(array_map(fn($e) => $e->transform(), $kalenderTermine));

        $calendar = new Calendar($events);
        $calendar->setPublishedTTL(new DateInterval('PT1H')); // recommend a one-hour delay before refreshing the calendar
        ICSFeed::sendICSFeed($calendar);
        exit;
    }

    /**
     *
     * @param ICSFeed $feed
     */
    private function sendKlassenkalender($feed) {
        $data = json_decode($feed->getFeedData());

        $class = $data->klasse;
        $withEx = $data->withEx > 0;

        $showGrade = false;
        $name = "";


        if($class == "all_grades") {
            $lnwData = Leistungsnachweis::getByClass([], $this->startDate,$this->endDate);
            $termine = Klassentermin::getByClass([], $this->startDate,$this->endDate);
            $showGrade = true;
            $name = "Klassentermine aller Klassen";
        }
        else if($class == "allMyGrades") {

            $currentStundenplan = stundenplandata::getCurrentStundenplan();

            $lehrer = user::getUserByID($feed->getUserID());

            if($lehrer->isTeacher()) {
                $kuerzel = $lehrer->getTeacherObject()->getKuerzel();
            }
            else {
                http_response_code(500);
                echo("internal error. ICS Feed invalid.");
                exit(0);
            }

            $grades = klasse::getByUnterrichtForTeacher($lehrer->getTeacherObject());

            $myGrades = [];

            for($i = 0; $i < sizeof($grades); $i++) $myGrades[] = $grades[$i]->getKlassenName();

            // $myGrades = $currentStundenplan->getAllGradesForTeacher($kuerzel);

            $lnwData = Leistungsnachweis::getByClass($myGrades,$this->startDate,$this->endDate);
            $termine = Klassentermin::getByClass($myGrades,$this->startDate,$this->endDate);

            $showGrade = true;

            $name = "Klassentermine aller Klasse von " . $kuerzel;
        }
        else if($class == "allMyTermine") {

            $currentStundenplan = stundenplandata::getCurrentStundenplan();

            $lehrer = user::getUserByID($feed->getUserID());

            if($lehrer->isTeacher()) {
                $kuerzel = $lehrer->getTeacherObject()->getKuerzel();
            }
            else {
                http_response_code(500);
                echo("internal error. ICS Feed invalid.");
                exit(0);
            }


            $lnwData = Leistungsnachweis::getBayTeacher($kuerzel,$this->startDate,$this->endDate);
            $termine = Klassentermin::getBayTeacher($kuerzel,$this->startDate,$this->endDate);


            $showGrade = true;

            $name = "Klassentermine, die " . $kuerzel . " eingetragen hat.";
        }
        elseif(substr($class,0,3) == "all") {
            $showGrade = true;

            $class = substr($class,3);

            $classes = $currentStundenplan->getAllMyPossibleGrades($class);

            $lnwData = Leistungsnachweis::getByClass($classes, $this->startDate,$this->endDate);
            $termine = Klassentermin::getByClass($classes, $this->startDate,$this->endDate);

            $name = "Klassentermine der Klassen " . implode(",", $classes);

        }
        else {

            $showGrade = true;

            $lnwData = Leistungsnachweis::getByClass([$class],$this->startDate,$this->endDate);
            $termine = Klassentermin::getByClass([$class],$this->startDate,$this->endDate);

            $name = "Klassentermine der Klasse " . $class;

        }


        if($withEx) $name .= " (Mit unangekündigten Leisungsnachweisen.)";

        $vCalendar = new NamedCalendar();
        $vCalendar->setName($name);
        $vCalendar->setPublishedTTL(new DateInterval('PT1H')); // recommend a one-hour delay before refreshing the calendar


        for($i = 0; $i < sizeof($lnwData); $i++) {
            if($lnwData[$i]->showForNotTeacher() || $withEx) {
                $periods = $lnwData[$i]->getStunden();
                $date = new DateTimeImmutable($lnwData[$i]->getDatumStart());
                $events = stundenplandata::getTimesForWeekdayAndPeriods($date->format("w"), $periods, true);
                if (empty($events)) {
                    $events = array(array(
                        "start" => new DateInterval("P0D"),
                        "end" => new DateInterval("P0D"),
                        "all_day" => true
                    ));
                }

                foreach ($events as $eventno=>$event) {
                    $vCalendar->addEvent(ICSFeed::getICSFeedObject(
                        "LNW" . $lnwData[$i]->getID() . "-$eventno",
                        (($showGrade) ? ($lnwData[$i]->getKlasse() . ": ") : "") . $lnwData[$i]->getArtLangtext() . " in " . $lnwData[$i]->getFach() . " bei " . $lnwData[$i]->getLehrer(),
                        $date->add($event["start"]),
                        $date->add($event["end"]),
                        $lnwData[$i]->getBetrifft(),
                        "",
                        array_key_exists("all_day", $event)
                    ));
                }
            }
        }


        for($i = 0; $i < sizeof($termine); $i++) {
            $start = new DateTimeImmutable($termine[$i]->getDatumStart());
            $end = new DateTimeImmutable($termine[$i]->getDatumEnde());
            if ($start != $end) {
                $events = null;
            } else {
                $events = stundenplandata::getTimesForWeekdayAndPeriods($start->format("w"), $termine[$i]->getStunden(), true);
                if (!empty($events)) {
                    // getTimesForWeekdayAndPeriods only returns the time, so we need to add that to the dates before we continue
                    foreach ($events as &$event) {
                        $event["start"] = $start->add($event["start"]);
                        $event["end"] = $start->add($event["end"]);
                    }
                }
            }

            if (empty($events)) {
                $events = array(array(
                    "start" => $start,
                    "end" => $end,
                    "all_day" => true
                ));
            }

            foreach ($events as $eventno=>$event) {
                $vCalendar->addEvent(ICSFeed::getICSFeedObject(
                    "KT" . $termine[$i]->getID() . "-$eventno",
                    $termine[$i]->getTitle(),
                    $event["start"],
                    $event["end"],
                    $termine[$i]->getOrt(),
                    implode(", ", $termine[$i]->getKlassen()) . "\r\n" . $termine[$i]->getBetrifft(),
                    array_key_exists("all_day", $event)
                ));
            }
        }



        ICSFeed::sendICSFeed($vCalendar);
        exit(0);


    }

    public static function hasSettings() {
        return true;
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
        return [];
    }


    public static function getSiteDisplayName() {
        return 'ICS Feeds';
    }

    public static function siteIsAlwaysActive() {
        return true;
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups() {
        return [];
    }

    public static function getAdminGroup() {
        return 'Webportal_ICS_FEED_Admin';
    }

    public static function hasAdmin() {
        return false;
    }

    public static function getAdminMenuGroup() {
        return 'Kalender';
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fa-calendar';
    }


    public static function getAdminMenuIcon() {
        return 'fa fa-child';
    }


    public static function getActionSchuljahreswechsel() {
        return 'ICS Feeds der Klassenkalender, die von Eltern oder Schülern sind löschen';
    }

    public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {

    }
}


?>
