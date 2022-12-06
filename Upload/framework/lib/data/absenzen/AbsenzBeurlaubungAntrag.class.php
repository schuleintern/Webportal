<?php

class AbsenzBeurlaubungAntrag {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getID() {
        return $this->data['antragID'];
    }

    public function getUserID() {
        return $this->data['antragUserID'];
    }
    
    public function getSchuelerAsvID() {
        return $this->data['antragSchuelerAsvID'];
    }
    
    /**
     * 
     * @return NULL|schueler
     */
    public function getSchueler() {
        return schueler::getByAsvID($this->getSchuelerAsvID());
    }
    
    public function getStartDatumAsSQLDate() {
        return $this->data['antragDatumStart'];
    }
    
    public function getEndDatumAsSQLDate() {
        return $this->data['antragDatumEnde'];
    }
    
    public function getBegruendung() {
        return $this->data['antragBegruendung'];
    }
    
    public function getAntragTime() {
        return $this->data['antragTime'];
    }
    
    public function isKLDecisionMade() {
        return $this->data['antragKLGenehmigt'] >= 0;
    }
    
    public function isKLGenehmigt() {
        return $this->data['antragKLGenehmigt'] > 0;
    }    
    
    public function isSLDecisionMade() {
        return $this->data['antragSLgenehmigt'] >= 0;
    }
    
    public function isSLGenehmigt() {
        return $this->data['antragSLgenehmigt'] > 0;
    }   
    
    public function getKLKommentar() {
        return $this->data['antragKLKommentar'];
    }
    
    public function getSLKommentar() {
        return $this->data['antragSLKommentar'];
    }
    
    public function getSLDate() {
        return $this->data['antragSLgenehmigtDate'];
    }    
    
    public function getKLDate() {
        return $this->data['antragKLGenehmigtDate'];
    }    
    
    public function isVerarbeitet() {
        return $this->data['antragIsVerarbeitet'] > 0;
    }
     
    public function getStunden() {
        return explode(",",$this->data['antragStunden']);
    }
    
    public function setVerarbeitet() {


        if ( $this->data['extension'] ) {

            DB::getDB()->query("UPDATE ext_beurlaubung_antrag SET status = 21 WHERE id='" . $this->getID() . "'");

        } else {
            $this->setField('antragIsVerarbeitet', 1);
        }

    }
    
    public function setKLGenehmigung($genehmigt, $kommentar) {
        $this->setField('antragKLGenehmigt', $genehmigt ? 1 : 0);
        $this->setField('antragKLGenehmigtDate', date("Y-m-d"));
        $this->setField('antragKLKommentar', $kommentar);
    }
    
    public function setSLGenehmigung($genehmigt, $kommentar) {
        $this->setField('antragSLGenehmigt', $genehmigt ? 1 : 0);
        $this->setField('antragSLGenehmigtDate', date("Y-m-d"));
        $this->setField('antragSLKommentar', $kommentar);
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM absenzen_beurlaubung_antrag WHERE antragID='" . $this->getID() . "'");
    }
    
    private function setField($field, $value) {
        DB::getDB()->query("UPDATE absenzen_beurlaubung_antrag SET $field = '" . DB::getDB()->escapeString($value) . "' WHERE antragID='" . $this->getID() . "'");
    }
       
    
    public static function getAllForUser($userID) {
        $sql = DB::getDB()->query("SELECT * FROM absenzen_beurlaubung_antrag WHERE antragUserID='" . DB::getDB()->escapeString($userID) . "'");

        $allD = [];

        while($b = DB::getDB()->fetch_array($sql)) {
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }
        
        return $allD;
    }
    
    public static function getAllForSchulleitungOrKlassenleitung() {
        $sql = DB::getDB()->query("SELECT * FROM absenzen_beurlaubung_antrag WHERE antragDatumStart >= CURDATE() ORDER BY antragTime DESC");
        
        $allD = [];
        
        while($b = DB::getDB()->fetch_array($sql)) {
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }
        
        return $allD;
    }

    public static function getAllInPastForSchulleitungOrKlassenleitung() {
        $sql = DB::getDB()->query("SELECT * FROM absenzen_beurlaubung_antrag WHERE antragDatumStart < CURDATE() ORDER BY antragDatumStart DESC");

        $allD = [];

        while($b = DB::getDB()->fetch_array($sql)) {
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }

        return $allD;
    }
    
    public static function getAllForKlassenleitung() {
        $sql = DB::getDB()->query("SELECT * FROM absenzen_beurlaubung_antrag WHERE antragDatumStart >= CURDATE() ORDER BY antragTime DESC");
        
        $allD = [];
        
        while($b = DB::getDB()->fetch_array($sql)) {
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }
        
        return $allD;
    }
    
    public static function getGenehmigtNichtVerarbeitete() {
        $where = ' 	antragIsVerarbeitet=0 ';
        
        if(DB::getSettings()->getBoolean('beurlaubung-klassenleitung-freigabe')) {
            $where .= ' AND antragKLGenehmigt=1 ';
        }
        
        if(DB::getSettings()->getBoolean('beurlaubung-schulleitung-freigabe')) {
            $where .= ' AND antragSLGenehmigt=1 ';
        }
        
        $sql = DB::getDB()->query("SELECT * FROM absenzen_beurlaubung_antrag WHERE $where");
        
        
        $allD = [];
        
        while($b = DB::getDB()->fetch_array($sql)) {
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }

        include_once (PATH_LIB.'data/extensions/ExtensionsPages.php');
        if ( ExtensionsPages::isActive('ext.zwiebelgasse.beurlaubung')) {
            include_once PATH_EXTENSIONS . 'beurlaubung' . DS . 'models' . DS . 'Antrag.class.php';
            $ext_beurlaubungen = extBeurlaubungModelAntrag::getGenehmigtNichtVerarbeitete();
            $allD = array_merge($allD, $ext_beurlaubungen);
        }

        return $allD;
    }
    
    /**
     * 
     * @param unknown $dateStart
     * @param unknown $dateEnde
     * @param unknown $schuelerAsvID
     * @param unknown $begruendung
     * @param unknown $stunden
     * @return int new ID
     */
    public static function create($dateStart, $dateEnde, $schuelerAsvID, $begruendung, $stunden) {
        DB::getDB()->query("INSERT INTO absenzen_beurlaubung_antrag (
            antragUserID,
            antragSchuelerAsvID,
            antragDatumStart,
            antragDatumEnde,
            antragBegruendung,
            antragStunden,
            antragTime) values(
                '" . DB::getSession()->getUserID() . "',
                '" . DB::getDB()->escapeString($schuelerAsvID) . "',
                '" . DB::getDB()->escapeString($dateStart) . "',
                '" . DB::getDB()->escapeString($dateEnde) . "',
                '" . DB::getDB()->escapeString($begruendung) . "',
                '" . implode(",",$stunden) . "',
                UNIX_TIMESTAMP()
            )
        ");
        
        return DB::getDB()->insert_id();
    }
    
    /**
     * 
     * @param unknown $id
     * @return AbsenzBeurlaubungAntrag|NULL
     */
    public static function getByID($id) {
        $data = DB::getDB()->query_first("SELECT * FROM absenzen_beurlaubung_antrag WHERE antragID='" . intval($id) . "'");
        
        if($data['antragID'] > 0) return new AbsenzBeurlaubungAntrag($data);
        else return null;
    }

}
