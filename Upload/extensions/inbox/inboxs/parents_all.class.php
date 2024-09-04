<?php

/**
 *
 */
class extInboxRecipientParentsAll
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

        return 'Alle Eltern';
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
            foreach ($items as $pupil) {

                $parents = $pupil->getParentsUsers();
                foreach ($parents as $parent) {

                        $user_id = (int)$parent->getUserID();
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
            "title" => 'Alle Eltern',
            "data" => $ret
        ];
    }


}