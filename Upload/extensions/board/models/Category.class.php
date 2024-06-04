<?php

class extBoardModelCategory extends ExtensionModel
{

    static $table = 'ext_board_category';

    static $fields = [
        'id',
        'state',
        'createdTime',
        'createdUserID',
        'title',
        'acl'
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

    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {

        }

        return $collection;
    }

    public function getAllAllowed()
    {
        $ret = [];
        $data = $this->getByState([1]);
        if ($data) {
            $userTyp = DB::getSession()->getUser()->getUserTyp(true);
            $userAdmin = DB::getSession()->getUser()->isAdmin();
            if ($userAdmin) {
                $ret = $data;
            }
            if (count($ret) == 0 && $userTyp) {
                foreach ($data as $item) {
                    if ($item->getData('acl')) {
                        $acl = json_decode($item->getData('acl'));
                        if ($acl) {
                            if ($acl->{$userTyp}) {
                                $ret[] = $item;
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }


}
