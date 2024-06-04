<?php

class ClearTmpFolders extends AbstractCron {


	public function __construct() {
	}

	public function execute() {

        if (is_dir(PATH_TMP)) {
            FILE::removeFolder(PATH_TMP);
        }

        if (is_dir(PATH_WWW_TMP)) {
            FILE::removeFolder(PATH_WWW_TMP);
        }

        return true;
	}
	
	public function getName() {
		return "Temporäre Ordner löschen";
	}
	
	public function getDescription() {
		return "Entfernt alle temporären Ordner im System";
	}
	

	public function getCronResult() {
        return ['success' => 1, 'resultText' => 'Erfolgreich'];
    }
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 3600;
	}
}



?>