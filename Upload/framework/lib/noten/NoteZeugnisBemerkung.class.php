<?php

/**
 * Bermerkung im Zeugnis
 * @author Christian Spitschka
 *
 */
class NoteZeugnisBemerkung {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getZeugnis() {
        return NoteZeugnis::getByID($this->data['bemerkungZeugnisID']);
    }

    public function getSchueler() {
        return schueler::getByAsvID($this->data['bemerkungSchuelerAsvID']);
    }

    public function getText1() {
        return $this->data['bemerkungText1'];
    }

    public function getText2() {
        return $this->data['bemerkungText2'];
    }

    public function klassenzielErreicht() {
        return $this->data['klassenzielErreicht'] == 1;
    }

    public function vorrueckenAufProbe() {
        return $this->data['klassenzielErreicht'] == 2;
    }
    
    public static function getDefaultText1($schueler, $zeugnis) {
        return "";
    }

    /**
     *
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function getDefaultText2($schueler, $zeugnis) {

        $text = "";

        $wahlunterricht = NoteWahlfachNote::getForSchueler($schueler, $zeugnis);

        for($i = 0; $i < sizeof($wahlunterricht); $i++) {
            if($wahlunterricht[$i]->getNote() < 5 && $wahlunterricht[$i]->getNote() > 0) {
                $text .= $wahlunterricht[$i]->getZeugnisText($schueler) . "\r\n";
            }
        }

        $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler);
        if($na != null && $na->hasNotenschutz()) {
            if($na->getArtKurz() == 'lrs') {
                $text .= 'Die Rechtschreibleistungen wurden nicht bewertet. In den Fremdsprachen wurden die schriftlichen und mündlichen Leistungsnachweise im Verhältnis 1:1 gewichtet.' . "\r\n";
            }

            if($na->getArtKurz() == 'rs') {
                $text .= 'Auf die Bewertung der Rechtschreibleistung wurde in allen Fächern verzichtet. In den Fremdsprachen wurden die mündlichen Leistungen stärker gewichtet.' . "\r\n";
            }

            if($na->getArtKurz() == 'ls') {
                $text .= 'Auf die Bewertung des Vorlesens wurde verzichtet.' . "\r\n";
            }
        }

        return $text;
    }

    /**
     *
     * @param String $text
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function setText1($text, $schueler, $zeugnis) {
        DB::getDB()->query("INSERT INTO noten_zeugnis_bemerkung (bemerkungSchuelerAsvID, bemerkungZeugnisID, bemerkungText1) values('" . $schueler->getAsvID() . "','" . $zeugnis->getID() . "','" . DB::getDB()->escapeString($text) . "')

            ON DUPLICATE KEY UPDATE bemerkungText1='" . DB::getDB()->escapeString($text) . "'

        ");
    }

    /**
    *
    * @param String $text
    * @param schueler $schueler
    * @param NoteZeugnis $zeugnis
    */
    public static function setText2($text, $schueler, $zeugnis) {
        DB::getDB()->query("INSERT INTO noten_zeugnis_bemerkung (bemerkungSchuelerAsvID, bemerkungZeugnisID, bemerkungText2) values('" . $schueler->getAsvID() . "','" . $zeugnis->getID() . "','" . DB::getDB()->escapeString($text) . "')

            ON DUPLICATE KEY UPDATE bemerkungText2='" . DB::getDB()->escapeString($text) . "'

        ");
    }
    
    /**
     *
     * @param boolean $klassenzielErreicht
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function setKlassenzielErreicht($klassenzielErreicht, $schueler, $zeugnis) {
        DB::getDB()->query("INSERT INTO noten_zeugnis_bemerkung (bemerkungSchuelerAsvID, bemerkungZeugnisID, klassenzielErreicht) values('" . $schueler->getAsvID() . "','" . $zeugnis->getID() . "','" . DB::getDB()->escapeString($klassenzielErreicht ? 1 : 0) . "')
            
            ON DUPLICATE KEY UPDATE klassenzielErreicht='" . DB::getDB()->escapeString($klassenzielErreicht ? 1 : 0) . "'
            
        ");
    }

    /**
     *
     * @param boolean $vorrueckenAufProbe
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function setVorrueckenAufProbe($vorrueckenAufProbe, $schueler, $zeugnis) {
        DB::getDB()->query("INSERT INTO noten_zeugnis_bemerkung (bemerkungSchuelerAsvID, bemerkungZeugnisID, klassenzielErreicht) values('" . $schueler->getAsvID() . "','" . $zeugnis->getID() . "','" . DB::getDB()->escapeString($klassenzielErreicht ? 1 : 0) . "')
            
            ON DUPLICATE KEY UPDATE klassenzielErreicht='" . DB::getDB()->escapeString($vorrueckenAufProbe ? 2 : 0) . "'
            
        ");
    }

    /**
     * 
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     * @return NoteZeugnisBemerkung|NULL
     */
    public static function getForSchueler($schueler, $zeugnis) {
        $data = DB::getDB()->query_first("SELECT * FROM noten_zeugnis_bemerkung WHERE bemerkungSchuelerAsvID='" . $schueler->getAsvID() . "' AND bemerkungZeugnisID='" . $zeugnis->getID() . "'");
        
        if($data['bemerkungSchuelerAsvID'] != "") {
            return new NoteZeugnisBemerkung($data);
        }
        else return null;
    }

}
