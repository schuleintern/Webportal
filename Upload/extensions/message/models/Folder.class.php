<?php
/**
 *
 */


class extMessageModelFolder
{

    /**
     * @var data []
     */
    private $data = [];


    private $user = false;
    private $typ = false;

    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false, $user = false, $folderTyp = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
        if ($user) {
            $this->user = $user;
        }
        if ($folderTyp) {
            $this->typ = $folderTyp;
        }
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData() {
        return $this->data;
    }

    public function getUser() {
        return $this->user;
    }
    public function getTyp() {
        return $this->typ;
    }

    /**
     * Getter
     */
    /*
    public function getID() {
        return $this->data['tutorenID'];
    }
    */

    public function getCollection() {
        return $this->data;
    }


    public function getMessages() {

        include_once PATH_EXTENSION . 'models' . DS . 'Message.class.php';

        if (!$this->getUser()) {
            return false;
        }
        if (!$this->getTyp()) {
            return false;
        }

        $fData = DB::getDB()->query("SELECT messageSubject, messageSender, messagePriority,
            messageIsRead, messageTime, messageAttachments, messageID
            FROM messages_messages WHERE messageUserID=".$this->getUser()->getUserID()." AND messageFolder = '".$this->getTyp()."'
            ORDER BY messageTime DESC LIMIT 0,250");
        $folders = [];
        while($f = DB::getDB()->fetch_array($fData, true)) {
            $obj = new extMessageModelMessage($f);
            $folders[] = $obj->getCollection();
        }
        return $folders;
    }


    public static function getFolder($user = false, $folder = false, $folderID = false)
    {

        if (!$folder) {
            return false;
        }
        $folder = strtoupper($folder);
        if( !in_array($folder,['POSTEINGANG','GESENDETE','PAPIERKORB','ARCHIV','ANDERER'])) {
            return false;
        }
        if (!$user) {
            return false;
        }
        $user_id = $user->getUserID();
        if (!$user_id) {
            return false;
        }

        if($folder == 'ANDERER') {
            $fData = DB::getDB()->query_first("SELECT * FROM messages_folders WHERE folderID='" . intval($folderID) . "' AND folderUserID='" . $user_id . "'");
            if($fData['folderID'] > 0) {
                return new extMessageModelFolder([
                    "isStandardFolder" => false,
                    "data" => $fData
                ], $user, $folder);
            }
        }

        if($folder == 'POSTEINGANG') {
            return new extMessageModelFolder([
                "isStandardFolder" => true,
                "data" => ['folderName' => 'Posteingang']
            ], $user, $folder);
        }

        if($folder == 'GESENDETE') {
            return new extMessageModelFolder([
                "isStandardFolder" => true,
                "data" => ['folderName' => 'Gesendete']
            ], $user, $folder);
        }

        if($folder == 'PAPIERKORB') {
            return new extMessageModelFolder([
                "isStandardFolder" => true,
                "data" => ['folderName' => 'Papierkorb']
            ], $user, $folder);
        }

        if($folder == 'ARCHIV') {
            return new extMessageModelFolder([
                "isStandardFolder" => true,
                "data" => ['folderName' => 'Archiv']
            ], $user, $folder);
        }

        return false;

    }


    public static function getMyFolders($user = false) {

        if (!$user) {
            return false;
        }
        $user_id = $user->getUserID();
        if (!$user_id) {
            return false;
        }
        $fData = DB::getDB()->query("SELECT * FROM messages_folders WHERE folderUserID='" . $user->getUserID() . "'", true);
        $folders = [];
        while($f = DB::getDB()->fetch_array($fData, true)) {
            $obj = new extMessageModelFolder($f);
            $folders[] = $obj->getCollection();
        }
        return $folders;
    }



}