<?php

/**
 * Stellt einen Statistikzeitpunkt dar.
 * (Wie viele Nutzer waren zur Zeit X angemeldet.)
 */
class UserLoginStat {

    /**
     * Unix Timestamp des Statistik Elements
     * @var int $time
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
        $this->time = $data['loginTimestamp'];

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