<?php


abstract class AbstractAusweis {

    private static $knownSchoolClasses = [
        '0366' => 'ISGYAusweis',
        '0740' => 'ISGYAusweis'
    ];

    private $data = [];

    public function __construct() {
        // Blank
    }

    public function loadFromData($data) {
        $this->data = $data;
    }

    public function createNew() {
        DB::getDB()->query("INSERT INTO ausweise (ausweisID) values(NULL)");
        $newID = DB::getDB()->insert_id();

        $this->loadFromData(DB::getDB()->query_first("SELECT * FROM ausweise WHERE ausweisID='" . $newID . "'"));
    }

    public function getID() {
        return $this->data['ausweisID'];
    }

    public function getBarcode() {
        return $this->data['ausweisBarcode'];
    }

    public function setBarcode($barcode) {
        $this->updateAttr('ausweisBarcode', $barcode);
    }

    /**
     *
     * @return boolean
     */
    public function isSchuelerausweis() {
        return $this->data['ausweisArt'] == 'SCHUELER';
    }

    /**
     *
     * @return boolean
     */
    public function isLehrerausweis() {
        return $this->data['ausweisArt'] == 'LEHRER';
    }

    /**
     *
     * @return boolean
     */
    public function isDienstausweis() {
        return $this->data['ausweisArt'] == 'MITARBEITER';
    }

    /**
     *
     * @return boolean
     */
    public function isGastausweis() {
        return $this->data['ausweisArt'] == 'GAST';
    }

    public function setSchuelerausweis() {
        $this->updateAttr('ausweisArt', 'SCHUELER');
    }

    public function setLehrerausweis() {
        $this->updateAttr('ausweisArt', 'LEHRER');
    }

    public function setMitarbeiterausweis() {
        $this->updateAttr('ausweisArt', 'MITARBEITER');
    }

    public function setGastausweus() {
        $this->updateAttr('ausweisArt', 'GAST');
    }

    public function getName() {
        return $this->data['ausweisName'];
    }

    public function getPLZ() {
        return $this->data['ausweisPLZ'];
    }

    public function getOrt() {
        return $this->data['ausweisOrt'];
    }

    public function setName($name) {
        $this->updateAttr('ausweisName', $name);
    }

    public function setPLZ($plz) {
        $this->updateAttr('ausweisPLZ', $plz);
    }

    public function setOrt($ort) {
        $this->updateAttr('ausweisOrt', $ort);
    }

    public function getEssenKundennummer() {
        return $this->data['ausweisEssenKundennummer'];
    }

    public function setEssenKundennummer($nummer) {
        $this->updateAttr('ausweisEssenKundennummer', $nummer);
    }

    public function getPreis() {
        return $this->data['ausweisPreis'];
    }

    public function setPreis($preisInCent) {
        $this->updateAttr('ausweisPreis', $preisInCent);
    }

    public function getBild() {
        return FileUpload::getByID($this->data['ausweisFoto']);
    }
    
    public function setGeburtsdatum($datum) {
        $this->updateAttr('ausweisGeburtsdatum', $datum);
    }
    
    public function setAblauf($datum) {
        $this->updateAttr('ausweisGueltigBis', $datum);
    }
    
    public function getGeburtsdatum() {
        return $this->data['ausweisGeburtsdatum'];
    }
    
    public function getAblaufdatum() {
        return $this->data['ausweisGueltigBis'];
    }
    
    /**
     *
     * @param FileUpload $upload
     */
    public function setBild($upload) {
        $this->updateAttr('ausweisFoto', $upload->getID());
    }

    /**
     *
     */
    public function isBezahlt() {
        return $this->data['ausweisBezahlt'];
    }

    public function setBezahlt($status) {
        $this->updateAttr('ausweisBezahlt', $status);
    }

    public function isBeantragt() {
        return
            $this->data['ausweisStatus'] == 'BEANTRAGT' ||
            $this->data['ausweisStatus'] == 'GENEHMIGT' ||
            $this->data['ausweisStatus'] == 'ERSTELLT' ||
            $this->data['ausweisStatus'] == 'ABGEHOLT';
    }

    public function isGenehmigt() {
        return
            $this->data['ausweisStatus'] == 'GENEHMIGT' ||
            $this->data['ausweisStatus'] == 'ERSTELLT' ||
            $this->data['ausweisStatus'] == 'ABGEHOLT';
    }

    public function isErstellt() {
        return
            $this->data['ausweisStatus'] == 'ERSTELLT' ||
            $this->data['ausweisStatus'] == 'ABGEHOLT';
    }

