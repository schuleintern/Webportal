<?php


class Office365Meetings extends AbstractPage {
    private $tenantName = null;
    private $isActive = false;
	
	public function __construct() {
		parent::__construct(array("Office 365"));
		
		$this->checkLogin();
		
	}

	public function execute() {
	    new errorPage();
	}
	
	public static function displayAdministration($selfURL) {
	    $isOffice365Active = DB::getSettings()->getBoolean('office365-active');


        if($isOffice365Active && oAuth2Auth::ssoTeacherActive()) {
            $canActivateMeetings = true;
        }
        else $canActivateMeetings = false;

	    // Handbuch: https://schuleintern.atlassian.net/wiki/spaces/ADMINHANDBUCH/pages/3145864/Videokonferenzen+mit+Teams

        $html = "";
        eval("\$html = \"" . DB::getTPL()->get("office365/meetings/admin/index") ."\";");
        return $html;
	}
	
	public static function hasAdmin() {
	    return true;
	}
	
	public static function getAdminMenuIcon() {
	    return 'fa fas fa-video';
	}
	
	public static function getAdminMenuGroup() {
	    return "Office 365";
	}
	
	public static function getAdminMenuGroupIcon() {
	    return "fa far fa-file-word";
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getAdminGroup() {
	    return 'Webportal_Office365_Admin';
	}
	
	public static function getSettingsDescription() {
	    if(DB::getSettings()->getBoolean('office365-active')) {
	        if(oAuth2Auth::ssoTeacherActive()) {
                return [
                    [
                        'name' => 'office365-meeting-teacher',
                        'titel' => 'Office 365 Teams Meetings für Lehrer aktivieren?',
                        'text' => 'Ist diese Funktion aktiviert, können Lehrer Meetings mit externen Teilnehmern vereinbaren. (Über Nachrichten)',
                        'typ' => 'BOOLEAN'
                    ]
                ];
            }
        }

	    return [];

	}
	
	
	public static function getSiteDisplayName() {
		return 'Office 365 Meetings';
	}
		
	public static function onlyForSchool() {
        return [];
	}


    /**
     * Ist die Meeting Integration für Lehrkräfte aktiv.
     * @return bool
     */
	public static function isActiveForTeacher() {
	    return DB::getSettings()->getBoolean('office365-active') && oAuth2Auth::ssoTeacherActive() && DB::getSettings()->getBoolean("office365-meeting-teacher");
    }

    /**
     * Immer aktiv.
     * @return bool
     */
    public static function siteIsAlwaysActive() {
        return true;
    }
}


?>