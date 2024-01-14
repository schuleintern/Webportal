<?php

/**
 *
 */
class extInboxRecipientTeachersKlasse
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

        return 'Lehrer*innen Klasse '.$content;
    }

    /**
     * @return Array[]
     */
    public static function getInboxs($content = false)
    {

        if (!$content) {
            return false;
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();

        $ret = [];
        $klassen = klasse::getByName((string)$content);
        if ($klassen) {

            $teachers = $klassen->getKlassenlehrer();

            if ($teachers) {
                foreach($teachers as $teacher) {

                    $user_id = (int)$teacher->getUser()->getUserID();
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
        return $ret;

    }


}