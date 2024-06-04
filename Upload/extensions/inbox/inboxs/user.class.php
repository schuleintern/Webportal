<?php

/**
 *
 */
class extInboxRecipientUser
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

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByUserIDFirst((int)$user_id);

        if ( $inbox ) {
            $temp = $inbox->getCollection(true);
            if ($temp && $temp['title']) {
                return $temp['title'];
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

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByUserIDFirst($user_id);

        if ($inbox) {
            $inbox_collection = $inbox->getCollection(true);
            if ($inbox_collection) {
                return [$inbox_collection];
            }
        }

        return false;



    }


}