<?php


class UnknownRecipient extends MessageRecipient {
	
	public function getSaveString() {
		return 'NULL';
	}
	public static function getAllInstances() {
		return [];
	}
	public function getDisplayName() {
		return 'Unbekannter Empfänger';
	}
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return false;
	}
	public function getRecipientUserIDs() {
		return [];
	}
	public static function getInstanceForSaveString($saveString) {
		return new UnknownRecipient();
	}
	public function getMissingNames() {
		return [];
	}

}

