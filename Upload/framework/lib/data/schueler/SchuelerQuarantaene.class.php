<?php

class SchuelerQuarantaene {
    private $data = [];


    /**
     * Cache geladen?
     * @var bool
     */
    private static $cacheLoaded = false;


    /**
     * Cache
     * @var SchuelerQuarantaene[]
     */
    private static $cache = [];

    /**
     * SchuelerQuarantaene constructor.
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
    }


    public function getID() {
        return $this->data['quarantaeneID'];
    }

    public function delete() {
        DB::getDB()->query("DELETE FROM schueler_quarantaene WHERE quarantaeneID='" . $this->getID() . "'");
    }


    /**
     * Ist die Quarantaene heute gültig
     * @return bool
     */
    public function isToday() {
        return DateFunctions::isSQLDateAtOrAfterAnother(DateFunctions::getTodayAsSQLDate(), $this->getStartDate())
                &&
                DateFunctions::isSQLDateAtOrBeforeAnother(DateFunctions::getTodayAsSQLDate(), $this->getEndDate());

    }

    /**
     * Schüler zur Quarantaene
     * @return schueler|null
     */
    public function getSchueler() {
        return schueler::getByAsvID($this->data['quarantaeneSchuelerAsvID']);
    }

    /**
     * ASV ID des Schülers
     * @return string
     */
    public function getSchuelerAsvID() {
        return $this->data['quarantaeneSchuelerAsvID'];
    }

    /**
     *
     * @return string
     */
    public function getKommentar() {
        return $this->data['quarantaeneKommentar'];
    }

    /**
     * Ersteller
     * @return user|null
     */
    public function getCreateUser() {
        return user::getUserByID($this->data['quarantaeneCreatedByUserID']);
    }

    /**
     * Start als SQL Date
     * @return string
     */
    public function getStartDate() {
        return $this->data['quarantaeneStart'];
    }

    /**
     * Ende als SQL Date
     * @return string
     */
    public function getEndDate() {
        return $this->data['quarantaeneEnde'];
    }

    /**
     * Start Datum als natürliches Datum
     * @return string
     */
    public function getStartAsNaturalDate() {
        return DateFunctions::getNaturalDateFromMySQLDate($this->getStartDate());
    }

    /**
     * End Datum als natürliches Datum
     * @return string
     */
    public function getEndAsNaturalDate() {
        return DateFunctions::getNaturalDateFromMySQLDate($this->getEndDate());
    }

    public function isIsolation() {
        return $this->data['quarantaeneArt'] == 'I';
    }

    public function isQuarantaene() {
        return $this->data['quarantaeneArt'] == 'K1';
    }

    public function isSonstige() {
        return $this->data['quarantaeneArt'] == 'S';
    }

    /**
     * Neues Enddatum setzen
     * @param string $newDate
     */
    public function setEndDate($newDate) {
        DB::getDB()->query("UPDATE schueler_quarantaene SET quarantaeneEnde='" . DB::getDB()->escapeString($newDate) . "' WHERE quarantaeneID='" . $this->getID() . "'");
        $this->data['quarantaeneEnde'] = $newDate;
    }

    /**
     * Neues Enddatum setzen
     * @param string $newDate
     */
    public function setStartDate($newDate) {
        DB::getDB()->query("UPDATE schueler_quarantaene SET quarantaeneStart='" . DB::getDB()->escapeString($newDate) . "' WHERE quarantaeneID='" . $this->getID() . "'");
        $this->data['quarantaeneStart'] = $newDate;
    }

    /**
     * Kommentar hinzufügen
     * @param string $kommentar
     */
    public function addKommentar($kommentar) {
        $newKommentar = "";

        if($this->getKommentar() != "") {
            $newKommentar = $this->getKommentar() . "\r\n-------------\r\n";
        }

        $newKommentar .= DB::getSession()->getUser()->getDisplayNameWithFunction() . ": " . DateFunctions::getTodayAsNaturalDate() . "\r\n" . $kommentar;

        DB::getDB()->query("UPDATE schueler_quarantaene SET quarantaeneKommentar='" . DB::getDB()->escapeString($newKommentar) . "' WHERE quarantaeneID='" . $this->getID() . "'");

        $this->data['quarantaeneKommentar'] = $newKommentar;
    }

    /**
     * Statustext
     * @return string
     */
    public function getStatusText() {
        if($this->isIsolation()) return "In Isolation (positiv gestestet) bis " . $this->getEndAsNaturalDate();
        if($this->isQuarantaene()) return "In Quarantäne (Kontaktperson) bis " . $this->getEndAsNaturalDate();
        if($this->isSonstige()) return "Sonstige Quarantäne bis " . $this->getEndAsNaturalDate();
        return "n/a";
    }

