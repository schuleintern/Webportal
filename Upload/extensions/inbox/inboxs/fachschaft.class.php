<?php

/**
 *
 */
class extInboxRecipientFachschaft
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

        $fach = fach::getByID((int)$content);

        return 'Fachschaft '.$fach->getKurzform();
    }

    /**
     * @return Array[]
     */
    public static function getInboxs($content = false)
    {

        if (!$content) {
            return false;
        }

        $fach = fach::getByID((int)$content);


        include_once $teachers = $fach->getFachLehrer();PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
            
        $ret = [];

        foreach($teachers as $teacher) {
            

            $inbox = $class->getByUserIDFirst($teacher->getUser()->getUserID());
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