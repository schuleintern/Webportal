<?php
/**
 *
 */
class extVplanModelList
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
    public function getData() {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getDate() {
        return $this->data['date'];
    }
    public function getKlasse() {
        return $this->data['klasse'];
    }
    public function getStunde() {
        return $this->data['stunde'];
    }
    public function getUserAlt() {
        return $this->data['user_alt'];
    }
    public function getUserNeu() {
        return $this->data['user_neu'];
    }
    public function getFachAlt() {
        return $this->data['fach_alt'];
    }
    public function getFachNeu() {
        return $this->data['fach_neu'];
    }
    public function getRaumNeu() {
        return $this->data['raum_neu'];
    }
    public function getRaumAlt() {
        return $this->data['raum_alt'];
    }
    public function getInfo_1() {
        return $this->data['info_1'];
    }
    public function getInfo_2() {
        return $this->data['info_2'];
    }
    public function getInfo_3() {
        return $this->data['info_3'];
    }


    public function getCollection() {

        $collection = [
            "id" => $this->getID(),
            "date" => $this->getDate(),
            "stunde" => $this->getStunde(),
            //"klasse" => $this->getKlasse(),
            //"user_alt" => $this->getUserAlt(),
            //"user_neu" => $this->getUserNeu(),
            //"fach_alt" => $this->getFachAlt(),
            //"fach_neu" => $this->getFachNeu(),
            //"raum_neu" => $this->getRaumNeu(),
            //"raum_alt" => $this->getRaumAlt(),
            //"info_1" => $this->getInfo_1(),
            //"info_2" => $this->getInfo_2(),
            //"info_3" => $this->getInfo_3()
        ];


        $userTyp = DB::getSession()->getUser()->getUserTyp(true);

        if ( self::aclRule('extVplan-col-show-klasse', $userTyp) ) {
            $collection['klasse'] = $this->getKlasse();
        }
        if ( self::aclRule('extVplan-col-show-user_neu', $userTyp) ) {
            $collection['user_neu'] = $this->getUserNeu();
        }
        if ( self::aclRule('extVplan-col-show-user_alt', $userTyp) ) {
            $collection['user_alt'] = $this->getUserAlt();
        }
        if ( self::aclRule('extVplan-col-show-fach_alt', $userTyp) ) {
            $collection['fach_alt'] = $this->getFachAlt();
        }
        if ( self::aclRule('extVplan-col-show-fach_neu', $userTyp) ) {
            $collection['fach_neu'] = $this->getFachNeu();
        }
        if ( self::aclRule('extVplan-col-show-raum_alt', $userTyp) ) {
            $collection['raum_alt'] = $this->getRaumAlt();
        }
        if ( self::aclRule('extVplan-col-show-raum_neu', $userTyp) ) {
            $collection['raum_neu'] = $this->getRaumNeu();
        }
        if ( self::aclRule('extVplan-col-show-info_1', $userTyp) ) {
            $collection['info_1'] = $this->getInfo_1();
        }
        if ( self::aclRule('extVplan-col-show-info_2', $userTyp) ) {
            $collection['info_2'] = $this->getInfo_2();
        }
        if ( self::aclRule('extVplan-col-show-info_3', $userTyp) ) {
            $collection['info_3'] = $this->getInfo_3();
        }



        return $collection;
    }

    public static function aclRule($rule = false, $userTyp = false) {

        if (!$rule || !$userTyp) {
            return false;
        }
        $aclCol = DB::getSettings()->getValue((string)$rule);
        if ($aclCol && $userTyp) {
            $aclCol = json_decode($aclCol);
            if (isset($aclCol->$userTyp) && $aclCol->$userTyp == 1) {
                return true;
            }
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function getByDate($date = false) {

        if (!$date) {
            return false;
        }

        $ret = [];


        $dataSQL = DB::getDB()->query("SELECT * FROM ext_vplan_list WHERE `date` = '".$date."'");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;


    }


}