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
		return $this->settingsValues[$setting];
	}

	public function getInteger($setting) {
        return intval($this->settingsValues[$setting]);
    }

    /**
     * @param $setting
     * @return FileUpload|null
     */
    public function getUpload($setting) {
	    return FileUpload::getByID(self::getInteger($setting));
    }
	
	public function setValue($setting, $value) {
		$this->settingsValues[$setting] = $value;
		DB::getDB()->query("INSERT INTO settings (settingName, settingValue) values('$setting','" . DB::getDB()->escapeString($value) . "') ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($value) . "'");
	}
	
	public function getBoolean($setting) {
		if($this->settingsValues[$setting] == "") return false;
		return $this->settingsValues[$setting] != "0";
	}
	
	public function getAllSettings() {
	    return $this->settingsValues;
	}
	
	public function getSelectedItems($setting) {
	    if($this->settingsValues[$setting] == "") return [];
	    else return explode("~~~~",$this->settingsValues[$setting]);
	}
	
	public function isItemSelected($setting, $item) {
	    return in_array($item,$this->getSelectedItems($setting));
	}
	
}


?>