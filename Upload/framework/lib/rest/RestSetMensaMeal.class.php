<?php

class RestSetMensaMeal extends AbstractRest {
    protected $statusCode = 200;
	
	public function execute($input, $request) {

		$data = $input['data'];

		$data['id'] = intval($data['id']);


		if ( $request[1] == 'delete' ) {
			
			if ( !$data['id'] ) {
				return [
					'error' => true,
					'msg' => 'Fehlende Daten'
				];
			}

			$dbRow = DB::getDB()->query_first("SELECT id FROM mensa_speiseplan WHERE id = " . $data['id'] . "");

			if ( $dbRow['id'] ) {
			
				DB::getDB()->query("DELETE FROM mensa_speiseplan WHERE id=".$data['id']);
				
				return [
					'error' => false,
					'done' => true
				];

			} else {

				return [
					'error' => true,
					'msg' => 'Fehlender Speiseplan Eintrag'
				];

			}
			exit;
		}

		if (!$data['date'] || !$data['title']) {
			return [
				'error' => true,
				'msg' => 'Fehlende Daten'
			];
		}

		$data['preis_schueler'] = str_replace(',','.',$data['preis_schueler']);
		$data['preis_default'] = str_replace(',','.',$data['preis_default']);

		

		if ( $data['id'] ) {

			$dbRow = DB::getDB()->query_first("SELECT id FROM mensa_speiseplan WHERE id = " . $data['id'] . "");

			if ( $dbRow['id'] ) {

				DB::getDB()->query("UPDATE mensa_speiseplan SET
					`title` = '".DB::getDB()->escapeString($data['title'])."',
					`preis_schueler` = '".DB::getDB()->escapeString($data['preis_schueler'])."',
					`preis_default` = '".DB::getDB()->escapeString($data['preis_default'])."',
					`desc` = '".DB::getDB()->escapeString($data['desc'])."',
					`vegetarisch` = '".DB::getDB()->escapeString($data['vegetarisch'])."',
					`vegan` = '".DB::getDB()->escapeString($data['vegan'])."',
					`laktosefrei` = '".DB::getDB()->escapeString($data['laktosefrei'])."',
					`glutenfrei` = '".DB::getDB()->escapeString($data['glutenfrei'])."',
					`bio` = '".DB::getDB()->escapeString($data['bio'])."',
					`regional` = '".DB::getDB()->escapeString($data['regional'])."'
					WHERE id = " . $dbRow['id'] . ";");

				return [
					'error' => false,
					'done' => true
				];

			} else {
				return [
					'error' => true,
					'msg' => 'Fehlender Speiseplan Eintrag'
				];
			}

			
		} else {

			DB::getDB()->query("INSERT INTO mensa_speiseplan (
				`date`,
				`title`,
				`preis_schueler`,
				`preis_default`,
				`desc`,
				`vegetarisch`,
				`vegan`,
				`laktosefrei`,
				`glutenfrei`,
				`bio`,
				`regional`
				) values (
				'".DB::getDB()->escapeString($data['date'])."',
				'".DB::getDB()->escapeString($data['title'])."',
				'".DB::getDB()->escapeString($data['preis_schueler'])."',
				'".DB::getDB()->escapeString($data['preis_default'])."',
				'".DB::getDB()->escapeString($data['desc'])."',
				'".DB::getDB()->escapeString($data['vegetarisch'])."',
				'".DB::getDB()->escapeString($data['vegan'])."',
				'".DB::getDB()->escapeString($data['laktosefrei'])."',
				'".DB::getDB()->escapeString($data['glutenfrei'])."',
				'".DB::getDB()->escapeString($data['bio'])."',
				'".DB::getDB()->escapeString($data['regional'])."'
			);");
			
			return [
				'error' => false,
				'done' => true
			];

		}
		

	}
	
	public function getAllowedMethod() {
		return 'POST';
	}

	protected function malformedRequest() {
		$this->statusCode = 400;
	}

	/**
	 * Überprüft, ob ein Modul eine System Authentifizierung benötigt. (z.B. zum Abfragen aller Schülerdaten)
	 * @return boolean
	 */
	public function needsSystemAuth() {
		return false;
	}

	public function needsUserAuth() {
		return true;
	}
	
}	

?>