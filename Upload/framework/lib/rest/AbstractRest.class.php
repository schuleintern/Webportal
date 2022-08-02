<?php

abstract class AbstractRest {
	protected $statusCode = 200;

    static $adminGroupName = NULL;
    static $aclGroupName = NULL;
    static $type = false;
    static $extension = false;

	/**
	 * @var user
	 */
	public $user = null;

	/**
	 * @var acl
	 */
	public $acl = null;

    /**
     * @param false $type STRING | BOOLEAN
     */
    public function __construct($type = false) {

        $this->type = $type;

        if ( $this->type == 'extension' && PATH_EXTENSION ) {
            $path = str_replace(DS.'admin','',PATH_EXTENSION);
            $this->extension = FILE::getExtensionJSON($path.'extension.json');
            if ( isset($this->extension) ) {

                // Admin Group
                if ( $this->extension['adminGroupName'] ) {
                    self::setAdminGroup($this->extension['adminGroupName']);
                }

                // ACL Group
                if ( $this->extension['aclGroupName'] ) {
                    self::setAclGroup($this->extension['aclGroupName']);
                }
            }
        }

    }

	
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

    public function needsAdminAuth() {
        return false;
    }

    /**
     * @deprecated:  use getAclGroup
     */
	/*
	 public function aclModuleName() {
		return get_called_class();
	}
    */


    /**
     * Liest die Gruppe aus, die Zugriff auf die Administration des Moduls hat.
     * @return String Gruppenname als String
     */
    public static function getAdminGroup() {
        return self::$adminGroupName;
    }

    /**
     * Setzt die Admin Gruppe als String
     * @param String Gruppenname als String
     */
    public static function setAdminGroup($str) {
        if ($str) {
            self::$adminGroupName = $str;
        }
    }


    /**
     * Gibt den Gruppennamen für die ACL Rechte zurück
     * @return String Gruppenname als String
     */
    public static function getAclGroup() {
        if (self::$aclGroupName) {
            return self::$aclGroupName;
        }
        return get_called_class();
    }

    /**
     * Setzt die ACL Gruppe als String
     * @param String Gruppenname als String
     */
    public static function setAclGroup($str) {
        if ($str) {
            self::$aclGroupName = $str;
        }
    }




	/**
	 * Access Control List
	 * @return acl
	 */
	public function acl() {
		$this->acl = ACL::getAcl($this->user, $this->getAclGroup(), false, $this->getAdminGroup());
	}

    public function getAclByID($id = false, $showRight = false) {

        if ( $id ) {
            $acl = ACL::getAcl($this->user, false, $id, $this->getAdminGroup() );
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
		return [ 'rights' => $this->acl['rights'], 'owne' => $this->acl['owne'] ];
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