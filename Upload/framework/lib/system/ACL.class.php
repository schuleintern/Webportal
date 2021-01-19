<?php

/**
 * Globale Access Control List class
 * 
 * @author: Christian Marienfeld
 */

class ACL {


  public function getAcl($user, $selector = false, $adminGroup = false) {

    //return 'ACL';
    // $userID = DB::getSession()->getUser();
		// $moduleClass = get_called_class();

		$userID = $user->getUserID();

		if (!$userID) {
			return false;
		}

		$acl = [];

		$acl['user']['admin'] = $user->isAdmin();
		$acl['user']['schueler'] = $user->isPupil();
		$acl['user']['lehrer'] = $user->isTeacher();
		$acl['user']['eltern'] = $user->isEltern();
		$acl['user']['sekretariat'] = $user->isSekretariat();

		$acl['rights'] = [
			'read' => 0,
			'write' => 0,
			'delete' => 0
		];

		$acl = array_merge( $acl, self::getBlank() );

		if ( intval($selector) > 1 ) {
			$aclDB = DB::getDB ()->query_first ( "SELECT * FROM acl WHERE id = ".intval($selector)." ");
			
			if ( $adminGroup && DB::getSession()->isMember($adminGroup) ) {
				$acl['user']['admin'] = true;
			}

		} else {


			$aclDB = DB::getDB ()->query_first ( "SELECT * FROM acl WHERE moduleClass = '".$selector."' ");
			if ($aclDB && $aclDB['id'] ) {
				if ( DB::getSession()->isMember($selector::getAdminGroup()) ) {
					$acl['user']['admin'] = true;
				}
			}
		}

		

		if (!$aclDB || !$aclDB['id'] ) {
			return $acl;
		}

		$acl['aclID'] = $aclDB['id'];
		$acl['aclModuleClass'] = $aclDB['moduleClass'];

		$acl['groups'] = [
			'schueler' => ['read' => intval($aclDB['schuelerRead']), 'write' => intval($aclDB['schuelerWrite']), 'delete' => intval($aclDB['schuelerDelete']) ],
			'eltern' => [ 'read' => intval($aclDB['elternRead']), 'write' => intval($aclDB['elternWrite']), 'delete' => intval($aclDB['elternDelete']) ],
			'lehrer' => [ 'read' => intval($aclDB['lehrerRead']), 'write' => intval($aclDB['lehrerWrite']), 'delete' => intval($aclDB['lehrerDelete']) ],
			'none' => [ 'read' => intval($aclDB['noneRead']), 'write' => intval($aclDB['noneWrite']), 'delete' => intval($aclDB['noneDelete']) ],
			'owne' => [ 'read' => intval($aclDB['owneRead']), 'write' => intval($aclDB['owneWrite']), 'delete' => intval($aclDB['owneDelete']) ]
		];

		
		if (!$acl['user']['schueler'] && !$acl['user']['lehrer'] && !$acl['user']['eltern']) {
			$acl['user']['none'] = 1;
		}
		

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
		//  $acl['rights']['write'] = 0;
    //  $acl['rights']['delete'] = 0;
    //print_r($acl);
    return $acl;

  }


	public function setAcl( $row, $module = '' ) {

		// return false;

		// if ( !isset($row['id']) ) {
		// 	return false;
		// }
		
		// echo "<pre>";
    //       print_r($row);
		// 			echo "</pre>";
					
		$row = json_decode(json_encode($row), true);

		// echo "<pre>ACL:";
		// print_r($row['groups']['schueler']['delete']);
		// echo "</pre>";

		// exit;
		
		if ( $row['aclModuleClass'] != $module ) {
			$row['aclModuleClass'] = $module;
		}
		
		// echo "<pre>ACL:";
		// print_r($row);
		// echo "</pre>";

		if ( $row['aclID'] ) {

			// if ($module) {
			// 	$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['aclID']) . " AND moduleClass = '".$module."'");
			// } else {
			// 	$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['aclID']));
			// }

			$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['aclID']));


