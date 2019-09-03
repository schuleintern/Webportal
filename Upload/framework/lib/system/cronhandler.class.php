<?php

/**
 * Verwaltet die Crons.
 * @author Christian Spitschka
 * @version 1.0 Beta
 */
class cronhandler {
	private static $allowedActions = [
			"CreateElternUsers",
			"SyncUsers",
			'ElternMailSenderCron',
			'MailSender',
			'ElternMailReceiver',
			'CreateDemoVplan',
			'UpdateExterneKalender',
	        'CreateOffice365Users',
	       'TagebuchFehlSucher',
	    'FerienDownloader',
	    'CronGarbageCollector',
	    'CronNextCloud',
	    'CronVerspaetungAuswertung',
	    'CreateTagebuchPDFs'
	];
	
	
	public function __construct() {
		header("Content-type: text/plain");
		
		if(DB::getGlobalSettings()->cronkey != $_REQUEST['cronkey']) {
			echo("Invalid Cron Key!");
			exit(0);
		}
		
		//Check Access Key
		
		if($_REQUEST['cronName'] != "") {
		    // Execute Single Cron
		    $cronName = $_REQUEST['cronName'];
		    
		    $result = [];
		    
		    if(in_array($cronName, self::$allowedActions)) {
		        /**
		         * 
		         * @var AbstractCron $cron
		         */
		        $cron = new $cronName();
		        if($cron->onlyExecuteSeparate()) {
		            
		            
		            
		            $startTime = time();
		            $cron->execute();
		            $endTime = time();
		            
		            $result = $cron->getCronResult();
		            
		            DB::getDB()->query("INSERT INTO cron_execution (
    						cronName,
    						cronStartTime,
    						cronEndTime,
    						cronSuccess,
    						cronResult)
    						values(
    							'" . $cronName . "',
    							'" . $startTime . "',
    							'" . $endTime . "',
    							'" . ($result['success'] ? 1 : 0). "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");}
		        
		        
		        
		    }
		    
		    echo("done.");
		    print_R($result);
		    exit(0);
		    
		    
		}


		// Cron Running?
		
		$isRunning = DB::getSettings()->getValue("cronRunning");
		
		if($isRunning != 0) {
		    if(($isRunning + 1800) <= time()) {
		        // Nach 30 Minuten Reset
		        DB::getSettings()->setValue("cronRunning", time());
		    }
		    else {
		        echo("Cron is running. Exit.");
		        exit(0);
		    }
		}
		
		DB::getSettings()->setValue("cronRunning", time());
		
		
		//
		
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			require_once('../framework/lib/cron/'.self::$allowedActions[$i].".class.php");
		}
		
		// Remove Slashes in pages
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			if(strpos(self::$allowedActions[$i],"/") > 0) self::$allowedActions[$i] = substr(self::$allowedActions[$i], strpos(self::$allowedActions[$i],"/")+1);
		}
		
		
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			/**
			 * 
			 * @var AbstractCron $cron
			 */
			$cron = new self::$allowedActions[$i]();
			
			if(!$cron->onlyExecuteSeparate()) {
			
    			$lastExecution = DB::getDB()->query_first("SELECT * FROM cron_execution WHERE cronName='" . self::$allowedActions[$i] . "' ORDER BY cronStartTime DESC LIMIT 1");
    			
    			if(sizeof($lastExecution) == 0) {
    				$lastExecution = time() - 1 - $cron->executeEveryXSeconds();	// First Run
    			}
    			else $lastExecution = $lastExecution['cronStartTime'];
    				
    			
    			$execute = false;
    			
    			if(($lastExecution + $cron->executeEveryXSeconds()) < time()) {
    				$execute = true;
    			}
    			
    			if($execute) {
    				$startTime = time();
    				$cron->execute();
    				$endTime = time();
    				
    				$result = $cron->getCronResult();
    				
    				DB::getDB()->query("INSERT INTO cron_execution (
    						cronName,
    						cronStartTime,
    						cronEndTime,
    						cronSuccess,
    						cronResult) 
    						values(
    							'" . self::$allowedActions[$i] . "',
    							'" . $startTime . "',
    							'" . $endTime . "',
    							'" . ($result['success'] ? 1 : 0). "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");
    			}
    			else {
    				echo("skip " . self::$allowedActions[$i]. "\r\n");
    			}
			}
		}
		
		// Cron zu Ende
		DB::getSettings()->setValue("cronRunning", 0);
		exit(0);
		
	}
	
	public static function getAllowedActions() {
		return self::$allowedActions;
	}
}

?>