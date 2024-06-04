<?php

abstract class AbstractApp {

    public $isMobile = true;

    static $adminGroupName = NULL;
    static $aclGroupName = NULL;

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

        //$this->user = DB::getSession()->getUser();
        //$this->acl();

    }

	
	public abstract function execute();
	

    


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

        //echo ' - getAclGroup:'.self::$aclGroupName;

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

        //echo ' - setAclGroup:'.$str;

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



    protected function canRead () {

        if ( DB::getSession() && DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) === true ) {
            return true;
        }
        if ( (int)$this->acl['rights']['read'] === 1  ) {
            return true;
        }
        return false;
    }

    protected function canWrite () {
        if ( DB::getSession() && DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) === true ) {
            return true;
        }
        if ( (int)$this->acl['rights']['write'] === 1  ) {
            return true;
        }
        return false;
    }

    protected function canDelete () {
        if ( DB::getSession() && DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) === true ) {
            return true;
        }
        if ( (int)$this->acl['rights']['delete'] === 1  ) {
            return true;
        }
        return false;
    }

    protected function canAdmin () {
        //echo $this->getAdminGroup();

        if ( DB::getSession() && DB::getSession()->isAdminOrGroupAdmin($this->getAdminGroup()) === true ) {
            return true;
        }
        return false;
    }


}	

?>