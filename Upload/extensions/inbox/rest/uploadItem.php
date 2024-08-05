<?php

use AbstractRest;
use DB;
use extInboxModelMessageBody2;

class uploadItem extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }

        $id = $request[2];
        if (!$id || $id == 'undefined') {
            $id = '__tmp';
            //$id = $user->getUserID();
            /*
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing ID'
            ];
            */
        }


        $acl = $this->getAcl();
        if (!$this->canWrite()) {
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
        /*
        if ($info['extension'] != 'pdf') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Falsches Dateiformat'
            ];
        }
        */


        $allowedString = DB::getSettings()->getValue("extInbox-files-allowedExt");
        if (!$allowedString) {
            $allowedString = '';
        }
        $allowed = explode(',', $allowedString);

        if (!in_array($info['extension'], $allowed)) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Falsches Dateiformat'
            ];
        }

        $maxSize = DB::getSettings()->getValue("extInbox-files-maxSize");
        if ($file['size'] > ($maxSize * 1024)) { // byte to kb *1024
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Datei zu groß'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'MessageBody2.class.php';
        $class = new extInboxModelMessageBody2();

        $filename = 'file_' . time() . '_' . rand(100, 999);

        if ($ret = $class->uploadFile($file, $filename, 'ext_inbox/files_' . $id)) {

            $ret['success'] = true;
            return $ret;

        }


        return [
            'error' => true,
            'msg' => 'Nicht Erfolgreich!'
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