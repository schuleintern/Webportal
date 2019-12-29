<?php

class MessageInbox extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Nachrichten"));
		
		$this->checkLogin();
	}
	
	public function execute() {
	
		$folder = DB::getDB()->escapeString($_REQUEST['folder']);
		$folderID = intval($_REQUEST['folderID']);
		
		$folder = MessageFolder::getFolder(DB::getSession()->getUser(), $folder, $folderID);
		
		if($folder == NULL) {
			header("Location: index.php?page=MessageInbox&folder=POSTEINGANG");
			exit(0);
		}
		
		$perPage = 20;
		$page = intval($_GET['pageNumber']);
		
		if($page > 0) {
			$offset = ($page-1) * $perPage;
		}
		else {
			$offset = 0;
			$page = 1;
		}
		
		$isSearch = false;
		
		if($_POST['searchText'] != "") {
		    $isSearch = true;
		    $searchString = $_REQUEST['searchText'];
		    
		    $messages = [];
		    // $folder->getMessagesSearch($_REQUEST['searchText']);
		}
		else {
		    $messages = $folder->getMessages($perPage, $offset);
		}
		
		
		
		$totalMessages = $folder->getMessageNumber();
		$numberPages = ceil($totalMessages / $perPage);
		
		
		if($_REQUEST['action'] == 'pageBack') {
			if($page > 1) $page--;
			header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL() . "&pageNumber=" . $page . "&folderID=" . $folder->getID());
			exit();
		}
		
		if($_REQUEST['action'] == 'pageForward') {
			if($page < ($numberPages)) $page++;
			header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL() . "&pageNumber=" . $page . "&folderID=" . $folder->getID());
			exit();
		}
		
		if($_REQUEST['action'] == 'createFolder') {
		    
		    $folderID = MessageFolder::createFolder($_POST['folderName']);
		    
		    header("Location: index.php?page=MessageInbox&folder=ANDERER&folderID=" . $folderID);
		    exit();
		}
		
		if($_REQUEST['action'] == 'deleteFolder') {
		   		    
            $myFolders = MessageFolder::getMyFolders(DB::getSession()->getUser());
            
            for($i = 0; $i < sizeof($myFolders); $i++) {
                if($myFolders[$i]->getID() == $_REQUEST['folderID']) {
                    $myFolders[$i]->delete();
                }
            }
		    
		    header("Location: index.php?page=MessageInbox&folder=POSTEINGANG");
		    exit();
		}
		
		
		$ersteNummer = $offset+1;
		
		if($numberPages == 0) $ersteNummer = 0;
			
		if($numberPages == 0) $numberPages = 1;
		
		if($page == $numberPages) {
			$letzteNummer = $totalMessages;
		}
		else {
			$letzteNummer = $page * $perPage;
		}
		
		if($_REQUEST['action'] == 'deleteSelected') {
			$deleteIDs = [];
			for($i = 0; $i < sizeof($messages); $i++) {
				if($_POST['message_' . $messages[$i]->getID()] > 0) {
					$deleteIDs[] = $messages[$i]->getID();
				}
			}
			
			$folder->deleteMessages($deleteIDs);
			header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL() . "&folderID=" . $folder->getID());
			exit(0);
		}
		
		if($_REQUEST['action'] == 'markAsRead') {
		    $markIDs = [];
		    for($i = 0; $i < sizeof($messages); $i++) {
		        if($_POST['message_' . $messages[$i]->getID()] > 0) {
		            $markIDs[] = $messages[$i]->getID();
		        }
		    }
		    
		    
		    $folder->markReadStatus($markIDs, true);
		    header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL() . "&folderID=" . $folder->getID());
		    exit(0);
		}
		
		if($_REQUEST['action'] == 'markAsUnRead') {
		    $markIDs = [];
		    for($i = 0; $i < sizeof($messages); $i++) {
		        if($_POST['message_' . $messages[$i]->getID()] > 0) {
		            $markIDs[] = $messages[$i]->getID();
		        }
		    }
		    
		    
		    
		    $folder->markReadStatus($markIDs, false);
		    header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL() . "&folderID=" . $folder->getID());
		    exit(0);
		}
		
		$folders = MessageFolder::getMyFolders(DB::getSession()->getUser());
		
		
		if($_REQUEST['action'] == 'moveSelected') {
		    
		    
		    // ACHTUNG: Nachrichten nicht in Gesendete verschieben lassen!
		    		    
		    $moveIDs = [];
		    for($i = 0; $i < sizeof($messages); $i++) {
		        if($_POST['message_' . $messages[$i]->getID()] > 0) {
		            $moveIDs[] = $messages[$i]->getID();
		        }
		    }
		    
		    $toFolder = null;
		    
		    if($_REQUEST['moveToFolderID'] == 'POSTEINGANG') {
		        $toFolder = MessageFolder::getFolder(DB::getSession()->getUser(), "POSTEINGANG", 0);
		    }
		    
		    if($_REQUEST['moveToFolderID'] == 'ARCHIV') {
		        $toFolder = MessageFolder::getFolder(DB::getSession()->getUser(), "ARCHIV", 0);
		    }
		    
		    if($toFolder == null) {
    		    for($i = 0; $i < sizeof($folders); $i++) {        // In eigenen Ordnern suchen
    		        if($_REQUEST['moveToFolderID'] == $folders[$i]->getID()) {
    		            $toFolder = $folders[$i];
    		        }
    		    }
		    }
		    		    
		    if($toFolder != null) $folder->moveMessages($moveIDs, $toFolder);
		    
		    header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL());
		    exit(0);
		}
		
		
		if($_REQUEST['action'] == 'archiveSelected') {
		    
		    $moveIDs = [];
		    for($i = 0; $i < sizeof($messages); $i++) {
		        if($_POST['message_' . $messages[$i]->getID()] > 0) {
		            $moveIDs[] = $messages[$i]->getID();
		        }
		    }
		    
		    $toFolder = MessageFolder::getFolder(DB::getSession()->getUser(), 'ARCHIV', 0);
		    
		    $folder->moveMessages($moveIDs, $toFolder);
		    
		    header("Location: index.php?page=MessageInbox&folder=" . $folder->getFolderSQL());
		    exit(0);
		}
		
		
		
		if($folder->getFolderSQL() == 'GESENDETE') {
		    $isSentFolder = true;
		}
		else $isSentFolder = false;
		
		
		$messageHTML = "";
		for($i = 0; $i < sizeof($messages); $i++) {
			$message = $messages[$i];
			
			if($isSentFolder) {
			    $recipients = [];
			    
			    /**$recipientsObjects = $message->getRecipients();
			    
			    for($r = 0; $r < sizeof($recipientsObjects); $r++) {
			        $recipients[] = $recipientsObjects[$r]->getDisplayName();
			    } **/

			    $recipientsPreview = $messages[$i]->getRecipientsPreview();
			}

			eval("\$messageHTML .= \"" . DB::getTPL()->get("messages/inbox/message") . "\";");
		}
		
		// Ordner Status
		
		$posteingangOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "POSTEINGANG", 0);
		$gesendetOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "GESENDET", 0);
		$papierkorbOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "PAPIERKORB", 0);
		$archivOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "ARCHIV", 0);
				
		// Eigene Order
		
		
		$ownFolders = "";
		
		$selectFolders = "<option value=\"POSTEINGANG\">Posteingang</option>";
		$selectFolders .= "<option value=\"ARCHIV\">Archiv</option>";
		
		for($i = 0; $i < sizeof($folders); $i++) {
		    if($_REQUEST['folderID'] == $folders[$i]->getID() && $_REQUEST['folder'] == 'ANDERER') $ownFolders .= '<li class="active">';
		    else $ownFolders .= '<li>';
		    
		    $ownFolders .= '
                <a href="index.php?page=MessageInbox&folder=ANDERER&folderID=' . $folders[$i]->getID() . '">

                    <i class="fa fa-folder"></i> ' . $folders[$i]->getName() . '                 


                    <span class="label label-primary pull-right">' . $folders[$i]->getUnreadMessageNumber() . '</span>
                </a></li>';
		    
		    $selectFolders .= "<option value=\"" . $folders[$i]->getID() . "\">" . $folders[$i]->getName() . "</option>";
		}
		
		//
		
		eval("\$FRAMECONTENT = \"" . DB::getTPL()->get("messages/inbox/index") . "\";");
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("messages/inbox/frame") . "\");");
		exit(0);
	}
	
	public static function getSettingsDescription() {
		$settings = [];
		
		
		return $settings;
	}
	
	public static function getSiteDisplayName() {
		return "Nachrichten - Posteingang";
	}
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
		
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
	
	public static function hasAdmin() {
		return false;
	}
	
	public static function getAdminGroup() {
		return "NONE";
	}
	
	public static function displayAdministration($selfURL) {
		
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
}


?>