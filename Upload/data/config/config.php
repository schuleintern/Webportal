<?php

class GlobalSettings {
    /**
     * Ist die Anwendung im Debug modus?
     * @var bool
     */
    public $debugMode = true;

    /**
     * Schulnummer als String (mit führender Null)
     * @var string
     */
    public $schulnummer = "0740";

    /**
     * Datenbankeinstellungen für diese Installation
     * @var array
     */
    public $dbSettigns = array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'schuleintern.dev'
    );

    /**
     * URL zur index.php für diese Installation (SSL!)
     * @var string
     */
    public $urlToIndexPHP = "http://schuleintern.local/index.php";

    /**
     * Schlüssel zum Ausführen des Cron Jobs.
     * @var string
     */
    public $cronkey = "112233";

    /**
     * Schlüssel für den Zugriff auf die API
     * @var string
     */
    public $apiKey = "112233";

    /**
     * Seitenname zur Darstellung auf LoginSeite
     * @var string
     */
    public $siteNameHTMLDisplay = "<b>SCHULE</b>intern";

    /**
     * Seitenname zur Darstellung auf LoginSeite
     * @var string
     */
    public $siteNameHTMLDisplayShort = "<b>SCHULE</b>";

    /**
     * Einfacher Seitenname
     * @var string
     */
    public $siteNamePlain = "SchuleIntern";
    /**
     * Schulname
     * @var string
     */
    public $schoolName = "Realschule Testhausen";

    /**
     * Modus der Schülerbenutzer:
     * SYNC:	Synchronisierung
     * ASV:		Benutzer kommen aus der ASV (werden automatisch erstellt.)
     * @var string
     */
    public $schuelerUserMode = "SYNC";
    /**
     * Modus der Lehrerbenutzer:
     * SYNC:	Synchronisierung
     * ASV:		Benutzer kommen aus der ASV (werden automatisch erstellt.)
     * @var string
     */
    public $lehrerUserMode = "SYNC";
    /**
     * Modus der Eltern:
     * ASV_MAIL:		E-Mailadressen kommen aus der ASV
     * ASV_CODE:		Eltern bekommen Elternbrief mit Code zur Selbstregistrierung^
     * KLASSENELTERN
     * @var string
     */
    public $elternUserMode = "ASV_MAIL";

    /**
     * Verwendete Stundenplan Software
     * UNTIS, SPM++, SPM++V2
     * @var string
     */
    public $stundenplanSoftware = "WILLI";

    /**
     * Hat eine Notenverwaltung?
     * @var boolean
     */
    public $hasNotenverwaltung = true;

        /**
     * Daten der Azure App, die für den Zugriff auf die GraphAPI nötig ist.
     * @var array
     */
    public $office365AppCredentials = [
        'client_id' => '',
        'scope' => 'https://graph.microsoft.com/.default',
        'client_secret' => '',
        'grant_type' => 'client_credentials'
    ];

    /**
     * URL zur Ferienliste für den Import in den Kalender.
     * @var string
     */
    public $ferienURL = "https://ferien.schule-intern.de/Ferien.txt";

    /**
     * Domain des Update Servers
     * @var string
     */
        public $updateServer = "http://update.schule-intern.de";

}