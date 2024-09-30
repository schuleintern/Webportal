<?php

/**
 *
 */
class extInboxRecipientParentsUnterricht
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
        $unterricht = SchuelerUnterricht::getByID((int)$content);

        return 'Eltern Unterricht ' . $unterricht->getBezeichnung();
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
        //include_once PATH_EXTENSION . 'inboxs' . DS . 'parent.class.php';

        $ret = [];

        $unterricht = SchuelerUnterricht::getByID((int)$content);

        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();

        $schuelers = $unterricht->getSchueler();

        if ($schuelers) {


            foreach ($schuelers as $schueler) {

                if ($schueler->getUserID()) {

                    $parents = $schueler->getParentsUsers();

                    include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
                    $class = new extInboxModelInbox2();

                    foreach($parents as $parent) {
                        $inbox = $class->getByUserIDFirst( $parent->getUserID() );
                        if ($inbox) {
                            $inbox_collection = $inbox->getCollection(true);
                            if ($inbox_collection) {

                                //$inbox_collection['title'] = 'Eltern von '.$user->getDisplayName();

                                $ret[] = $inbox_collection;

                            }
                        }
                    }
                }
            }

        }

        return [
            "title" => 'Eltern ' . $unterricht->getBezeichnung(),
            "data" => $ret
        ];

    }


}