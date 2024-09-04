<?php

/**
 *
 */
class extInboxRecipientPupilsAll
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

        return 'Alle Schüler*innen';
    }

    /**
     * @return Array[]
     */
    public static function getInboxs($content = false)
    {


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $ret = [];

        $items = schueler::getAll();

        if ($items) {
            foreach ($items as $item) {
                $user = $item->getUser();
                if ($user) {
                    $user_id = (int)$user->getUserID();
                    if ($user_id) {
                        $inbox = $class->getByUserIDFirst($user_id);
                        if ($inbox) {
                            $inbox_collection = $inbox->getCollection(true);
                            if ($inbox_collection) {
                                $ret[] = $inbox_collection;
                            }
                        }
                    }
                }

            }


        }

        return [
            "title" => 'Alle Schüler*innen',
            "data" => $ret
        ];
    }


}