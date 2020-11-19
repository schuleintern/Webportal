<?php

/**
 * Stellt einen Statistikzeitpunkt dar.
 * (Wie viele Nutzer waren zur Zeit X angemeldet.)
 */
class UserLoginStat {

    /**
     * Unix Timestamp des Statistik Elements
     * @var String $time
     */
    private $time = 0;

    /**
     * Angemeldete Lehrer zu diesem Zeitpunkt
     * @var int
     */
    private $loggedInTeachers = 0;

    /**
     * Angemeldete Schüler zu diesem Zeitpunkt
     * @var int
     */
    private $loggedInStudents = 0;

    /**
     * Angemeldete Eltern zu diesem Zeitpunkt
     * @var int
     */
    private $loggedInParents = 0;

    private function __construct($data) {
        $this->time = $data['statTimestamp'];

        $this->loggedInTeachers = $data['statLoggedInTeachers'];
        $this->loggedInParents = $data['statLoggedInParents'];
        $this->loggedInStudents = $data['statLoggedInStudents'];
    }

    /**
     * @return int
     */
    public function getLoggedInTeachers() {
        return $this->loggedInTeachers;
    }

    /**
     * @return int
     */
    public function getLoggedInStudents() {
        return $this->loggedInStudents;
    }

    /**
     * @return int
     */
    public function getLoggedInParents() {
        return $this->loggedInParents;
    }

    public static function getTodayStat() {
        return self::prepareResult(self::getByDateRange(DateFunctions::getTodayAsSQLDate(), DateFunctions::addOneDayToMySqlDate(DateFunctions::getTodayAsSQLDate())));
    }

    public static function getCurrentMonth() {

        $currentMonth = date("m");
        $currentYear = date("Y");

        $nextYear = $currentYear;

        if($currentMonth == 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        else $nextMonth = $currentMonth + 1;


        return self::prepareResult(self::getByDateRange(date("Y-m") . "-01",DateFunctions::substractOneDayToMySqlDate($nextYear . "-" . $nextMonth . "-01")),true);
    }

    public static function getYear($year) {
        return self::prepareResult(self::getByDateRange($year . "-01-01", $year . "-12-31"),false, true);
    }


    /**
     * @param $begin
     * @param $end
     * @return UserLoginStat[]
     */
    private static function getByDateRange($begin, $end) {
        $labels = [];

        list($yearBegin, $monthBegin, $dayBegin) = explode("-",$begin);
        list($yearEnd, $monthEnd, $dayEnd)  = explode("-", $end);

        $sql = DB::getDB()->query("SELECT * FROM loginstat WHERE
                              statTimestamp >= '" . DateFunctions::getMySQLTimeStamp($dayBegin, $monthBegin, $yearBegin,0,0,0) . "'
                              AND
                              statTimestamp < '" . DateFunctions::getMySQLTimeStamp($dayEnd, $monthEnd, $yearEnd, 23,59) . "' ORDER BY statTimestamp ASC");



        $result = [];

        while($d = DB::getDB()->fetch_array($sql)) {
            $result[] = new UserLoginStat($d);
        }


        return $result;

    }

    /**
     * @param UserLoginStat[] $stats
     * @param false $groupByHour Bei True wird ein Mittelwert der Stunde gebildet
     * @return array[]
     */
    private static function prepareResult($stats, $groupByHour = false, $groupByDay = false) {
        $result = [
            'labels' => [],
            'teacherdata' => [],
            'studentdata' => [],
            'parentsdata' => []
        ];

        if($groupByHour) {
            $currentHour = -1;
            /**
             * @var DateTime
             */
            $currentTimeObject = null;

            $statsDay = [
                's' => 0,
                't' => 0,
                'p' => 0,
            ];

            for($i = 0; $i < sizeof($stats); $i++) {
                $date = DateFunctions::getDateTimeObjectFromMySQLTimestamp($stats[$i]->time);

                $hour = $date->format("H") * 1;

                if($hour != $currentHour) {
                    if($currentHour != -1) {
                        // Letzte Stat speichern

                        $result['labels'][] = $currentTimeObject->format("d.m.Y H:00");

                        $result['teacherdata'][] = $statsDay['t'];
                        $result['studentdata'][] = $statsDay['s'];
                        $result['parentsdata'][] = $statsDay['p'];


                        $statsDay = [
                            's' => 0,
                            't' => 0,
                            'p' => 0,
                        ];
                    }
                }

                if($stats[$i]->loggedInStudents > $statsDay['s']) $statsDay['s'] = $stats[$i]->loggedInStudents;

                if($stats[$i]->loggedInParents > $statsDay['p']) $statsDay['p'] = $stats[$i]->loggedInParents;

                if($stats[$i]->loggedInTeachers > $statsDay['t']) $statsDay['t'] = $stats[$i]->loggedInTeachers;

                $currentTimeObject = $date;
                $currentHour = $hour;
            }
        }
        else if($groupByDay) {
            $currentDay = -1;
            /**
             * @var DateTime
             */
            $currentTimeObject = null;

            $statsDay = [
                's' => 0,
                't' => 0,
                'p' => 0,
            ];

            for($i = 0; $i < sizeof($stats); $i++) {
                $date = DateFunctions::getDateTimeObjectFromMySQLTimestamp($stats[$i]->time);

                $day = $date->format("d") * 1;

                if($day != $currentDay) {
                    if($currentDay != -1) {
                        $result['labels'][] = $currentTimeObject->format("d.m.Y");

                        $result['teacherdata'][] = $statsDay['t'];
                        $result['studentdata'][] = $statsDay['s'];
                        $result['parentsdata'][] = $statsDay['p'];


                        $statsDay = [
                            's' => 0,
                            't' => 0,
                            'p' => 0,
                        ];
                    }
                }

                if($stats[$i]->loggedInStudents > $statsDay['s']) $statsDay['s'] = $stats[$i]->loggedInStudents;

                if($stats[$i]->loggedInParents > $statsDay['p']) $statsDay['p'] = $stats[$i]->loggedInParents;

                if($stats[$i]->loggedInTeachers > $statsDay['t']) $statsDay['t'] = $stats[$i]->loggedInTeachers;

                $currentTimeObject = $date;
                $currentDay = $day;
            }
        }
        else {
            for($i = 0; $i < sizeof($stats); $i++) {
                $result['labels'][] = DateFunctions::getTimeFromDateTimeObject(DateFunctions::getDateTimeObjectFromMySQLTimestamp($stats[$i]->time));
                $result['teacherdata'][] = $stats[$i]->loggedInTeachers;
                $result['studentdata'][] = $stats[$i]->loggedInStudents;
                $result['parentsdata'][] = $stats[$i]->loggedInParents;
            }
        }

        return $result;
    }


    /**
     * Erstellt ein Statistik Element
     */
    public static function createStatItem() {
        DB::getDB()->query("INSERT INTO loginstat
            (
             statLoggedInTeachers,
             statLoggedInStudents,
             statLoggedInParents
            ) values(
                (SELECT COUNT(*) FROM sessions WHERE sessionUserID IN (SELECT lehrerUserID FROM lehrer) AND sessionLastActivity  > " . (time() - 300) . "),
                (SELECT COUNT(*) FROM sessions WHERE sessionUserID IN (SELECT schuelerUserID FROM schueler) AND sessionLastActivity  > " . (time() - 300) . "),
                (SELECT COUNT(*) FROM sessions WHERE sessionUserID IN (SELECT elternUserID FROM eltern_email) AND sessionLastActivity  > " . (time() - 300) . ")  
            )

        ");
    }


}