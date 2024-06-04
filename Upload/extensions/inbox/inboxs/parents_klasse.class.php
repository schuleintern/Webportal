<?php

/**
 *
 */
class extInboxRecipientParentsKlasse
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

        return 'Eltern Klasse '.$content;
    }


    /**
     * @return Array[]
     */
    public static function getInboxs($content = false)
    {

        if (!$content) {
            return false;
        }


        //include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        include_once PATH_EXTENSION . 'inboxs' . DS . 'parent.class.php';

        $ret = [];
        $klassen = klasse::getByName((string)$content);
        if ($klassen) {
            $schuelers = $klassen->getSchueler();
            if ($schuelers) {

                


                
                foreach ($schuelers as $schueler) {

                    $foo = extInboxRecipientParent::getInboxs($schueler->getUserID());
                    if ($foo) {
                        $ret[] = $foo[0];
                    }
                    

                    /*
                    $parents = $schueler->getParentsUsers();
                    if ($parents) {
                        foreach ($parents as $parent) {

                            $user_id = (int)$parent->getUserID();
                            if ($user_id) {
                                $inbox = extInboxModelInbox::getUserByUserID($user_id);
                                if ($inbox) {
                                    $inbox_collection = $inbox->getCollection(true);
                                    if ($inbox_collection) {
                                        $ret[] = $inbox_collection;
                                    }
                                }
                            }
                        }
                    }
                    */
                }
                
            }
        }
        return $ret;

    }


}