    public function getCalloutDiv() {
        return "<div class=\"callout callout-" . (($this->isIsolation()) ? ("danger") : ("warning")) . "\"><i class=\"fa fa-head-side-mask\"></i> " . $this->getStatusText() . "</div>";
    }

    public function getStatusLabel() {
        return "<div class=\"label label-" . (($this->isIsolation()) ? ("danger") : ("warning")) . "\"><i class=\"fa fa-head-side-mask\"></i> " . $this->getStatusText() . "</div>";
    }

    public function getStatusAsDisabledButton($block=true) {
        return "<button type=\"button\" class=\"btn " . (($block) ? " btn-block " : "") . "disabled btn-" . (($this->isIsolation()) ? ("danger") : ("warning")) . "\"><i class=\"fa fa-head-side-mask\"></i> " . $this->getStatusText() . "</button>";

    }

    public function getArtDisplayName() {
        if($this->isIsolation()) return "Isolation";
        if($this->isQuarantaene()) return "Quarantäne";
        if($this->isSonstige()) return "Sonstige";
        return "n/a";
    }

    /**
     * @return FileUpload|null
     */
    public function getAttachment() {
        if($this->data['quarantaeneFileUpload'] > 0) return FileUpload::getByID($this->data['quarantaeneFileUpload']);
        return null;
    }


    private static function initCache() {
        if(!self::$cacheLoaded) {

            $all = DB::getDB()->query("SELECT * FROM schueler_quarantaene JOIN schueler ON schueler_quarantaene.quarantaeneSchuelerAsvID=schueler.schuelerAsvID ORDER BY length(schuelerKlasse), schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");

            while($q = DB::getDB()->fetch_array($all)) self::$cache[] = new SchuelerQuarantaene($q);
            self::$cacheLoaded = true;
        }
    }

    /**
     * @param schueler $schueler
     * @return SchuelerQuarantaene|null
     */
    public static function getCurrentForSchueler($schueler) {
        self::initCache();

        $todaySQLDate = DateFunctions::getTodayAsSQLDate();

        for($i = 0; $i < sizeof(self::$cache); $i++) {
            if(self::$cache[$i]->getSchuelerAsvID() == $schueler->getAsvID() && self::$cache[$i]->isToday()) return self::$cache[$i];
        }

        return null;
    }

    /**
     * Alle für einen Schüler
     * @param schueler $schueler
     * @return SchuelerQuarantaene[]
     */
    public static function getAllForSchueler($schueler) {

        self::initCache();

        $result = [];

        for($i = 0; $i < sizeof(self::$cache); $i++) {
            if(self::$cache[$i]->getSchuelerAsvID() == $schueler->getAsvID()) $result[] = self::$cache[$i];
        }

        return $result;
    }

    /**
     * @param schueler $schueler
     * @param string $sqlStart
     * @param string $sqlEnd
     * @param string $type
     * @param string $comment
     * @param FileUpload|null $attachmentUpload
     */
    public static function addForSchueler($schueler, $sqlStart, $sqlEnd, $type, $comment, $attachmentUpload) {
        DB::getDB()->query("INSERT INTO schueler_quarantaene (
                                  quarantaeneSchuelerAsvID,
                                  quarantaeneStart,
                                  quarantaeneEnde,
                                  quarantaeneArt,
                                  quarantaeneKommentar,
                                  quarantaeneFileUpload,
                                  quarantaeneCreatedByUserID) 
                                  
                                  values(
                                        '" . $schueler->getAsvID() . "',
                                        '" . DB::getDB()->escapeString($sqlStart) . "',
                                        '" . DB::getDB()->escapeString($sqlEnd) . "',
                                        '" . DB::getDB()->escapeString($type) . "',
                                        '" . DB::getDB()->escapeString($comment) . "',
                                        '" . DB::getDB()->escapeString($attachmentUpload != null ? $attachmentUpload->getID() : 0) . "',
                                        '" . DB::getDB()->escapeString(DB::getSession()->getUser()->getUserID()) . "'
                                  )
                                ");
    }

    /**
     * Einer der Schüler in Isolation oder Quarantäne?
     * @param klasse $klasse
     */
    public static function hasOneInClass($klasse) {
        $schueler = $klasse->getSchueler(false);
        for($i = 0; $i < sizeof($schueler); $i++) {
            if(self::getCurrentForSchueler($schueler[$i]) != null) return true;
        }

        return false;
    }

    /**
     * Funktion aktiviert?
     * @return bool
     */
    public static function isActive() {
        return DB::getSettings()->getBoolean('schuelerinfo-quarantaene');
    }


    /**
     * Alle Quarantäne Fälle der Schule
     * @return SchuelerQuarantaene[]
     */
    public static function getAll() {
        self::initCache();

        return self::$cache;
    }



}