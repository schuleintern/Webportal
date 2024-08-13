<?php
/**
 *
 */
class extKlassenkalenderModelKalender extends ExtensionModel
{


    static $table = 'ext_klassenkalender';

    static $fields = [
        'id',
        'sort',
        'state',
        'createdTime',
        'createdUserID',
        'title',
        'color',
        'acl',
        'admins'
    ];


    static $defaults = [
        'admins' => '',
        'acl' => 0,
        'state' => 1
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


    public function getCollection($full = false, $withAdmins = false)
    {
        $collection = parent::getCollection();

        if ($full) {

            if ($collection['acl']) {
                $collection['aclID'] = $collection['acl'];
                $user = DB::getSession()->getUser();
                $collection['acl'] = ACL::getAcl( $user, false, (int)$collection['acl'] );
            } else {
                $collection['acl'] = ACL::getBlank();
            }

        }
        if ($withAdmins) {
            if ($collection['admins']) {
                $arr = [];
                $admins = json_decode($collection['admins']);
                foreach ($admins as $admin) {
                    if ( $foo = User::getCollectionByID($admin) ) {
                        $arr[] = $foo;
                    }
                }
                $collection['admins'] = $arr;
            }
        }

        $collection['preSelect'] = 0;
        if (DB::getSession()->getUser()->isAdmin()) {
            $collection['preSelect'] = 1;
        }

        return $collection;
    }

/*
    public function getByDate($date = false)
    {

        if (!$this->getModelTable()) {
            return false;
        }
        if (!$date) {
            return false;
        }
        $ret = [];

        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE dateStart <= :dateStart AND dateEnd >= :dateEnd ', ['dateStart' => $date, 'dateEnd' => $date])->fetchAll();
        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;

    }


    public function add($userID = false, $user = false, $dateStart = false, $dateEnd = false, $dateAdd = false, $info = '')
    {

        if (!$userID || !$user || !$dateStart || !$dateEnd || !$dateAdd) {
            return false;
        }

        $asv_id = 0;
        $userObj = user::getUserByID($user);
        if ($userObj->isPupil()) {
            $asv_id = $userObj->getPupilObject()->getAsvID();
        }
        $data = [
            'user_id' => $user,
            'asv_id' => $asv_id,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'info' => $info,
            'createdTime' => date('Y-m-d H:i', time()),
            'createdUserID' => $userID,
            'days' => $dateAdd
        ];

        if ($this->save($data)) {
            return true;
        }
        return false;

    }
*/


}