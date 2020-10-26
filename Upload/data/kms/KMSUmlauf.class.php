<?php

class KMSUmlauf {

    /**
     * Data
     * @var array
     */
    private $data = [];

    /**
     * Zu zeichnende KMSse
     * @var KMS[]
     */
    private $kmsse = [];


    /**
     * Subdaten geladen. (KMSse und Benutzerdaten)
     * @var bool
     */
    private $subDataLoaded = false;

    /**
     * KMSUmlauf constructor.
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * @return KMS[]
     */
    public function getKMSse() {
        return $this->kmsse;
    }


}