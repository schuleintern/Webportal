<?php

class importAsv extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $folderID = (string)$input['randFile'];
        if (!$folderID || $folderID == 'undefined') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Folder'
            ];
        }

        $password = (string)$_POST['password'];
        if (!$password || $password == 'undefined') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Date'
            ];
        }


        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $zip_Path = PATH_TMP.'asvimport_'.$folderID.'.zip';
        $xml_Path = PATH_TMP.'asvimport_'.$folderID;

        include_once PATH_EXTENSION . 'models' . DS .'ASV.class.php';
        $class = new extImportModelASV();

        if ( $class->unZip($zip_Path, $password, $xml_Path) ) {

            $file_Path = $xml_Path.DS.'export.xml';
            if (file_exists($xml_Path)) {

                $simpleXML = simplexml_load_file($file_Path, null, LIBXML_NOCDATA);

                $ret = $class->handleXML($simpleXML);

                DB::getSettings()->setValue("last-asv-import", DateFunctions::getTodayAsNaturalDate());

                FILE::removeFolder(PATH_TMP);
                mkdir(PATH_TMP);

                return $ret;

            }
        }
        return [
            'error' => true,
            'msg' => 'Fehler beim Ausführen.'
        ];


    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'POST';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }

    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth()
    {
        return false;
    }

}

?>