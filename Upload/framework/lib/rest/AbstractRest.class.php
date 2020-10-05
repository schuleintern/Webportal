<?php

abstract class AbstractRest {
	protected $statusCode = 200;

	/**
	 * @var user
	 */
	public $user = null;

	/**
	 * @var acl
	 */
	public $acl = null;

	
	public abstract function execute($input, $request);
	
	public abstract function getAllowedMethod();
	
	public function getStatusCode() {
	    return $this->statusCode;
	}
	
	protected function malformedRequest() {
	    $this->statusCode = 400;
	}
	
	/**
	 * Überprüft, ob ein Modul eine System Authentifizierung benötigt. (z.B. zum Abfragen aller Schülerdaten)
	 * @return boolean
	 */
	public function needsSystemAuth() {
	    return true;
	}
	
	/**
	 * Überprüft die Authentifizierung
	 * @param String $username Benutzername
	 * @param String $password Passwort
	 * @return boolean Login erfolgreich
	 */
	public function checkAuth($username, $password) {
	    return false;
	}

    /**
     * Setzt die Auth Methode auf User (Cookie)
     * @return bool
     */
	public function needsUserAuth() {
		return false;
	}

	public function aclModuleName() {
		return get_called_class();
	}

	
	/**
	 * Access Control List
	 * @return acl
	 */
	public function acl() {
		$moduleClass = $this->aclModuleName();
		$this->acl = ACL::getAcl($this->user, $moduleClass);
	}

	public function getAclByID($id, $showRight = false) {

		if ( intval($id) > 0) {
			$acl = ACL::getAcl($this->user, false, $id);
			if ($showRight) {
				return [ 'rights' => $acl['rights'], 'owne' => $acl['owne'] ];
			} else {
				return $acl;
			}
		} else {
			return $acl = ACL::getBlank();
		}
		return false;
	}

	public function getAclAll() {
		return $this->acl;
	}

	public function getAcl() {
		return [
			'rights' => $this->acl['rights'],
			'owne' => $this->acl['owne'],
			'user' => $this->acl['user']
		];
	}

	public function getAclRead() {
		return $this->acl['rights']['read'];
	}

	public function getAclWrite() {
		return $this->acl['rights']['write'];
	}
	
	public function getAclDelete() {
		return $this->acl['rights']['delete'];
	}
	


}	

?>