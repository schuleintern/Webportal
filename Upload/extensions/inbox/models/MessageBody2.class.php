<?php

/**
 *
 */
class extInboxModelMessageBody2 extends ExtensionModel
{

    static $table = 'ext_inbox_message_body';

    static $fields = [
        'id',
        'sender',
        'receivers',
        'receivers_cc',
        'createdTime',
        'priority',
        'files',
        'subject',
        'text',
        'noAnswer',
        'isPrivat'
    ];

    
    static $defaults = [

    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }



    public function getCollection($full = false, $withText = false)
    {

        $collection = parent::getCollection();


        return $collection;
    }

    public function getSenderCollection()
    {
        if ($this->getData('sender')) {

            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
            $class = new extInboxModelInbox2();
            $inbox = $class->getByID($this->getData('sender'));
            if ($inbox) {
                return $inbox->getCollection(true, false, true);
            }
        }
        return false;
    }



    public function getReceiversCC()
    {

        if ($this->getData('receivers_cc')) {
            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
            $class = new extInboxModelInbox2();
            $ret = [];
            $arr = json_decode($this->getData('receivers_cc'), true);
            foreach ($arr as $item) {
                if ( count($item['inboxs']) > 0) {
                    $className = explode('::', $item['typ']);
                    $className = 'extInboxRecipient'.ucfirst($className[0]).ucfirst($className[1]);
                    $typ = str_replace('::','_', $item['typ']);
                    if (file_exists(PATH_EXTENSIONS.'inbox'.DS . 'inboxs' . DS . $typ.'.class.php')) {
                        include_once PATH_EXTENSIONS.'inbox'.DS . 'inboxs' . DS . $typ.'.class.php';
                        if (method_exists($className, 'getTitle')) {
                            $tmp_data = $className::getTitle($item['content']);
                            $inboxCol = false;
                            $inbox = $class->getByID($item['content']);
                            if ($inbox) {
                                $inboxCol = $inbox->getCollection(true, false, true);
                            }
                            $ret[] = [
                                "title" => $tmp_data,
                                "count" => count($item['inboxs']),
                                "inbox" => $inboxCol
                            ];
                        }
                    }


                }

            }
            return $ret;
        }
        return false;

    }

    public function getReceiversShort()
    {
        if ($this->getData('receivers')) {

            $ret = [];
            $arr = json_decode($this->getData('receivers'), true);

            include_once PATH_EXTENSIONS. 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
            $Inbox = new extInboxModelInbox2();

            foreach ($arr as $item) {
                if ( $item['inboxs'] && count($item['inboxs']) > 0 ) {


                    //if (!$item['content']) {
                        //$item['content'] = $item['inboxs'][0];
                    //}

                    /*
                    $temp = [];
                    foreach($item['inboxs'] as $inbox) {
                        $temp_inbox = $class->getByID($inbox);
                        $temp_collection = $temp_inbox->getCollection(true);
                        $temp[] = $temp_collection['title'];
                    }
                    $tmp_data = join(', ',$temp);
                    */

                    $className = explode('::', $item['typ']);
                    $className = 'extInboxRecipient'.ucfirst($className[0]).ucfirst($className[1]);
                    $typ = str_replace('::','_', $item['typ']);
                    include_once PATH_EXTENSIONS.'inbox'.DS . 'inboxs' . DS . $typ.'.class.php';

                    $tmp_data = $className::getTitle($item['content']);

                    $inboxCol = [];
                    foreach ($item['inboxs'] as $inbox) {
                        $inboxObj = $Inbox->getByID($inbox);
                        if ($inboxObj) {
                            $inboxCol[] = $inboxObj->getCollection(true, false, true);
                        }
                    }


                    $ret[] = [
                        "title" => $tmp_data,
                        "count" => count($item['inboxs']),
                        "inbox" => $inboxCol
                    ];
                }

            }
            return $ret;
        }
        return false;
    }

    public function getReceiversLong()
    {
        if ($this->getData('receivers')) {
            $ret = [];
            $arr = json_decode($this->getData('receivers'), true);
            foreach ($arr as $item) {
                if ( count($item['inboxs']) > 0) {
                    $ret[] = [
                        "title" => $item['typ'].'::'.$item['content'],
                        "count" => count($item['inboxs']),
                        "typ" => $item['typ'],
                        "content" => $item['content'],
                        "inboxs" => $item['inboxs']
                    ];
                }

            }
            return $ret;
        }
        return false;
    }








}