    public function isAbgeholt() {
        return
            $this->data['ausweisStatus'] == 'ABGEHOLT';

    }
    
    public function isNotGenehmigt() {
        return $this->data['ausweisStatus'] == 'NICHTGENEHMIGT';
        
    }
    
    public function getKommentar() {
        return $this->data['ausweisKommentar'];
    }
    
    public function setKommentar($text) {
        $this->updateAttr('ausweisKommentar', $text);
    }

    /**
     *
     * @param String $status (BEANTRAGT, GENEHMIGT, ERSTELLT, ABGEHOLT)
     */
    public function setStatus($status) {
        $this->updateAttr('ausweisStatus', $status);
    }


    /**
     *
     * @return user
     */
    public function getErsteller() {
        return user::getUserByID($this->data['ausweisErsteller']);
    }



    /**
     *
     * @param user $user
     */
    public function setErsteller($user) {
        $this->updateAttr('ausweisErsteller', $user->getUserID());
    }
    
    public function getType() {
        return $this->data['ausweisArt'];
    }


    /**
     *
     * @param String $name
     * @param String $value
     */
    private function updateAttr($name, $value) {
        DB::getDB()->query("UPDATE ausweise SET $name='" . DB::getDB()->escapeString($value) . "' WHERE ausweisID='" . $this->getID() . "'");
    }
    
    public function delete() {
        if($this->getID() > 0) {
            DB::getDB()->query("DELETE FROM ausweise WHERE ausweisID='" . $this->getID() . "'");
        }
    }

    /**
     * @return TCPDF TCPDF Dokument des Ausweises
     */
    public abstract function getAusweisPDFFront();

    /**
     * @return TCPDF TCPDF Dokument des Ausweises
     */
    public abstract function getAusweisPDFBack();
    
    /**
     * 
     * @param String $type
     * @return String SQL Datum mit dem Gültigkeitsende
     */
    public abstract function getGueltigkeitForNewAusweis($type);


    /**
     * @param $userID
     * @param string $type
     * @return AbstractAusweis[]
     */
    public static function getMyAusweise($userID, $type = '') {
        
        if($type != "") $addSQL = " AND ausweisArt='" . $type . "'";
        else $addSQL = "";
        
        $data = DB::getDB()->query("SELECT * FROM ausweise WHERE ausweisErsteller='" . DB::getDB()->escapeString($userID) . "'" . $addSQL);

        

        $ausweise = [];

        while($d = DB::getDB()->fetch_array($data)) {
            $newObject = self::getMyAusweisObject();

            if($newObject == null) return [];

            $newObject->loadFromData($d);
            
            $ausweise[] = $newObject;

        }

        return $ausweise;
    }
    
    
    public static function getAusweiseToApprove($type="") {
        if($type != "") $addSQL = " AND ausweisArt='" . $type . "'";
        else $addSQL = "";
        
        $data = DB::getDB()->query("SELECT * FROM ausweise WHERE ausweisStatus='BEANTRAGT'" . $addSQL);
        
        
        
        $ausweise = [];
        
        while($d = DB::getDB()->fetch_array($data)) {
            $newObject = self::getMyAusweisObject();
            
            if($newObject == null) return [];
            
            $newObject->loadFromData($d);
            
            $ausweise[] = $newObject;
            
        }
        
        return $ausweise;
    }
    
    public static function getAusweiseToPrint($type='') {
        if($type != "") $addSQL = " AND ausweisArt='" . $type . "'";
        else $addSQL = "";
        
        $data = DB::getDB()->query("SELECT * FROM ausweise WHERE ausweisStatus='GENEHMIGT'" . $addSQL);
        
        
        
        $ausweise = [];
        
        while($d = DB::getDB()->fetch_array($data)) {
            $newObject = self::getMyAusweisObject();
            
            if($newObject == null) return [];
            
            $newObject->loadFromData($d);
            
            $ausweise[] = $newObject;
            
        }
        
        return $ausweise;
    }


    /**
     *
     * @return AbstractAusweis|NULL
     */
    public static function getMyAusweisObject() {
        $schulnummer = DB::getGlobalSettings()->schulnummer;

        $className = self::$knownSchoolClasses[$schulnummer];
        
        if($className != "") {
            include_once("../framework/lib/ausweis/" . $className . ".class.php");
            return new $className();
        }

        else return null;       // NULL, wenn für Schule keine Ausweis registriert ist.
    }

}