			if ( $dbRow['id'] ) {
				
				//echo $row['schuelerWrite'];

				DB::getDB()->query("UPDATE acl SET
					schuelerRead = ".intval($row['groups']['schueler']['read']).",
					schuelerWrite = ".intval($row['groups']['schueler']['write']).",
					schuelerDelete = ".intval($row['groups']['schueler']['delete']).",
					elternRead = ".intval($row['groups']['eltern']['read']).",
					elternWrite = ".intval($row['groups']['eltern']['write']).",
					elternDelete = ".intval($row['groups']['eltern']['delete']).",
					lehrerRead = ".intval($row['groups']['lehrer']['read']).",
					lehrerWrite = ".intval($row['groups']['lehrer']['write']).",
					lehrerDelete = ".intval($row['groups']['lehrer']['delete']).",
					noneRead = ".intval($row['groups']['none']['read']).",
					noneWrite = ".intval($row['groups']['none']['write']).",
					noneDelete = ".intval($row['groups']['none']['delete']).",
					owneRead = ".intval($row['groups']['owne']['read']).",
					owneWrite = ".intval($row['groups']['owne']['write']).",
					owneDelete = ".intval($row['groups']['owne']['delete'])."
					WHERE id = " . intval($row['aclID']) . ";");
				
				return [
					'error' => false,
					'success' => true,
					'msg' => 'Erfolgreich Gespeichert',
					'aclID' => intval($dbRow['id'])
				];

			} else {
				return [
					'error' => true,
					'msg' => 'Fehlende ACL Eintrag'
				];
			}

								
		} else if ( $row['aclModuleClass'] || $row['aclModuleClassParent'] ) {

			// if (!$module) {
			// 	$module = '';
			// }

			if ( $row['aclModuleClass'] ) {

				$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE  moduleClass = '".$row['aclModuleClass']."'");
			
				if ( $dbRow['id'] ) {
					return [
						'error' => true,
						'msg' => 'ACL Eintrag für das Modul bereits vorhanden!'
					];
				}
				
			}
			

			DB::getDB()->query("INSERT INTO acl (
				moduleClass,
				moduleClassParent,
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
				'".DB::getDB()->encodeString($row['aclModuleClass'])."',
				'".DB::getDB()->encodeString($row['aclModuleClassParent'])."',
				'".intval($row['groups']['schueler']['read'])."',
				'".intval($row['groups']['schueler']['write'])."',
				'".intval($row['groups']['schueler']['delete'])."',
				'".intval($row['groups']['eltern']['read'])."',
				'".intval($row['groups']['eltern']['write'])."',
				'".intval($row['groups']['eltern']['delete'])."',
				'".intval($row['groups']['lehrer']['read'])."',
				'".intval($row['groups']['lehrer']['write'])."',
				'".intval($row['groups']['lehrer']['delete'])."',
				'".intval($row['groups']['none']['read'])."',
				'".intval($row['groups']['none']['write'])."',
				'".intval($row['groups']['none']['delete'])."',
				'".intval($row['groups']['owne']['read'])."',
				'".intval($row['groups']['owne']['write'])."',
				'".intval($row['groups']['owne']['delete'])."'
			);");

			return [
				'error' => false,
				'success' => true,
				'msg' => 'Erfolgreich Gespeichert',
				'aclID' => DB::getDB()->insert_id()
			];

		}

		return [];
	}

	public function getBlank() {

		$blank = [
			'aclID' => 0,
			'aclModuleClass' => '',
			'aclModuleClassParent' => '',
			'groups' => [
				'schueler' => ['read' => 0, 'write' => 0, 'delete' => 0 ],
				'eltern' => [ 'read' => 0, 'write' => 0, 'delete' => 0 ],
				'lehrer' => [ 'read' => 0, 'write' => 0, 'delete' => 0 ],
				'none' => [ 'read' => 0, 'write' => 0, 'delete' => 0 ],
				'owne' => [ 'read' => 0, 'write' => 0, 'delete' => 0 ]
			],
			'rights' => [
				'read' => 0,
				'write' => 0,
				'delete' => 0
			]
		];

		return $blank;

	}

}




?>