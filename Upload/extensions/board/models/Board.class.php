<?php

class extBoardModelBoard extends ExtensionModel
{

    static $table = 'ext_board';

    static $fields = [
        'id',
        'createdUserID',
        'createdTime',
        'state',
        'title',
        'sort',
        'cat_id'

    ];


    static $defaults = [
        'sort' => 1
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


    public function getCollection($full = false, $withItems = false, $withACL = false, $adminGroup = false, $withRead = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('cat_id')) {
                include_once PATH_EXTENSION . 'models' . DS . 'Category.class.php';
                $Category = new extBoardModelCategory();
                $tmp_data = $Category->getByID($this->getData('cat_id'));
                if ($tmp_data) {
                    $collection['catTitle'] = $tmp_data->getData('title');
                    if ($withACL) {
                        if ($tmp_data->getData('acl')) {
                            $userTyp = DB::getSession()->getUser()->getUserTyp(true);
                            $userAdmin = DB::getSession()->getUser()->isAdmin();
                            if ( $adminGroup && DB::getSession() && DB::getSession()->isAdminOrGroupAdmin($adminGroup) === true ) {
                                $userAdmin = true;
                            }
                            $acl = json_decode($tmp_data->getData('acl'));
                            if (!$userAdmin && $acl) {
                                if (!$acl->{$userTyp}) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($withItems) {
            include_once PATH_EXTENSION . 'models' . DS . 'Item.class.php';
            $Item = new extBoardModelItem();
            $items = $Item->getByParentID($collection['id']);
            $childs = [];
            $today = date('Y-m-d', time());
            foreach ($items as $item) {
                if ($item->getState() == 1) {
                    if ( $item->getData('enddate') ) {
                        if ($today < $item->getData('enddate')) {
                            $childs[] = $item->getCollection(false, true);
                        }
                    } else {
                        $childs[] = $item->getCollection(false, true);
                    }
                }
            }
            $collection['items'] = $childs;
        }
        if ($withRead) {
            include_once PATH_EXTENSION . 'models' . DS . 'ItemRead.class.php';
            $ItemRead = new extBoardModelItemRead();
            foreach($collection['items'] as $key => $item) {
                $reads = $ItemRead->getByParentID($item['id']);
                if ($reads) {
                    $collection['items'][$key]['read'] = true;
                }
            }

        }
        return $collection;
    }





}
