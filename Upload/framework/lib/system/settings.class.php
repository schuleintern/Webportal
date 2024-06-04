<?php 


class settings {
	
	private $settingsValues = array();
	
	public function init() {
		$values = DB::getDB()->query("SELECT * FROM settings");
		
		while($value = DB::getDB()->fetch_array($values)) {
			$this->settingsValues[$value['settingName']] = $value['settingValue'];
		}
	}
	
	public function getValue($setting) {
            return array_key_exists($setting, $this->settingsValues) ? $this->settingsValues[$setting] : null;
	}

	public function getInteger($setting) {
        return intval($this->getValue($setting));
    }

    /**
     * @param $setting
     * @return FileUpload|null
     */
    public function getUpload($setting) {
	    return FileUpload::getByID(self::getInteger($setting));
    }
	
	public function setValue($setting, $value) {
		DB::getDB()->query("INSERT INTO settings (settingsExtension, settingName, settingValue) values('','$setting','" . DB::getDB()->escapeString($value) . "') ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($value) . "'");

		// Hirstory bei Änderung des Wertes
        if(self::getValue($setting) != $value) {
            DB::getDB()->query("INSERT INTO settings_history
                (
                 settingHistoryName,
                 settingHistoryChangeTime,
                 settingHistoryOldValue,
                 settingHistoryNewValue,
                 settingHistoryUserID
                 ) values (
                    '" . $setting . "',
                    UNIX_TIMESTAMP(),
                    '" . DB::getDB()->escapeString(self::getValue($setting)) . "',
                    '" . DB::getDB()->escapeString($value) . "',
                    " . ((DB::isLoggedIn()) ? ("'" . DB::getSession()->getUser()->getUserID() . "'") : "null") . "                           
                 )");
        }


        $this->settingsValues[$setting] = $value;
    }

    public function getHistory($setting) {
        $data = DB::getDB()->query("SELECT * FROM settings_history WHERE settingHistoryName='$setting' ORDER BY settingHistoryChangeTime DESC LIMIT 50");

        $result = [];

        while($d = DB::getDB()->fetch_array($data)) {
            $result[] = [
                'changeTime' => $d['settingHistoryChangeTime'],
                'oldValue' => $d['settingHistoryOldValue'],
                'newValue' => $d['settingHistoryNewValue'],
                'userID' => $d['settingHistoryUserID']
            ];
        }

        return $result;
    }
	
	public function getBoolean($setting) {
		if($this->getValue($setting) == "") return false;
		return $this->getValue($setting) != "0";
	}
	
	public function getAllSettings() {
	    return $this->settingsValues;
	}
	
	public function getSelectedItems($setting) {
	    if($this->getValue($setting) == "") return [];
	    else return explode("~~~~",$this->getValue($setting));
	}
	
	public function isItemSelected($setting, $item) {
	    return in_array($item,$this->getSelectedItems($setting));
	}
	
}


?>