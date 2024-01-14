<?php

class getFile extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        /*
        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        */

        $id = (int)$request[2];
        if (!$id) {
            return [false];
        }
        $file = (string)$request[3];
        if (!$file) {
            return false;
        }

        $target_Path = PATH_ROOT . 'data' . DS . 'ext_podcast' . DS . $id . DS . $file;
        if (!file_exists($target_Path)) {
            echo "Upload existiert nicht!";
            return false;
        }

        header('Content-Description: Dateidownload');
        header('Content-Type: ' . mime_content_type($target_Path));
        header('Content-Disposition: inline; filename="' . $target_Path . '"');
        //header('Expires: 0');
        //header('Cache-Control: must-revalidate');
        //header('Pragma: public');
        header('Content-Length: ' . filesize($target_Path));
        //header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60))); // 1 hour
        //header('Cache-Control: no-cache');
        header("Content-Transfer-Encoding: chunked");

        ob_clean();
        flush();

        $fp = fopen($target_Path, 'rb');        // READ / BINARY

        fpassthru($fp);


        return true;
        exit;
    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
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

    public function checkAuth()
    {
        return true;
    }
}
