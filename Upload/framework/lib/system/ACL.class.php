<?php

/**
 * Globale Access Control List class
 * 
 * @author: Christian Marienfeld
 */

class ACL {


  public function getAcl($user, $moduleClass = false, $id = false) {

    //return 'ACL';
    // $userID = DB::getSession()->getUser();
		// $moduleClass = get_called_class();

    $userID = $user->getUserID();
		$acl = [];

		if ($userID && $moduleClass && $id == false) {
			$aclDB = DB::getDB ()->query_first ( "SELECT * FROM acl WHERE moduleClass = '".$moduleClass."' ");
		}

		if ($userID && $moduleClass == false && $id) {
			$aclDB = DB::getDB ()->query_first ( "SELECT * FROM acl WHERE id = '".$id."' ");
		}

		if (!$aclDB) {
			return false;
		}

		$acl['groups'] = [
			'schueler' => ['read' => $aclDB['schuelerRead'], 'write' => $aclDB['schuelerWrite'], 'delete' => $aclDB['schuelerDelete'] ],
			'eltern' => [ 'read' => $aclDB['elternRead'], 'write' => $aclDB['elternWrite'], 'delete' => $aclDB['elternDelete'] ],
			'lehrer' => [ 'read' => $aclDB['lehrerRead'], 'write' => $aclDB['lehrerWrite'], 'delete' => $aclDB['lehrerDelete'] ],
			'none' => [ 'read' => $aclDB['noneRead'], 'write' => $aclDB['noneWrite'], 'delete' => $aclDB['noneDelete'] ],
			'owne' => [ 'read' => $aclDB['owneRead'], 'write' => $aclDB['owneWrite'], 'delete' => $aclDB['owneDelete'] ]
		];;

		$acl['user']['admin'] = $user->isAdmin();
		$acl['user']['schueler'] = $user->isPupil();
		$acl['user']['lehrer'] = $user->isTeacher();
		$acl['user']['eltern'] = $user->isEltern();
		$acl['user']['sekretariat'] = $user->isSekretariat();

		if (!$acl['user']['schueler'] && !$acl['user']['lehrer'] && !$acl['user']['eltern']) {
			$acl['user']['none'] = 1;
		}

		if ( DB::getSession()->isMember($moduleClass::getAdminGroup()) ) {
			$acl['user']['admin'] = true;
		}
		
		$acl['rights'] = [
			'read' => 0,
			'write' => 0,
			'delete' => 0
		];

		if ( $acl['user']['schueler'] == 1 ) {
			$acl['rights']['read'] = $acl['groups']['schueler']['read'];
			$acl['rights']['write'] = $acl['groups']['schueler']['write'];
			$acl['rights']['delete'] = $acl['groups']['schueler']['delete'];

		} else if ( $acl['user']['eltern'] == 1 ) {
			$acl['rights']['read'] = $acl['groups']['eltern']['read'];
			$acl['rights']['write'] = $acl['groups']['eltern']['write'];
			$acl['rights']['delete'] = $acl['groups']['eltern']['delete'];

		} else if ( $acl['user']['lehrer'] == 1 ) {
			$acl['rights']['read'] = $acl['groups']['lehrer']['read'];
			$acl['rights']['write'] = $acl['groups']['lehrer']['write'];
			$acl['rights']['delete'] = $acl['groups']['lehrer']['delete'];

		} else if ( $acl['user']['none'] == 1 ) {
			$acl['rights']['read'] = $acl['groups']['none']['read'];
			$acl['rights']['write'] = $acl['groups']['none']['write'];
			$acl['rights']['delete'] = $acl['groups']['none']['delete'];
		}

		if ( $acl['user']['admin'] == 1 ) {
			$acl['rights']['read'] = 1;
			$acl['rights']['write'] = 1;
			$acl['rights']['delete'] = 1;
		}

		$acl['owne'] = $acl['groups']['owne'];

		// $acl['rights']['read'] = 1;
		// $acl['rights']['write'] = 0;
    // $acl['rights']['delete'] = 0;
    //print_r($acl);
    return $acl;

  }


	public function setAcl( $row, $module = false ) {


		if ( !isset($row['id']) ) {
			return false;
		}

		if ( $row['id'] ) {

			if ($module) {
				$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['id']) . " AND moduleClass = '".$module."'");
			} else {
				$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['id']));
			}

			if ( $dbRow['id'] ) {
				
				//echo $row['schuelerWrite'];

				DB::getDB()->query("UPDATE acl SET
					schuelerRead = ".intval($row['schuelerRead']).",
					schuelerWrite = ".intval($row['schuelerWrite']).",
					schuelerDelete = ".intval($row['schuelerDelete']).",
					elternRead = ".intval($row['elternRead']).",
					elternWrite = ".intval($row['elternWrite']).",
					elternDelete = ".intval($row['elternDelete']).",
					lehrerRead = ".intval($row['lehrerRead']).",
					lehrerWrite = ".intval($row['lehrerWrite']).",
					lehrerDelete = ".intval($row['lehrerDelete']).",
					noneRead = ".intval($row['noneRead']).",
					noneWrite = ".intval($row['noneWrite']).",
					noneDelete = ".intval($row['noneDelete']).",
					owneRead = ".intval($row['owneRead']).",
					owneWrite = ".intval($row['owneWrite']).",
					owneDelete = ".intval($row['owneDelete'])."
					WHERE id = " . intval($row['id']) . ";");
				
				return [
					'error' => false,
					'done' => true,
					'aclID' => intval($row['id'])
				];

			} else {
				return [
					'error' => true,
					'msg' => 'Fehlende ACL Eintrag'
				];
			}

								
		} else {

			if (!$module) {
				$module = '';
			}

			DB::getDB()->query("INSERT INTO acl (
				moduleClass,
				schuelerRead,
				schuelerWrite,
				schuelerDelete,
				elternRead,
				elternWrite,
				elternDelete,
        lehrerRead,
        lehrerWrite,
        lehrerDelete,
        noneRead,
        noneWrite,
        noneDelete,
        owneRead,
        owneWrite,
				owneDelete
				) values (
				'".DB::getDB()->encodeString($module)."',
				'".DB::getDB()->encodeString($row['schuelerRead'])."',
				'".DB::getDB()->escapeString($row['schuelerWrite'])."',
				'".DB::getDB()->escapeString($row['schuelerDelete'])."',
				'".DB::getDB()->encodeString($row['elternRead'])."',
				'".DB::getDB()->encodeString($row['elternWrite'])."',
				'".DB::getDB()->encodeString($row['elternDelete'])."',
				'".DB::getDB()->encodeString($row['lehrerRead'])."',
				'".DB::getDB()->encodeString($row['lehrerWrite'])."',
				'".DB::getDB()->encodeString($row['lehrerDelete'])."',
				'".DB::getDB()->encodeString($row['noneRead'])."',
				'".DB::getDB()->encodeString($row['noneWrite'])."',
				'".DB::getDB()->encodeString($row['noneDelete'])."',
				'".DB::getDB()->encodeString($row['owneRead'])."',
				'".DB::getDB()->encodeString($row['owneWrite'])."',
				'".DB::getDB()->encodeString($row['owneDelete'])."'
			);");

			return [
				'error' => false,
				'done' => true,
				'aclID' => DB::getDB()->insert_id()
			];

		}

	}

}




?>