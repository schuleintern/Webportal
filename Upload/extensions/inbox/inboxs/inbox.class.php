<?php

/**
 *
 */
class extInboxRecipientInbox
{


    /**
     * Constructor
     * @param $data
     */
    public function __construct()
    {

    }


    public static function getTitle($inbox_id = false)
    {
        if (!(int)$inbox_id) {
            return false;
        }


        //include_once PATH_EXTENSIONS.'inbox'.DS. 'models' . DS . 'Inbox2.class.php';
        //$class = new extInboxModelInbox2();
        $inbox = PAGE::getFactory()->getInboxByID((int)$inbox_id);

        if ($inbox && $inbox->getData('type') == 'user') {
            if ($inbox->getData('parent_id')) {
                $user = user::getUserByID($inbox->getData('parent_id'));
                if ($user) {
                    return $user->getDisplayName();
                }

            }
        } else {
            if ($inbox && $inbox->getData('title')) {
                return $inbox->getData('title');
            }
        }


        return false;
    }


    /**
     * @return Array[]
     */
    public static function getInboxs($inbox_id = false)
    {

        if (!(int)$inbox_id) {
            return false;
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByID($inbox_id);

        if ($inbox) {
            $inbox_collection = $inbox->getCollection(true, false, true);
            if ($inbox_collection) {
                return [
                    "title" => $inbox_collection['title'],
                    "data" => [$inbox_collection]
                ];
            }
        }


        return false;


    }


}