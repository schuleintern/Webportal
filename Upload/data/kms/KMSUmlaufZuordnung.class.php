<?php

class KMSUmlaufZuordnung {

    /**
     * @var lehrer|null
     */
    private $lehrer = null;

    /**
     * @var schueler|null
     */
    private $schueler = null;

    /**
     * @var schueler|null
     */
    private $elternSchueler = null;

    /**
     * Sonstiger Nutzer, die nicht Lehrer oder Schüler oder Eltern sind.
     * @var user
     */
    private $user = null;

    /**
     * Ist das KMS bestätigt?
     * @var bool
     */
    private $isConfirmed = false;

    /**
     * Zeitpunkt der Bestätigung
     * @var int
     */
    private $confirmTime = 0;

    /**
     * KMSUmlaufZuordnung constructor.
     * @param $data array
     */
    public function __construct($data) {

    }
}