<?php

/**
 *
 */
class extInboxRecipientVerwaltung
{


    /**
     * Constructor
     * @param $data
     */
    public function __construct()
    {

    }

    public static function getTitle($content = false)
    {
        if (!$content || $content == '') {
            return false;
        }

        return 'Verwaltung '.ucfirst($content);
    }


    /**
     * @return Array[]
     */
    public static function getInboxs($content = false)
    {

        if (!$content) {
            return false;
        }

        $typ = (string)$content;
        $users = [];

        switch ($typ) {

            case 'schulleitung':
                $schulleitung = schulinfo::getSchulleitungLehrerObjects();
                foreach($schulleitung as $user) {
                    if ($user) {
                        $users[] = $user;
                    }
                }
                break;
            
            case 'sekretariat':
                $schulleitung = schulinfo::getVerwaltungsmitarbeiter();
                foreach($schulleitung as $user) {
                    if ($user) {
                        $users[] = $user;
                    }
                }
                break;

            case 'personalrat':
                $schulleitung = schulinfo::getPersonalratMitarbeiter();
                foreach($schulleitung as $user) {
                    if ($user) {
                        $users[] = $user;
                    }
                }
                break;

            case 'hausmeister':
                $schulleitung = schulinfo::getHausmeister();
                foreach($schulleitung as $user) {
                    if ($user) {
                        $users[] = $user;
                    }
                }
                break;
    }



        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $ret = [];
        foreach($users as $user) {
            $inbox = $class->getByUserIDFirst($user->getUserID());
            if ($inbox) {
                $inbox_collection = $inbox->getCollection(true);
                if ($inbox_collection) {
                    $ret[] = $inbox_collection;
                }
            }
        }






        return $ret;

    }


}