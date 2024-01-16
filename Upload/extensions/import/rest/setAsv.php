<?php


class setAsv extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $folderID = $request[2];
        if (!$folderID || $folderID == 'undefined') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Folder'
            ];
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if (!$this->canWrite()) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }


        $file = $_FILES['file'];
        if (!$file) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }
        $info = pathinfo($file['name']);
        if ($info['extension'] != 'zip') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Falsches Dateiformat'
            ];
        }

        $zip_Path = PATH_TMP.'asvimport_'.$folderID.'.zip';

        include_once PATH_EXTENSION . 'models' . DS .'ASV.class.php';
        $class = new extImportModelASV();

        if ( $class->uploadZip($file, $zip_Path) ) {
            return [
                'path' => $zip_Path,
                'success' => true
            ];
        }


        return [
            'error' => true,
            'msg' => "Error"
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