<?php


class LeistungsnachweisRespizienz extends Leistungsnachweis {

    private static $langnamen = [
        'SCHULAUFGABE' => 'Schulaufgabe',
        'STEGREIFAUFGABE' => 'Stegreifaufgabe',
        'KURZARBEIT' => 'Kurzarbeit',
        'PLNW' => 'Praktischer Leistungsnachweis',
        'MODUSTEST' => 'Modustest',
        'NACHHOLSCHULAUFGABE' => 'Nachholschulaufgabe'
    ];

    /**
     * File Data Array
     *
     * $data =  [
     *      [
     *          'uploadID' => 123 / null (Bei keiner Resp.)
     *          'lehrerKuerzel' => '',
     *          'kommentar' => '',
     *          'pupils' => [], // AsvIDs
     *          'notenschnitt' => 1,23
     *      ]
     * ];
     *
     *
     *
     */


    private $respdata;
    private $portaldata;

    private $lehrerFiles = [];
    private $fslFiles = [];
    private $slFiles = [];

    protected function __construct($data, $lnwData) {
        $this->respdata = $data;
        parent::__construct($lnwData);

        $this->lehrerFiles = json_decode($data['respizienzFile']);
        $this->fslFiles = json_decode($data['respizienzFSLFile']);
        $this->slFiles = json_decode($data['respizientSLFile']);
    }

    public function getID() {
        return $this->respdata['respizienzID'];
    }

    /**
     *
     * @return boolean
     */
    public function isFSLRespizieiert() {
        return sizeof($this->fslFiles) > 0;
    }

    /**
     *
     * @return boolean
     */
    public function isSLRespizieiert() {
        return sizeof($this->slFiles) > 0;
    }

    /**
     *
     * @return boolean
     */
    public function isUploaded() {
        return sizeof($this->lehrerFiles) > 0;
    }



    public function getFiles() {
        return $this->lehrerFiles;
    }

    public function getFSLFiles() {
        return $this->fslFiles;
    }

    public function getSLFiles() {
        return $this->slFiles;
    }

    public function addLehrerFile($uploadID, $kommentar, $pupils, $schnitt) {
        $this->addFile($uploadID, $kommentar, $pupils, $schnitt, $this->lehrerFiles, 'respizienzFile');
    }


    public function addFSLFile($uploadID, $kommentar) {
        $this->addFile($uploadID, $kommentar, [], '', $this->fslFiles, 'respizienzFSLFile');
    }

    public function addSLFile($uploadID, $kommentar) {
        $this->addFile($uploadID, $kommentar, [], '', $this->slFiles, 'respizientSLFile');
    }
    
    public function deleteLehrerFile($uploadID) {
        $this->deleteFile($uploadID, $this->lehrerFiles, 'respizienzFile');
    }
    
    public function deleteFSLFile($uploadID) {
        $this->deleteFile($uploadID, $this->fslFiles, 'respizienzFSLFile');
    }
    
    public function deleteSLFile($uploadID) {
        $this->deleteFile($uploadID, $this->slFiles, 'respizientSLFile');
    }


    private function addFile($uploadID, $kommentar, $pupils, $schnitt, $currentFiles, $fieldName) {
                
        $kuerzel = "n/a";

        if(DB::getSession()->isTeacher()) $kuerzel = DB::getSession()->getTeacherObject()->getKuerzel();

        $currentFiles[] = [
            'uploadID' => $uploadID,
            'lehrerKuerzel' => $kuerzel,
            'kommentar' => $kommentar,
            'pupils' => $pupils, // AsvIDs
            'notenschnitt' => $schnitt
        ];

        $this->setVal($fieldName, json_encode($currentFiles));
    }

