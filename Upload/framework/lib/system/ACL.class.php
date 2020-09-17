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
		// $acl['rights']['write'] = 1;
    // $acl['rights']['delete'] = 0;
    //print_r($acl);
    return $acl;

  }


}




?>