<?php

/**
 *
 */
class extInboxRecipientGroup
{


    /**
     * Constructor
     * @param $data
     */
    public function __construct()
    {

    }


    public static function getTitle($group_id = false)
    {

        if (!(int)$group_id) {
            return false;
        }

        if (EXTENSION::isActive('ext.zwiebelgasse.users')) {
            include_once PATH_EXTENSIONS . 'users' . DS . 'models' . DS . 'Groups.class.php';
            $class = new extUsersModelGroups();
            $tmp_data = $class->getByID($group_id);
            if (!$tmp_data) {
                return false;
            }
            $group = $tmp_data->getCollection();
            return $group['title'];
        }

        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByID((int)$inbox_id);

        if ($inbox->getData('title')) {
            return $inbox->getData('title');
        }
        */

        return false;
    }


    /**
     * @return Array[]
     */
    public static function getInboxs($group_id = false)
    {

        if (!(int)$group_id) {
            return false;
        }


        if (EXTENSION::isActive('ext.zwiebelgasse.users')) {
            include_once PATH_EXTENSIONS .'users'.DS. 'models' . DS . 'Groups.class.php';
            $class = new extUsersModelGroups();
            $tmp_data = $class->getByID($group_id);
            if (!$tmp_data) {
                return false;
            }
            $group = $tmp_data->getCollection();
            if ($group && $group['users'] && $group['title']) {

                include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
                $class = new extInboxModelInbox2();

                $ret = [];
                $users = json_decode($group['users']);
                foreach ($users as $user_id) {

                    $inbox = $class->getByUserIDFirst($user_id);
                    if ($inbox) {
                        $inbox_collection = $inbox->getCollection(true);
                        if ($inbox_collection) {
                            $ret[] = $inbox_collection;
                        }
                    }

                }
                return [
                    "title" => $group['title'],
                    "data" => $ret
                ];

            }
        }
        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $inbox = $class->getByID($inbox_id);

        if ($inbox) {
            $inbox_collection = $inbox->getCollection(true);
            if ($inbox_collection) {
                return [$inbox_collection];
            }
        }
        */

        return false;


    }


}