<?php

/**
 *
 */
class extAusweisModelAntrag
{


    /**
     * @var data []
     */
    private $data = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return boolean
     */
    public static function isVisible()
    {

        return true;
    }


    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID()
    {
        return $this->data['id'];
    }

    public function getState()
    {
        return $this->data['state'];
    }

    public function getCreatedTime()
    {
        return $this->data['createdTime'];
    }

    public function getUserID()
    {
        return $this->data['user_id'];
    }

    public function getImage()
    {
        return $this->data['image'];
    }
    public function getDoneTime()
    {
        return $this->data['doneTime'];
    }
    public function getDoneUser()
    {
        return $this->data['doneUser'];
    }

    public function getCollection($full = false)
    {

        $collection = [
            "id" => $this->getID(),
            "state" => $this->getState(),
            "createdTime" => $this->getCreatedTime(),
            "user_id" => $this->getUserID(),
            "image" => $this->getImage(),
            "doneTime" => $this->getDoneTime(),
            "doneUser" => $this->getDoneUser()
        ];

        if ($full) {
            if ($this->getUserID()) {
                $temp_user = user::getUserByID($this->getUserID());
                if ($temp_user) {
                    $collection['user'] = $temp_user->getCollection(true);
                }
            }
            if ($this->getDoneUser()) {
                $temp_user = user::getUserByID($this->getDoneUser());
                if ($temp_user) {
                    $collection['doneUserUser'] = $temp_user->getCollection();
                }
            }


            $collection['imagePath'] = 'index.php?page=ext_ausweis&view=default&task=getAntragImage&path=' . 'user-' . $this->getUserID() . DS . $collection['image'];

            //$collection['imagePath'] = PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS . 'user-' . $this->getUserID() . DS . $collection['image'];

            //$collection['imagePath'] = FILE::makeThumb(PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS . 'user-' . $this->getUserID() . DS . $collection['image'], 'antrag-' . $this->getUserID(),'ext_ausweis');
        }

        return $collection;
    }

    /**
     * @return Array[]
     */
    public static function getMyByStatus($status = [1])
    {
        $user = DB::getSession()->getUser();

        if (!$user || !$status || !is_array($status)) {
            return false;
        }
        if ($user) {
            switch ($user->getUserTyp(true)) {
                case 'isPupil':
                    $users = [$user->getUserID()];
                    break;
                case 'isEltern':
                    $tmp_data = $user->getElternObject()->getMySchueler();

                    $users = [];
                    foreach ($tmp_data as $item) {
                        $users[] = $item->getUserID();
                    }

                    break;
                case 'isTeacher':
                    $users = [$user->getUserID()];
                    break;
                case 'isNone':
                    $users = [$user->getUserID()];
                    break;
            }
        }


        $where_users = '';
        foreach ($users as $s) {
            if ($where_users != '') {
                $where_users .= " OR ";
            }
            $where_users .= " `user_id` = " . (int)$s;
        }

        $where = '';
        foreach ($status as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " `state` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_ausweis_antrag WHERE (" . $where . ") AND (" . $where_users . ") ORDER BY createdTime DESC");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

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
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_ausweis_antrag WHERE " . $where . " ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function getByID($id = false)
    {
        if (!$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT * FROM ext_ausweis_antrag WHERE id = " . (int)$id);
        if ($dataSQL) {
            return new self($dataSQL);
        }
        return false;
    }


    public static function getByKlassen($klassen = false)
    {
        if (!$klassen || !is_array($klassen)) {
            return false;
        }
        $where = '';
        foreach ($klassen as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " b.schuelerKlasse = '" . (string)$s . "' ";
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_ausweis_antrag AS a
        LEFT JOIN schueler AS b ON b.schuelerUserID LIKE a.user_id 
         WHERE user_typ = 'isPupil' AND  ( " . $where . " ) ORDER BY a.doneTime, a.createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    public static function getByKlassenCount($klassen = false)
    {
        if (!$klassen || !is_array($klassen)) {
            return false;
        }
        $where = '';
        foreach ($klassen as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " b.schuelerKlasse = '" . (string)$s . "' ";
        }
        $ret = 0;
        $dataSQL = DB::getDB()->query("SELECT a.id FROM ext_ausweis_antrag AS a
        LEFT JOIN schueler AS b ON b.schuelerUserID LIKE a.user_id 
         WHERE a.state = 1 AND a.user_typ = 'isPupil' AND  ( " . $where . " ) ORDER BY a.doneTime, a.createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret++;
        }
        return $ret;
    }



    /**
     * @return Array[]
     */
    public static function setState($id = false, $status = false, $userID = 0, $ausweis_old_id = false)
    {
        $status = (int)$status;
        if (!$id || !$status) {
            return false;
        }

        if (DB::getDB()->query(
            "UPDATE ext_ausweis_antrag
                SET state = " . $status . ", doneTime = '" . date('Y-m-d H:i', time()) . "', doneUser = " . $userID . "
                WHERE id=" . (int)$id
        )) {



            if ($status == 2) { // Freigeben
                $data = self::getByID($id);



                include_once PATH_EXTENSION . 'models' . DS . 'Ausweis.class.php';

                $active_ausweis = extAusweisModelAusweis::getByUserID($data->getUserID());
                if ($active_ausweis) {
                    extAusweisModelAusweis::deleteByID($active_ausweis->getID());
                }

                if ($ausweis_old_id) {
                    extAusweisModelAusweis::deleteByID($ausweis_old_id);
                }
                $front_filepath = extAusweisModelAusweis::makeAusweis($data);
                if (extAusweisModelAusweis::save($id, $front_filepath, $data->getUserID())) {
                    return true;
                }
                return false;
            }

            if ($status == 1) { // Gesperrt

                /*
                include_once PATH_EXTENSION . 'models' . DS . 'Ausweis.class.php';
                if ( extAusweisModelAusweis::deleteByID($id) ) {
                    return true;
                }
                return false;
                */
            }

            return true;
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function save($userID = false, $image = false)
    {
        if (!$userID || !$image) {
            return false;
        }

        $status = 1;

        $auto = DB::getSettings()->getBoolean('extAusweis-antrag-freigeben');



        $user_type = '';
        $user = user::getUserByID($userID);
        if ($user) {
            $user_type = $user->getUserTyp(true);
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Ausweis.class.php';

        $path = extAusweisModelAusweis::makeUserFolder($userID);

        if ($path) {
            $img = str_replace('data:image/png;base64,', '', $image);
            $img = str_replace(' ', '+', $img);
            $img = base64_decode($img);
            $filename = 'profil_' . time() . '.png';

            file_put_contents($path . $filename, $img);
            if (file_exists($path . $filename)) {
                if (DB::getDB()->query("INSERT INTO ext_ausweis_antrag
                (
                    state,
                    createdTime,
                    user_id,
                    user_typ,
                    image
    
                ) values(
                " . (int)$status . ",
                CURRENT_TIMESTAMP,
                " .  DB::getDB()->escapeString($userID) . ",
                '" . DB::getDB()->escapeString($user_type) . "',
                '" .  $filename . "'
                )
                    ")) {

                    if ($auto) {
                        $newID = DB::getDB()->insert_id();
                        self::setState($newID, 2, 0);
                    }


                    return true;
                }
            }
        }



        return false;
    }
}
