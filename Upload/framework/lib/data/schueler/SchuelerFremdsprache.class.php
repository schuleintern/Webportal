<?php

class SchuelerFremdsprache {
    private $data = [];

    /**
     * SchuelerFremdsprache constructor.
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Fach
     * @return string
     */
    public function getSpracheFach() {
        return $this->data['spracheFach'];
    }

    /**
     * Jahrfangsstufe aber der die Sprache unterrichtet wird für diesen Schüler
     * @return string
     */
    public function getSpracheAbJahrgangsstufe() {
        return $this->data['spracheAbJahrgangsstufe'] != "" ? $this->data['spracheAbJahrgangsstufe'] : "n/a";
    }

    /**
     * Feststellungsprüfung?
     * @return bool
     */
    public function mitFeststellungspruefung() {
        return $this->data['spracheFeststellungspruefung'] > 0;
    }

    /**
     * @param schueler $schueler
     * @return SchuelerFremdsprache[]
     */
    public static function getForSchueler($schueler) {
        $fremdsprachenSQL = DB::getDB()->query("SELECT * FROM schueler_fremdsprache WHERE schuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY spracheSortierung ASC");

        $fremdsprachen = [];

        while($fs = DB::getDB()->fetch_array($fremdsprachenSQL)) $fremdsprachen[] = new SchuelerFremdsprache($fs);

        return $fremdsprachen;
    }

}