<?php

/**
 *
 */
class extStundenplanModelStundenplan extends ExtensionModel
{

    static $table = 'ext_finanzen_antrag';

    static $fields = [
        'title',
        'payee',
        'users',
        'amount',
        'dueDate',
        'receipt',
        'createdTime',
        'createdUserID',
        'state'
    ];

    
    static $defaults = [
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


    public function getTitle()
    {
        return $this->getData('title');
    }
    public function getPayee()
    {
        return $this->getData('payee');
    }
    public function getUsers()
    {
        return $this->getData('users');
    }
    public function getAmount()
    {
        return $this->getData('amount');
    }
    public function getDueDate()
    {
        return $this->getData('dueDate');
    }
    public function getReceipt()
    {
        return $this->getData('receipt');
    }


    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
        }

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getByStatus($status = [1])
    {
        if (!$status || !is_array($status)) {
            return false;
        }
        $where = '';
        foreach ($status as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " `state` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_finanzen_antrag WHERE " . $where . " ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }




    /**
     * @return Array[]
     */
    public function changeState($status = false)
    {
        $status = (int)$status;
        if (!$status) {
            return false;
        }

        if ( parent::setState($status) ) {

            if ($status == 2) { // freigegeben
                
                if ( !$this->toBuchungen() ) {
                    return false;
                }
                
                
                
            }

            return true;
        }
        return false;
    }


    /**
     * @return Array[]
     */
    /*
    public static function submit($userID = false, $title = false, $payee = false, $users = false, $amount = false, $dueDate = false, $receipt = false)
    {
        if (!$userID || !$title || !$payee || !$users || !$amount) {
            return false;
        }

        $status = 1;

        if (DB::getDB()->query("INSERT INTO ext_finanzen_antrag
            (
                state,
                createdTime,
                createdUserID,
                title,
                payee,
                users,
                amount,
                dueDate,
                receipt
            ) values(
            " . (int)$status . ",
            CURRENT_TIMESTAMP,
            " .  DB::getDB()->escapeString($userID) . ",
            '" .  DB::getDB()->escapeString($title) . "',
            '" .  DB::getDB()->escapeString($payee) . "',
            '" . DB::getDB()->escapeString($users) . "',
            " . (float)DB::getDB()->escapeString($amount) . ",
            '" . DB::getDB()->escapeString($dueDate) . "',
            " . (int)DB::getDB()->escapeString($receipt) . "
            )
                ")) {
            return true;
        }
        return false;
    }
*/

    public function toBuchungen()
    {

        //include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Buchung.class.php';

        $users = $this->getUsers();

        $users = explode(',', (string)$users);
        if ($users && count($users) > 1) {
            foreach ($users as $userID) {
                $this->setAntragToBuchung($userID);
            }
        }
    }


    public function setAntragToBuchung($userID = false)
    {

        if (!$userID || !$this->getID()) {
            return false;
        }

        $myUserID = DB::getSession()->getUser()->getUserID();

        $data =  [
            'antrag_id' => $this->getID(),
            'user_id' => $userID,
            'title' => $this->getTitle(),
            'amount' => $this->getAmount(),
            'createdTime' => date( 'Y-m-d H:i:s', time() ),
            'createdUserID' => $myUserID
        ];

        include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Buchung.class.php';
        $class = new extFinanzenModelBuchung();

        if ( $class->save($data) ) {
            return true;
        }
        return false;
    }


}
