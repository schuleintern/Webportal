<?php

/**
 *
 */
class extInboxRecipientParent
{


    /**
     * Constructor
     * @param $data
     */
    public function __construct()
    {

    }

    public static function getTitle($user_id = false)
    {
        if (!(int)$user_id) {
            return false;
        }

        include_once PATH_EXTENSIONS. 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByUserIDFirst((int)$user_id);

        if ( $inbox ) {
            $temp = $inbox->getCollection(true);
            if ($temp && $temp['title']) {
                return 'Eltern von '.$temp['title'];
            }
        }

        return false;
    }


    /**
     * @return Array[]
     */
    public static function getInboxs($user_id = false)
    {

        if (!(int)$user_id) {
            return false;
        }

        $user = user::getUserByID($user_id);
        if ($user && $user->isPupil() ) {
            $parents = $user->getPupilObject()->getParentsUsers();
        }


        $ret = [];

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();

        foreach($parents as $parent) {
            $inbox = $class->getByUserIDFirst( $parent->getUserID() );
            if ($inbox) {
                $inbox_collection = $inbox->getCollection(true);
                if ($inbox_collection) {

                    //$inbox_collection['title'] = 'Eltern von '.$user->getDisplayName();

                    $ret = [
                        "title" => 'Eltern von '.$user->getDisplayName(),
                        "data" => [$inbox_collection]
                    ];

                }
            }
        }

        // TODO: Multiple Parents
        return $ret;



    }


}