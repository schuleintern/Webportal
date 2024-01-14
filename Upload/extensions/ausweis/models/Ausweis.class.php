<?php

/**
 *
 */
class extAusweisModelAusweis
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

    public function getCreatedUserID()
    {
        return $this->data['createdUserID'];
    }

    public function getFrontPath()
    {
        return $this->data['front_path'];
    }
    public function getAntragID()
    {
        return $this->data['antrag_id'];
    }
    public function getUserID()
    {
        return $this->data['user_id'];
    }

    public function getCollection($full = false)
    {

        $collection = [
            "id" => $this->getID(),
            "state" => $this->getState(),
            "createdTime" => $this->getCreatedTime(),
            "createdUserID" => $this->getCreatedUserID(),
            "front_path" => $this->getFrontPath(),
            "antrag_id" => $this->getAntragID(),
            "user_id" => $this->getUserID(),

        ];

        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
            if ($this->getUserID()) {
                $temp_user = user::getUserByID($this->getUserID());
                if ($temp_user) {
                    $collection['user'] = $temp_user->getCollection();
                    $collection['userName'] = $collection['user']['name'];
                }
            }
        }

        return $collection;
    }


    public static function getMy()
    {
        $user = DB::getSession()->getUser();
        if ($user && $user->getUserID()) {
            $dataSQL = DB::getDB()->query_first("SELECT * FROM ext_ausweis_ausweis WHERE state = 1 AND user_id = " . (int)$user->getUserID());
            if ($dataSQL) {
                return new self($dataSQL);
            }
            return false;
        }
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
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_ausweis_ausweis WHERE " . $where . " ORDER BY createdTime");
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
        $dataSQL = DB::getDB()->query_first("SELECT * FROM ext_ausweis_ausweis WHERE id = " . (int)$id);
        if ($dataSQL) {
            return new self($dataSQL);
        }
        return false;
    }

    public static function getByUserID($id = false)
    {
        if (!$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT * FROM ext_ausweis_ausweis WHERE user_id = " . (int)$id);
        if ($dataSQL) {
            return new self($dataSQL);
        }
        return false;
    }



    /**
     * @return Array[]
     */
    public static function setState($id = false, $status = false, $userID = false)
    {
        $status = (int)$status;
        if (!$id || !$status) {
            return false;
        }

        $data = self::getByID($id);

        if (DB::getDB()->query(
            "UPDATE ext_ausweis_ausweis
                SET state = " . $status . "
                WHERE id=" . (int)$id
        )) {

            if ($status == 1) { // freigeben
                include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
                extAusweisModelAntrag::setState($data->getAntragID(), 2, 0, $id); // freigeben
            }

            if ($status == 2) { // gesperrt
                include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
                extAusweisModelAntrag::setState($data->getAntragID(), 3, 0, $id); // sperren
            }


            return true;
        }
        return false;
    }

    
    public static function deleteByID($id = false)
    {

        if (!$id) {
            return false;
        }
        $data = self::getByID($id);

        if ($data && $data->getFrontPath()) {
            $path = PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS.$data->getFrontPath();

            if (file_exists($path)) {
                unlink($path);
            }
        }
        if (!DB::getDB()->query("DELETE FROM ext_ausweis_ausweis WHERE id = " . (int)$id)) {
            return false;
        }

        return true;
    }
    


    /**
     * @return Array[]
     */
    public static function save($antrag_id = false, $front_path = false, $user_id = false)
    {
        if (!$antrag_id || !$antrag_id || !$user_id) {
            return false;
        }

        $status = 1;

        if (DB::getDB()->query("INSERT INTO ext_ausweis_ausweis
            (
                state,
                createdTime,
                createdUserID,
                antrag_id,
                user_id,
                front_path
            ) values(
            " . (int)$status . ",
            CURRENT_TIMESTAMP,
            " . DB::getSession()->getUserID() . ",
            " .  DB::getDB()->escapeString($antrag_id) . ",
            " .  DB::getDB()->escapeString($user_id) . ",
            '" .  DB::getDB()->escapeString($front_path) . "'
            )
                ")) {
            return true;
        }
        return false;
    }



    public static function makeUserFolder($user_id = false) {

        if (!$user_id) {
            return false;
        }

        $path = PATH_DATA . 'ext_ausweis' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }

        $path = $path . 'ausweis' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path = $path . 'user-' . $user_id . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        return $path;

    }


    public function makeAusweis($data = false)
    {

        if (!$data) {
            return false;
        }

        $setting_city = explode(',', DB::getSettings()->getValue('extAusweis-antrag-print-city'));
        $setting_birthday = explode(',', DB::getSettings()->getValue('extAusweis-antrag-print-birthday'));
        $setting_profil = explode(',', DB::getSettings()->getValue('extAusweis-antrag-print-profil'));
        $setting_name = explode(',', DB::getSettings()->getValue('extAusweis-antrag-print-name'));
        $setting_size = explode(',', DB::getSettings()->getValue('extAusweis-antrag-print-size'));

        $city = "";
        $birthday = "";
        $asv = "";

        $user = user::getUserByID($data->getUserID());
        if ($user) {
            switch ($user->getUserTyp(true)) {
                case 'isPupil':
                    $pupil = $user->getPupilObject();
                    if ($pupil) {

                        if (DB::getSettings()->getValue('extAusweis-antrag-print-city')) {
                            $city = $pupil->getWohnort();
                        }
                        if (DB::getSettings()->getValue('extAusweis-antrag-print-birthday')) {
                            $birthday = $pupil->getGeburtstagAsNaturalDate();
                        }
                        $asv = $pupil->getAsvID();
                    }

                    break;
                case 'isEltern':

                    break;
                case 'isTeacher':

                    break;
                case 'isNone':

                    break;
            }
        }

        $path = self::makeUserFolder( $data->getUserID() );

        if (!$setting_size[0]) {
            $setting_size[0] = 1083;
        }
        if (!$setting_size[1]) {
            $setting_size[1] = 680;
        }
        $width = $setting_size[0];
        $height = $setting_size[1];

        $im = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 64, 64, 64);

        $font_normal = PATH_EXTENSION . 'assets' . DS . 'fonts' . DS . 'DIN_Regular.ttf';
        $font_bold = PATH_EXTENSION . 'assets' . DS . 'fonts' . DS . 'DIN_Bold.otf';


        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        $path_vorlagen = PATH_DATA . 'ext_ausweis' . DS . 'vorlagen' . DS;
        $path_layer_1 = DB::getSettings()->getValue('extAusweis-antrag-print-layer_1');
        if ($path_layer_1) {
            if (is_dir($path_vorlagen) && file_exists($path_vorlagen . $path_layer_1)) {
                $layer_1 = imagecreatefrompng($path_vorlagen . $path_layer_1);
                $layer_1_width = imagesx($layer_1);
                $layer_1_height = imagesy($layer_1);
                imagecopyresized($im, $layer_1, 0, 0, 0, 0, $width, $height, $layer_1_width, $layer_1_height);
            }
        }
        

        //imagecolortransparent($logo, $white);
        //imagealphablending($logo, false);
        //imagesavealpha($logo, true);

        
        if (!$setting_name[0]) {
            $setting_name[0] = 230;
        }
        if (!$setting_name[1]) {
            $setting_name[1] = 275;
        }
        imagettftext($im, 30, 0, $setting_name[0], $setting_name[1], $black, $font_bold, $user->getDisplayName());

        if ($city && $setting_city[0] && $setting_city[1]) {
            //imagettftext($im, 20, 0, 230, 380, $black, $font_normal, $city);
            imagettftext($im, 20, 0, $setting_city[0], $setting_city[1], $black, $font_normal, $city);
        }
        if ($birthday && $setting_birthday[0] && $setting_birthday[1]) {
            //imagettftext($im, 20, 0, 230, 460, $black, $font_normal, $birthday);
            imagettftext($im, 20, 0, $setting_birthday[0], $setting_birthday[1], $black, $font_normal, $birthday);
        }

        imagettftext($im, 15, 0, 63, 600, $black, $font_normal, $asv);


        if ($setting_profil) {
            if ($data->getImage()) {
                if (file_exists($path.$data->getImage())) {
                    $profil = imagecreatefrompng($path.$data->getImage());
                    $profil_width = imagesx($profil);
                    $profil_height = imagesy($profil);
                    //imagecopyresized($im, $profil, 800, 230, 0, 0, 150, 200, $profil_width, $profil_height);
                    if (!$setting_profil[0]) {
                        $setting_profil[0] = 800;
                    }
                    if (!$setting_profil[1]) {
                        $setting_profil[1] = 230;
                    }
                    if (!$setting_profil[2]) {
                        $setting_profil[2] = 150;
                    }
                    if (!$setting_profil[3]) {
                        $setting_profil[3] = 200;
                    }
                    imagecopyresized($im, $profil, $setting_profil[0], $setting_profil[1], 0, 0, $setting_profil[2], $setting_profil[3], $profil_width, $profil_height);
                }
            }
        }


        $path_layer_2 = DB::getSettings()->getValue('extAusweis-antrag-print-layer_2');
        if ($path_layer_2) {
            if (is_dir($path_vorlagen) && file_exists($path_vorlagen . $path_layer_2)) {
                $layer_2 = imagecreatefrompng($path_vorlagen . $path_layer_2);
                $layer_2_width = imagesx($layer_2);
                $layer_2_height = imagesy($layer_2);
                imagecopyresized($im, $layer_2, 0, 0, 0, 0, $width, $height, $layer_2_width, $layer_2_height);
            }
        }


        imagepng($im, $path . 'front.png');
        imagedestroy($im);

        return 'user-' . $data->getUserID() . DS . 'front.png';

        /*
        include_once PATH_EXTENSION . 'models' . DS . 'AusweisTCPDF.class.php';

        $pdf = new AusweisTCPDF([
            "vorname" => $user->getFirstName(),
            "nachname" => $user->getLastName(),
            "plz" => '1',
            "ort" => '2',
            "geburtstag" => '3',
            "ablaufdatum" => '4',
            "image" => $data->getImage()
        ], true, true);


        $pdf->Output('gaga.pdf', 'I');
        */
    }
}
