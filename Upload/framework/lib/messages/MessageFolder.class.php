<?php


class MessageFolder {
	
	private $isStandardFolder = false;
	
	private $folder = '';
	
	private $folderID;
	private $folderName;
	private $user;
	
	public function __construct($isStandardFolder, $data, $user, $folder) {
		if($isStandardFolder) {
			$this->isStandardFolder = $isStandardFolder;
			$this->folderID = 0;
			$this->folderName = $data['folderName'];
			$this->user = $user;
		}
		else {
			$this->folderID = $data['folderID'];
			$this->folderName = $data['folderName'];
			$this->user = $user;
		}
		
		$this->folder = $folder;
	}
	
	public function getName() {
		return $this->folderName;
	}
	
	public function getID() {
		return $this->folderID;
	}
	
	
	/**
	 * 
	 * @return NULL|user
	 */
	public function getUser() {
		return $this->user;
	}
	
	public function getFolderSQL() {
		return $this->folder;
	}
	
	/**
	 * 
	 * @return Message[] Nachrichten im Verzeichnis
	 */
	public function getMessages($limit, $offset) {
		return Message::getMessages($this->user, $this->folder, $this->folderID, $limit, $offset);
	}
	
	public function getMessagesSearch($search, $limit, $offset) {
	    return Message::getMessagesSearch($this->user, $this->folder, $this->folderID, $search, $limit, $offset);
	    
	}
		
	/**
	 * Anzahl der Nachrichten im Ordner (total)
	 * @return number
	 */
	public function getMessageNumber() {
		return Message::getMessageNumber($this->user, $this->folder, $this->folderID);
	}
	
	/**
	 * Anzahl der ungelesenden Nachrichten
	 * @return number
	 */
	public function getUnreadMessageNumber() {
		return Message::getUnreadMessageNumber($this->user, $this->folder, $this->folderID);
	}
	
	/**
	 * TODO: Nachrichten mit Lesebestätigung nicht löschen lassen.
	 * @param int $messageIDs
	 */
	public function deleteMessages($messageIDs) {
		if(sizeof($messageIDs) > 0) {
			if($this->folder == 'PAPIERKORB') {
			    DB::getDB()->query("UPDATE messages_messages SET messageIsDeleted=1 WHERE messageID IN('" . implode("','",$messageIDs) . "')");
			}
			else {
				DB::getDB()->query("UPDATE messages_messages SET messageFolder='PAPIERKORB' WHERE messageID IN('" . implode("','",$messageIDs) . "')");
			}
		}
	}
	
	
	public function markReadStatus($messageIDs, $status) {
	    
	    if($this->folder == 'GESENDETE') return;
    
	    if(sizeof($messageIDs) > 0) {
	       DB::getDB()->query("UPDATE messages_messages SET messageisRead='$status' WHERE messageID IN('" . implode("','",$messageIDs) . "')");
	    }
	}
	
	/**
	 * 
	 * @param int[] $messageIDs
	 * @param MessageFolder $folder
	 */
	public function moveMessages($messageIDs, $folder) {
	    if($this->folder == 'GESENDETE') return;
	    DB::getDB()->query("UPDATE messages_messages SET messageFolder='" . $folder->getFolderSQL() . "', messageFolderID='" . $folder->getID() . "' WHERE messageID IN('" . implode("','",$messageIDs) . "')");
	    
	}
	
	public function delete() {
	    DB::getDB()->query("UPDATE messages_messages SET messageFolder='PAPIERKORB', messageFolderID=0 WHERE messageFolder='ANDERER' AND messageFolderID='" . $this->getID() . "'");
	   DB::getDB()->query("DELETE FROM messages_folders WHERE folderID='" . $this->getID() . "'");
	}
	
	
	public function isOwnFolder() {
	    return $this->folder == 'ANDERER';
	}
	
	/**
	 * 
	 * @param user $user
	 * @return MessageFolder[]
	 */
	public static function getMyFolders($user) {
	    $fData = DB::getDB()->query("SELECT * FROM messages_folders WHERE folderUserID='" . $user->getUserID() . "'");
	    
	    $folders = [];
	    
	    while($f = DB::getDB()->fetch_array($fData)) {
	        $folders[] = new MessageFolder(false, $f, $user, 'ANDERER');
	    }
	    
	    return $folders;
	}
	
	public static function createFolder($name) {
	    DB::getDB()->query("INSERT INTO messages_folders (folderName, folderUserID) values('" . DB::getDB()->escapeString($name) . "','" . DB::getUserID() . "')");
	
	    return DB::getDB()->insert_id();
	
	}
	
	/**
	 * 
	 * @param user $user
	 * @param String $folder
	 * @param int $folderID
	 * @return NULL|MessageFolder
	 */
	public static function getFolder($user, $folder, $folderID) {
		if(!in_array($folder,['POSTEINGANG','GESENDETE','PAPIERKORB','ARCHIV','ANDERER'])) {
			return NULL;
		}
		
		if($folder == 'ANDERER') {
			$fData = DB::getDB()->query_first("SELECT * FROM messages_folders WHERE folderID='" . intval($folderID) . "' AND folderUserID='" . $user->getUserID() . "'");
			if($fData['folderID'] > 0) {
				return new MessageFolder(false, $fData, $user, $folder);
			}
			else return NULL;
		}
		
		if($folder == 'POSTEINGANG') {
			return new MessageFolder(true, ['folderName' => 'Posteingang'], $user, $folder);
		}
		
		if($folder == 'GESENDETE') {
			return new MessageFolder(true, ['folderName' => 'Gesendete'], $user, $folder);
		}
		
		if($folder == 'PAPIERKORB') {
			return new MessageFolder(true, ['folderName' => 'Papierkorb'], $user, $folder);
		}
		
		if($folder == 'ARCHIV') {
		    return new MessageFolder(true, ['folderName' => 'Archiv'], $user, $folder);
		}
		
		return NULL;
	
	}
	
	
	
}