    private function deleteFile($uploadID, $currentFiles, $fieldName) {

        $newFieldValue = [];
        
        // if($uploadID == 'null') $uploadID = null;

        for($i = 0; $i < sizeof($currentFiles); $i++) {
            if($currentFiles[$i]->uploadID != $uploadID) {
                $newFieldValue[] = $currentFiles[$i];
            }
            
            if($currentFiles[$i]->uploadID == null && $uploadID == null) {
                $newFieldValue[] = $currentFiles[$i];
            }
        }
        
        $this->setVal($fieldName, json_encode($newFieldValue));
    }

    public function getLangname() {
        return $this->getArtLangtext();
    }

    public function isAnalog() {
        return $this->respdata['respizienzIsAnalog'] > 0;
    }

    public function setAnalog($status) {
        $this->respdata['respizienzIsAnalog'] = $status ? 1 : 0;
        $this->setVal('respizienzIsAnalog', $status);
    }


    public function getDatumAsNaturalDate() {
        return DateFunctions::getNaturalDateFromMySQLDate($this->getDatumStart());
    }

    public function getSchuelerMitNoten() {
        $alleNoten = [];

        for($i = 0; $i < sizeof($this->portaldata->noten); $i++) {
            $alleNoten[] = [
                'schueler' => schueler::getByAsvID($this->portaldata->noten[$i]->schuelerAsvID),
                'note' => $this->portaldata->noten[$i]->note
            ];
        }

        return $alleNoten;
    }
    
    

    private function setVal($field, $val) {
        DB::getDB()->query("UPDATE respizienz SET $field = '" . DB::getDB()->escapeString($val) . "' WHERE respizienzID='" . $this->getID() . "'");
    }

    /**
     *
     * @param lehrer $teacher
     * @return LeistungsnachweisRespizienz[]
     */
    public static function getbyTeacher($teacher) {

        $leistungsnachweise = Leistungsnachweis::getBayTeacher($teacher->getKuerzel());


        $alle = [];

        for($i = 0; $i < sizeof($leistungsnachweise); $i++) {
            $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            if($data['respizienzID'] > 0) {
                ; // Nix
            }
            else {
                DB::getDB()->query("INSERT INTO respizienz (respizienzID) values('" . $leistungsnachweise[$i]->getID() . "')");
                $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            }
            $alle[] = new LeistungsnachweisRespizienz($data, $leistungsnachweise[$i]->getDataArray());
        }

        return $alle;
    }

    /**
     *
     * @param lehrer $teacher
     */
    public static function getByFachbetreuer($teacher) {
        
        $faecherMitFSL = $teacher->getFachschaftsleitungFaecher();
        
        
        
        $leistungsnachweise = Leistungsnachweis::getByFaecher($faecherMitFSL);
        
        
        $alle = [];
        
        for($i = 0; $i < sizeof($leistungsnachweise); $i++) {
            $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            if($data['respizienzID'] > 0) {
                ; // Nix
            }
            else {
                DB::getDB()->query("INSERT INTO respizienz (respizienzID) values('" . $leistungsnachweise[$i]->getID() . "')");
                $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            }
            $alle[] = new LeistungsnachweisRespizienz($data, $leistungsnachweise[$i]->getDataArray());
        }
        
        return $alle;
    }

    /**
     *
     * @param lehrer $teacher
     */
    public static function getBySchulleitung($teacher) {
        
        $leistungsnachweise = Leistungsnachweis::getAll();
        
        
        $alle = [];
        
        for($i = 0; $i < sizeof($leistungsnachweise); $i++) {
            $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            if($data['respizienzID'] > 0) {
                ; // Nix
            }
            else {
                DB::getDB()->query("INSERT INTO respizienz (respizienzID) values('" . $leistungsnachweise[$i]->getID() . "')");
                $data = DB::getDB()->query_first("SELECT * FROM respizienz WHERE respizienzID='" . $leistungsnachweise[$i]->getID() . "'");
            }
            $alle[] = new LeistungsnachweisRespizienz($data, $leistungsnachweise[$i]->getDataArray());
        }
        
        return $alle;
    }


}
