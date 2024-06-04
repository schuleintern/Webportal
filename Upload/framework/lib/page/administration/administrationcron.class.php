<?php


class administrationcron extends AbstractPage {

	private $info;

	public function __construct() {
		parent::__construct(array("Administration", "Crons"), false, true);

		$this->checkLogin();


		new errorPage();
	}

	public function execute() {}

	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Fï¿½r die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();

	}

	public static function hasSettings() {
		return false;
	}

	public static function getSiteDisplayName() {
		return "Automatische Aufgaben";
	}

	public static function getSettingsDescription() {

	}

	public static function siteIsAlwaysActive() {
		return true;
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function displayAdministration($selfURL) {

	    // Cron URLS

        $url1 = str_replace("index.php", "cron.php", DB::getGlobalSettings()->urlToIndexPHP)
                . "?cronkey=" . DB::getGlobalSettings()->cronkey;
        $url2 = str_replace("index.php", "cron.php", DB::getGlobalSettings()->urlToIndexPHP)
            . "?cronkey=" . DB::getGlobalSettings()->cronkey . "&cronName=MailSender";

        $hasUserSync = false;

        if(DB::getGlobalSettings()->lehrerUserMode == "SYNC" || DB::getGlobalSettings()->schuelerUserMode == "SYNC") {
            $hasUserSync = true;
        }
        // UserSync Cron
        $url3 = str_replace("index.php", "cron.php", DB::getGlobalSettings()->urlToIndexPHP)
            . "?cronkey=" . DB::getGlobalSettings()->cronkey . "&cronName=SyncUsers";



        /** @var String[] $allCrons */
        $allCrons = cronhandler::getAllowedActions();


        if($_REQUEST['executeCron'] != "") {

            $result = [
                'CronName' => $_REQUEST['executeCron'],
                'ValidCrons' => $allCrons,
                'Result' => []
            ];

            for($i = 0; $i < sizeof($allCrons); $i++) {

                $cron = new $allCrons[$i]();

                if($allCrons[$i] == $_REQUEST['executeCron']) {
                    /** @var AbstractCron $cron */
                    $cron->execute();

                    $result['Result'] = $cron->getCronResult();
                }
            }

            header("Content-type: application/json");
            echo(json_encode($result));
            exit(0);
        }

		$htmlList = "";
		$cronHTMLList = "";
		
		for($i = 0; $i < sizeof($allCrons); $i++) {
			
			/**
			 * 
			 * @var AbstractCron $cronObject
			 */
			$cronObject = new $allCrons[$i]();
			
// 			print_r($cronObject);
			
			if(is_object($cronObject)) {
			
				$htmlList .= "<li" . (($i == 0) ? (" class=\"active\"") : ("")) . "><a href=\"#" . $allCrons[$i] . "\" data-toggle=\"tab\"><i class=\"fa fa-clock-o\"></i> " . $cronObject->getName() . "</a></li>";	        
	
				$executions = DB::getDB()->query("SELECT * FROM cron_execution WHERE cronName='" . $allCrons[$i] . "' ORDER BY cronStartTime DESC LIMIT 20");
				
				$executionHTML = "";
				while($e = DB::getDB()->fetch_array($executions)) {
					$executionHTML .= "<tr><td>" . functions::makeDateFromTimestamp($e['cronStartTime']) . "</td>";
					$executionHTML .= "<td>" . functions::makeDateFromTimestamp($e['cronEndTime']) . "</td>";
					$executionHTML .= "<td>" . (($e['cronSuccess'] > 0) ? "Ja" : "Nein") . "</td>";
					$executionHTML .= "<td><pre>" . $e['cronResult'] . "</pre></td></tr>";
				}
				
				$executeEveryMinutes = $cronObject->executeEveryXSeconds() / 60;
				
				eval("\$cronHTMLList .= \"" . DB::getTPL()->get("administration/cron/crontab") . "\";");
			}
			
		}
// 		die();
		
		$html = "";
		
		eval("\$html .= \"" . DB::getTPL()->get("administration/cron/index") . "\";");
		
		return $html;
	}
	
	
	public static function getAdminGroup() {
		return 'Webportal_Admin_Cronjobs';
	}
	
	public static function getAdminMenuGroup() {
		return 'System';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-clock';
	}
}


?>