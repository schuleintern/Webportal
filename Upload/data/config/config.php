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
        'user' => 'root',
        'password' => '',
        'database' => 'schuleintern.dev'
    );

    /**
     * Mailaccount für das Versenden von Nachrichten.
     */
    public $smtpSettings = array(
        'sender' => 'info@schule-intern-demo.de',
        'username' => '',
        'password' => '',
        'host' => 'localhost'
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
    public $stundenplanSoftware = "SPM++";

    /**
     * Version der Stundenplansoftware (Interface)
     * @var string
     */
    public $stundenplanSoftwareVersion = "4";

    /**
     * Owncloud Integration aktiv?
     * @var boolean ja/nein
     */
    public $enableNextCloud = true;

    /**
     * Chota für einzelne OwnCloud Account
     * @var boolean ja/nein
     */
    public $nextCloudQuota = "500MB";

    /**
     * Zugangsdaten des OwnCloud Administrators
     * @var array
     */
    public $nextCloudAuth = [
        'user' => 'nextcloudadmin',
        'password' => '112233'
    ];

    /**
     * OwnCloud Host ohne abschließenden / (Slash)
     * @var string
     */
    public $nextCloudHost = "https://cloud.schule-intern.local";


    /**
     * Hat eine Notenverwaltung?
     * @var boolean
     */
    public $hasNotenverwaltung = true;

    /**
     * Ist die Datei imagesSchool/Briefkopf.png ein Ganzseitiges Bild?
     * @var boolean
     */
    public $printLetterWithFullBackgroundImage = false;

    /**
     * Margin auf der rechten Seite
     * @var integer
     */
    public $printSettingsMarginRight = 0;


    /**
     * Benutzername für den Zugriff auf die REST API
     * @deprecated
     * @var string
     */
    public $restApiUsername = "112233";

    /**
     * Passwort für den Zugriff auf die REST API
     * @deprecated
     * @var string
     */
    public $restApiPassword = '112233';

    /**
     * Optional:
     * KAS Account (All-Inkl.com) für die Automatisierung der Erstellung von Mailadressen
     * @var array
     */
    public $kasAccount = [
        'Username' => 'w012345678',
        'Password' => 'abcdef'
    ];

    /**
     * KAS Domain für die Mailadressenerstellungerstellung
     * @var string
     */
    public $kasMailDomain = "schule-intern-demo.de";

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
}