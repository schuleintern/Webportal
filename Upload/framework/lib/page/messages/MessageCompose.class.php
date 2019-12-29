<?php

class MessageCompose extends AbstractPage {
	
	private $recipientHandler = NULL;
	
	public function __construct() {
		parent::__construct(array("Nachrichten"));
		
		$this->checkLogin();
	}
	
	public function execute() {
		
		
		MessageSendRights::init();
			
		switch($_REQUEST['action']) {
		    case 'getRecipientsList':
		        $recipient = RecipientHandler::getRecipientFromSaveString($_REQUEST['saveString']);
		        
		        $result = [
		            'recipientList' => ''
		        ];
		        
		        $recipientList = [];
		        
		        if($recipient != null) {
		            $users = $recipient->getRecipientUserIDs();
		            
		            for($i = 0; $i < sizeof($users); $i++) {
		                $user = user::getUserByID($users[$i]);
		                if($user != null) {
		                    $recipientList[] = $user->getDisplayNameWithFunction();
		                }
		            }
		        }
		        
		        $result['recipientList'] = implode("<br />\r\n",$recipientList);
		        
		        
		        header("Content-type: text/json");
		        echo json_encode($result);
		        exit(0);
		    break;
		        
		    
		    case 'getTeachersJSON':
		        $teacher = lehrer::getAll();
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        $singleTeachers = MessageSendRights::getAllowedTeachers();
		        
		        // Debugger::debugObject($singleTeachers,1);
		        
		        if($_REQUEST['term'] != "") {
		            $search = strtolower($_REQUEST['term']);
		        }
		        else {
		            $search = null;
		        }
		        
		        $selectOptionsSingleTeacher = "";
		        if(sizeof($singleTeachers) > 0) {
		            $canContactAnyTeacher = true;
		            for($i = 0; $i < sizeof($singleTeachers); $i++) {
		                
		                $ok = false;
		                
		                if($search != null && strpos(strtolower($singleTeachers[$i]->getDisplayName()), $search) > 0) {
		                    $ok = true;
		                }
		                
		                if($search == null) $ok = true;
		                
		                if($ok) {		                
    		                $responseData['results'][] = [
    		                    'id' => $singleTeachers[$i]->getSaveString(),
    		                    'text' => $singleTeachers[$i]->getDisplayName()
    		                ];
    		                
		                }
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);
		        
		     break;
		     
		    case 'getPupilJSON':
		        $pupilRecipients = MessageSendRights::getAllowedPupils();
		        
	        
		        $responseData = [
		            'results' => []
		        ];
		        
		        
		        if($_REQUEST['term'] != "") {
		            $search = strtolower($_REQUEST['term']);
		        }
		        else {
		            $search = null;
		        }
		        
		        
		        
		        
		        for($i = 0; $i < sizeof($pupilRecipients); $i++) {
		            if($search != null && strpos(strtolower($pupilRecipients[$i]->getDisplayName()), $search) > 0) {
    		            $responseData['results'][] = [
    		                'id' => $pupilRecipients[$i]->getSaveString(),
    		                'text' => $pupilRecipients[$i]->getDisplayName()
    		                
    		            ];
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);
		    break;
		    
		    
		    case  'getParentsJSON':
		        
		        $parentsRecipients = MessageSendRights::getAllowedParents();
		        
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        if($_REQUEST['term'] != "") {
		            $search = strtolower($_REQUEST['term']);
		        }
		        else {
		            $search = null;
		        }
		        
		        
		        for($i = 0; $i < sizeof($parentsRecipients); $i++) {
		            if($search != null && strpos(strtolower($parentsRecipients[$i]->getDisplayName()), $search) > 0) {
		                
    		            $responseData['results'][] = [
    		                'id' => $parentsRecipients[$i]->getSaveString(),
    		                'text' => $parentsRecipients[$i]->getDisplayName(),
    		                'disabled' =>  !$parentsRecipients[$i]->isAvailible()
    		            ];
		            
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);
		        break;
		        
		        
		        
		    case  'getSchuelerOwnUnterrichtJSON':
		        
		        /**
		         * 
		         * @var SchuelerUnterricht[] $unterrichte
		         */
		        $unterrichte = [];
		        
		        if(MessageSendRights::isOwnUnterrichtAllowed()) {
		            
		        
    		        if(DB::getSession()->isTeacher()) {
    		            $unterrichte = SchuelerUnterricht::getUnterrichtForLehrer(DB::getSession()->getTeacherObject());
    		        }
    		        
    		        if(DB::getSession()->isPupil()) {
    		            $unterrichte = SchuelerUnterricht::getUnterrichtForSchueler(DB::getSession()->getPupilObject());
    		        }
    		        
    		        if(DB::getSession()->isEltern()) {
    		            
    		            $schueler = DB::getSession()->getElternObject()->getMySchueler();
    		            
    		            for($s = 0; $s < sizeof($schueler); $s++) {
    		                $unterrichts = SchuelerUnterricht::getUnterrichtForSchueler($schueler[$i]);
    		                
    		                $unterrichte = array_merge($unterrichte, $unterrichts);
    		            }
    		            
    		        }
		        
		        }
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        if($_REQUEST['term'] != "") {
		            $search = strtolower($_REQUEST['term']);
		        }
		        else {
		            $search = null;
		        }
		        
		        
		        for($i = 0; $i < sizeof($unterrichte); $i++) {
		            
		            if($search == "%" || ($search != null && strpos(strtolower($unterrichte[$i]->getBezeichnung()), $search) > 0)) {
		                
		                $recipient = new PupilsOfClassRecipient($unterrichte[$i]);
		                
		                $responseData['results'][] = [
		                    'id' => $recipient->getSaveString(),
		                    'text' => $recipient->getDisplayName(),
		                    'disabled' =>  false
		                ];
		                
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);
		        break;

		    case  'getElternOwnUnterrichtJSON':
		        
		        /**
		         *
		         * @var SchuelerUnterricht[] $unterrichte
		         */
		        $unterrichte = [];
		        
		        if(MessageSendRights::isOwnUnterrichtAllowed()) {
		        
    		        if(DB::getSession()->isTeacher()) {
    		            $unterrichte = SchuelerUnterricht::getUnterrichtForLehrer(DB::getSession()->getTeacherObject());
    		        }
    		        
    		        if(DB::getSession()->isPupil()) {
    		            $unterrichte = SchuelerUnterricht::getUnterrichtForSchueler(DB::getSession()->getPupilObject());
    		        }
    		        
    		        if(DB::getSession()->isEltern()) {
    		            
    		            $schueler = DB::getSession()->getElternObject()->getMySchueler();
    		            
    		            for($s = 0; $s < sizeof($schueler); $s++) {
    		                $unterrichts = SchuelerUnterricht::getUnterrichtForSchueler($schueler[$i]);
    		                
    		                $unterrichte = array_merge($unterrichte, $unterrichts);
    		            }
    		            
    		        }
		        
		        }
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        if($_REQUEST['term'] != "") {
		            $search = strtolower($_REQUEST['term']);
		        }
		        else {
		            $search = null;
		        }
		        
		        
		        for($i = 0; $i < sizeof($unterrichte); $i++) {
		            if($search == "%" || ($search != null && strpos(strtolower($unterrichte[$i]->getBezeichnung()), $search) > 0)) {
		                
		                $recipient = new ParentsOfPupilsOfClassRecipient($unterrichte[$i]);
		                
		                $responseData['results'][] = [
		                    'id' => $recipient->getSaveString(),
		                    'text' => $recipient->getDisplayName(),
		                    'disabled' =>  false
		                ];
		                
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);
		        break;
		        
		        
		    case 'getSchuelerAllUnterrichtJSON':
		    
		        /**
		         *
		         * @var SchuelerUnterricht[] $unterrichte
		         */
		        $unterrichte = [];
		        
		        if(MessageSendRights::isAllUnterrichtAllowed()) {
		            if(trim($_REQUEST['term']) != "")
		                
		                  $unterrichte = SchuelerUnterricht::searchInBezeichnung($_REQUEST['term']);
		            
		        }
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        
		        for($i = 0; $i < sizeof($unterrichte); $i++) {
		                
		                $recipient = new PupilsOfClassRecipient($unterrichte[$i]);
		                
		                $responseData['results'][] = [
		                    'id' => $recipient->getSaveString(),
		                    'text' => $recipient->getDisplayName(),
		                    'disabled' =>  false
		                ];
		                
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);		        
		    break;
		    
		    case 'getElternAllUnterrichtJSON':
		        
		        /**
		         *
		         * @var SchuelerUnterricht[] $unterrichte
		         */
		        $unterrichte = [];
		        
		        if(MessageSendRights::isAllUnterrichtAllowed()) {
		            if(trim($_REQUEST['term']) != "")
		                
		                $unterrichte = SchuelerUnterricht::searchInBezeichnung($_REQUEST['term']);
		                
		        }
		        
		        $responseData = [
		            'results' => []
		        ];
		        
		        
		        for($i = 0; $i < sizeof($unterrichte); $i++) {
		            
		            $recipient = new ParentsOfPupilsOfClassRecipient($unterrichte[$i]);
		            
		            $responseData['results'][] = [
		                'id' => $recipient->getSaveString(),
		                'text' => $recipient->getDisplayName(),
		                'disabled' =>  false
		            ];
		            
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($responseData);
		        exit(0);		 
		        break;
		        
		    case  'uploadAttachment':

		        $upload = FileUpload::uploadOfficePdfOrPicture('attachmentFile','');
		        
		        $result = [
		            'uploadOK' => false,
		            'attachmentID' => 0,
		            'attachmentAccessCode' => '',
		            'attachmentFileName' => ''
		        ];
		        
		        if($upload['result']) {
		            $uploadObject = $upload['uploadobject'];
		           
		            $result['uploadOK'] = true;
		            
		            $attachment = MessageAttachment::addAttachmentAndGetObject($uploadObject);
		            $result['attachmentID'] = $attachment->getID();
		            $result['attachmentAccessCode'] = $attachment->getAccessCode();
		            $result['attachmentURL'] = $attachment->getUpload()->getURLToFile(true);
		            $result['attachmentFileName'] = $attachment->getUpload()->getFileName();
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($result);
		        exit(0);
		        
		    break;
		    
		    case 'addQuestion':
		        $result = [
		          'questionOK' => false,
		          'questionID' => 0,
		          'questionSecret' => '',
		          'questionText' => '',
		          'questionType' => ''
		        ];
		        
		        if(MessageSendRights::canAskQuestions()) {
		          
		            $newType = null;
		            
		            switch($_REQUEST['questionType']) {
		                case 'TEXT': $newType = 'TEXT'; break;
		                case 'BOOLEAN': $newType = 'BOOLEAN'; break;
		                case 'NUMBER': $newType = 'NUMBER'; break;
		                case 'FILE': $newType = 'FILE'; break;
		            }
		            
		            if($newType != null) {
		                $newQuestion = MessageQuestion::createQuestion($_REQUEST['questionText'], $newType);
		                
		                if($newQuestion != null) {
		                    $result['questionOK'] = true;
		                    $result['questionID'] = $newQuestion->getID();
		                    $result['questionSecret'] = $newQuestion->getSecret();
		                    $result['questionText'] = $newQuestion->getQuestionText();
		                    $result['questionType'] = $newQuestion->getQuestionTypeAsText();
		                }
		            }
		        }
		        
		        header("Content-type: text/json");
		        echo json_encode($result);
		        exit(0);
		    break;
	    
			case 'send':
				$messageSender = new MessageSender();
				

								
				$recipientHandler = new RecipientHandler($_REQUEST['recipients']);
				$recipientHandlerCC = new RecipientHandler($_REQUEST['ccrecipients']);
				$recipientHandlerBCC = new RecipientHandler($_REQUEST['bccrecipients']);
								
				
				$messageSender->setSender(DB::getSession()->getUser());
				
				$messageSender->setSubject($_POST['messageSubject']);
				$messageSender->setText($_POST['messageText']);
				$messageSender->setPriority($_POST['priority']);
				
				$messageSender->setRecipients($recipientHandler);				
				$messageSender->setCCRecipients($recipientHandlerCC);
				$messageSender->setBCCRecipients($recipientHandlerBCC);
				
				// Debugger::debugObject($messageSender,1);
								
				$attachments = explode(";",$_REQUEST['attachments']);
				
				
				
				for($i = 0; $i < sizeof($attachments); $i++) {
					list($id, $secret) = explode("#",$attachments[$i]);
					
					
					
					$attachment = MessageAttachment::getByID($id);
					
					if($attachment != null) {
					    if($attachment->getAccessCode() == $secret) {			        
					        $messageSender->addAttachment($attachment);
					    }
					}
				}
				
				if(MessageSendRights::canAskQuestions()) {
				    
				    $questions = explode(";",$_REQUEST['questions']);
				    
				    
				    for($i = 0; $i < sizeof($questions); $i++) {
				        list($id, $secret) = explode("#",$questions[$i]);
				        
				        $question = MessageQuestion::getByID($id);
				        
				        if($question != null && $question->getSecret() == $secret) {
				            $messageSender->addQuestion($question);
				        }				        
				    }
				}
				
				
				if(MessageSendRights::canRequestReadingConfirmation() && $_REQUEST['readConfirmation'] > 0) $messageSender->setNeedConfirmation();
				
				
				$isReply = false;
				

				if($_REQUEST['forwardMessage'] != "") {
							
						$forwardMessage = Message::getByID(intval($_REQUEST['forwardMessage']));
						if($forwardMessage!= null) {
								if($forwardMessage->getUserID() == DB::getSession()->getUserID()) {
									$messageSender->setForwardMessage($forwardMessage);
								}
						}
				}
				
				if($_REQUEST['replyMessage'] != "") {
				    
				    $replyMessage = Message::getByID(intval($_REQUEST['replyMessage']));
				    if($replyMessage!= null) {
				        if($replyMessage->getUserID() == DB::getSession()->getUserID()) {
				            $messageSender->setReplyMessage($replyMessage);
				        }
				    }
				}
				
				if($_REQUEST['replyAllMessage'] != "") {
				    
				    $replyMessage = Message::getByID(intval($_REQUEST['replyMessage']));
				    
				    if($replyMessage!= null) {
				        if($replyMessage->getUserID() == DB::getSession()->getUserID()) {
				            $messageSender->setReplyMessage($replyMessage);
				        }
				    }
				}
				
						
				if($_REQUEST['dontAllowAnser'] > 0) {
					$messageSender->dontAllowAnswer();
				}
				
				$messageSender->send();
								
				header("Location: index.php?page=MessageInbox&folder=GESENDETE");
				exit(0);
			break;

				
		}

		$this->showForm();
	}
		
	private function showForm() {
	
		$isReply = false;
		$isForward = false;
		
		if($_REQUEST['forwardMessage'] != "") {
		    
			$forwardMessage = Message::getByID(intval($_REQUEST['forwardMessage']));
			if($forwardMessage!= null) {

					$isForward = true;

					$arr = array();
					$attachments = $forwardMessage->getAttachments();
						for($i = 0; $i < sizeof($attachments); $i++) {
							array_push($arr, array(
								'attachmentID' => $attachments[$i]->getID(),
								'attachmentFileName' => $attachments[$i]->getUpload()->getFileName(),
								'attachmentAccessCode' => $attachments[$i]->getAccessCode(),
								'attachmentURL' => $attachments[$i]->getUpload()->getURLToFile(true)
							));
						}

						$forwardJSONData = json_encode([
							'key' => 'attachments',
							'value' => $arr
						]);
			}
	}

		if($_REQUEST['replyMessage'] != "") {
		    
		    $replyMessage = Message::getByID(intval($_REQUEST['replyMessage']));
		    if($replyMessage!= null) {
		        if($replyMessage->getUserID() == DB::getSession()->getUserID()) {
		            // --> Erlaubt
		            $isReply = true;
		            
		            $replyJSONData = [
		                'key' => 'U:' . $replyMessage->getSender()->getUserID(),
		                'name' => $replyMessage->getSender()->getDisplayNameWithFunction()
		            ];
		            
		            $replyJSONData = json_encode($replyJSONData);
		        }
		    }
		}
		
		
		if($_REQUEST['replyAllMessage'] != "") {
		    
		    if(MessageSendRights::canReplyAll()) {
		    
    		    $replyMessage = Message::getByID(intval($_REQUEST['replyAllMessage']));
    		    if($replyMessage!= null) {
    		        if($replyMessage->getUserID() == DB::getSession()->getUserID()) {
    		            // --> Erlaubt
    		            $isReply = true;
    		            $isReplyAll = true;
    		            
    		            
    		            $replyJSONData = [];
    		            $replyJSONDataCC = [];
    		            
    		            $replyJSONData[] = [
    		                'key' => 'U:' . $replyMessage->getSender()->getUserID(),
    		                'name' => $replyMessage->getSender()->getDisplayNameWithFunction()
    		            ];
    		            
    		            
    		            $recipients = $replyMessage->getRecipients();
    		            
    		            for($i = 0; $i < sizeof($recipients); $i++) {
    		                $replyJSONData[] = [
    		                    'key' => $recipients[$i]->getSaveString(),
    		                    'name' => $recipients[$i]->getDisplayName()
    		                ];
    		            }
    		            
    		            $ccRecipients = $replyMessage->getCCRecipients();
    		            
    		            for($i = 0; $i < sizeof($ccRecipients); $i++) {
    		                $replyJSONDataCC[] = [
    		                    'key' => $ccRecipients[$i]->getSaveString(),
    		                    'name' => $ccRecipients[$i]->getDisplayName()
    		                ];
    		            }
    		            
    		            $replyJSONData = json_encode($replyJSONData);
    		            
    		            $replyJSONDataCC = json_encode($replyJSONDataCC);
    		            
    		        }
    		    }
		    
		    }
		}
		
		
		if($_REQUEST['messageSubject'] != '') {
			$preText = htmlspecialchars($_REQUEST['messageText']);
			$preSubject = htmlspecialchars($_REQUEST['messageSubject']);
		}
		else $preSubject = '';
		
		
		$folder = 'GESENDETE';
		$folderID = 0;
		
		$folder = MessageFolder::getFolder(DB::getSession()->getUser(), $folder, $folderID);
		
		
		// Ordner Status
		
		$posteingangOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "POSTEINGANG", 0);
		$papierkorbOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "PAPIERKORB", 0);
		$archivOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "ARCHIV", 0);
		$gesendetOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "GESENDET", 0);
		
		$ownFolders = "";
		
		$folders = MessageFolder::getMyFolders(DB::getSession()->getUser());
		
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
		
		// Mögliche Empfänger auflisten
		
		$canContactAnyTeacher = false;
		$canContactAndyPupil = false;
		$canContactAnyParents = false;
		

		
		$allTeacher = MessageSendRights::isAllTeacherAllowed();
		if($allTeacher) $canContactAnyTeacher = true;
		
		
		$canContactAnyVerwaltung = false;
		
		$schulleitung = MessageSendRights::isSchulleitungAllowed();
		if($schulleitung) $canContactAnyTeacher = true;
		
		$personalrat = MessageSendRights::isPersonalratAllowed();
		if($personalrat) $canContactAnyVerwaltung = true;
		
		$hausmeister = MessageSendRights::isHausmeisterAllowed();
		if($hausmeister) $canContactAnyVerwaltung = true;
		
		$verwaltung = MessageSendRights::isVerwaltungAllowed();
		if($verwaltung) $canContactAnyVerwaltung = true;
		
		
		$fachschaftenAllowed = MessageSendRights::isFachschaftenAllowed();
		
		$canContactAnySingleTeacher = false;
		
		$allowedTeachers = MessageSendRights::getAllowedTeachers();
		$canContactAnySingleTeacher = sizeof($allowedTeachers) > 0;
		
		if($canContactAnySingleTeacher) $canContactAnyTeacher = true;
		
		$selectOptionsFachschaften = "";
		
		if($fachschaftenAllowed) {
			$canContactAnyTeacher = true;
			
			$fachschaften = FachschaftRecipient::getAllInstances();
			
			for($i = 0; $i < sizeof($fachschaften); $i++) {
				$selectOptionsFachschaften .= "<option value=\"" . $fachschaften[$i]->getSaveString() . "\">" . $fachschaften[$i]->getDisplayName() . "</option>\r\n";
				
			}
		}
		
		$klassenteams = MessageSendRights::getAllowedKlassenteams();
		
		
		$selectOptionsKlassenteams = '';
		
		for($i = 0; $i < sizeof($klassenteams); $i++) {
			$selectOptionsKlassenteams .= "<option value=\"" . $klassenteams[$i]->getSaveString() . "\">" . $klassenteams[$i]->getDisplayName() . "</option>\r\n";
			$canContactAnyVerwaltung = true;
		}
		
		$klassenleitungen = MessageSendRights::getAllowedKlassenleitungen();
		
		
		$selectOptionsKlassenleitung = '';
		$selectOptionsKlassenleitungJSON = [];
		
		for($i = 0; $i < sizeof($klassenleitungen); $i++) {
		    $selectOptionsKlassenleitungJSON[] = $klassenleitungen[$i]->getSaveString();
		    $selectOptionsKlassenleitung .= "<option value=\"" . $klassenleitungen[$i]->getSaveString() . "\">" . $klassenleitungen[$i]->getDisplayName() . "</option>\r\n";
		    $canContactAnyVerwaltung = true;
		}
		
		$selectOptionsKlassenleitungJSON = json_encode($selectOptionsKlassenleitungJSON);
		$selectOptionsKlassenleitungJSON = str_replace("\"","'",$selectOptionsKlassenleitungJSON);
		
		
		$selectOptionsSchueler = '';
		
		
		$canContactAnyPupil = false;
		
		$canContactAnySinglePupil = sizeof(MessageSendRights::getAllowedPupils()) > 0;
		
		if($canContactAnySinglePupil) $canContactAnyPupil = true;
	

		$canContactAnySingleParents = sizeof(MessageSendRights::getAllowedParents()) > 0;
		if($canContactAnySingleParents) $canContactAnyParents = true;
		
		
		$selectOptionsWholeGrades = '';
		
		$pupilRecipients = MessageSendRights::getAllowedPupilGrades();
		
		$selectIDs = [];
		
				
		for($i = 0; $i < sizeof($pupilRecipients); $i++) {
			
			$ks = $pupilRecipients[$i]->getKlasse()->getKlassenstufe();
			if($ks == '') $ks = 'Andere Klassen';
			else $ks = $ks . ". Klassen";
			
			
			$selectIDs[$ks][] = $pupilRecipients[$i];
			
			$canContactAnyPupil = true;
			// $selectOptionsWholeGrades .= "<option value=\"" . $pupilRecipients[$i]->getSaveString() . "\">" . $pupilRecipients[$i]->getDisplayName() . "</option>\r\n";
			
		}
		
		
		$preSelect = "";
		
		$selectOptionsWholeGrades = '<table class="table table-bordered"><tr>';
		$selectAllParentsOfGrade = '';
		
		foreach($selectIDs as $jgs => $klassen) {
			
			$selectOptionsWholeGrades .= "<td>";
			
			$selectOptionsWholeGrades .= "<b>" . $jgs . "</b><br />";
			
			// $preSelect .= "<button class=\"btn\" type=\"button\" onclick=\"$('#wholeGradeParents').val([";
			
			for($i = 0; $i < sizeof($klassen); $i++) {
				
				$selectOptionsWholeGrades .= "<input type=\"checkbox\" name=\"" . $klassen[$i]->getSaveString() . "\" value=\"1\" id=\"" . md5($klassen[$i]->getSaveString()) . "\"> <label for=\"" . $klassen[$i]->getSaveString() . "\">" . $klassen[$i]->getKlasse()->getKlassenName() . "</label><br />";
				
				$selectAllParentsOfGrade .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('check');\r\n";
				$selectAllParentsOfGradeUncheck .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('uncheck');\r\n";
			}
			
			
			
			// $preSelect .= ']).trigger(\'change\');' . "\">$jgs</button>\r\n";
			
			$selectOptionsWholeGrades .= "</td>";
			
		}
		
		$anzahl = 0;
		
		$selectOptionsWholeGrades .= "</tr><tr>";
		
		foreach($selectIDs as $jgs => $klassen) {
			$anzahl++;
			$selectOptionsWholeGrades .= "<td>";
			
			$select56 = '';
			$select57 = '';
			
			for($i = 0; $i < sizeof($klassen); $i++) {
				
				$select56 .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('check');\r\n";
				$select57 .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('uncheck');\r\n";
				
			}
			
			$selectOptionsWholeGrades .= "<button type=\"button\" onclick=\"javascript:" . $select56 . "\" class=\"btn\">Alle</button> ";
			$selectOptionsWholeGrades .= "<button type=\"button\" onclick=\"javascript:" . $select57 . "\" class=\"btn\">keine</button> ";
			
			$selectOptionsWholeGrades .= "</td>";
			
		}
		
		$selectOptionsWholeGrades .= "</tr><tr><td colspan=\"" . $anzahl . "\">";
		$selectOptionsWholeGrades .= "<button type=\"button\" onclick=\"javascript:" . $selectAllParentsOfGrade . "\" class=\"btn\">Alle Klassen</button> ";
		$selectOptionsWholeGrades .= "<button type=\"button\" onclick=\"javascript:" . $selectAllParentsOfGradeUncheck . "\" class=\"btn\">Keine Klassen</button> ";
		$selectOptionsWholeGrades .= "</td></table>";
		
		
		$canContactOwnUnterricht = false;
		$canContactAllUnterricht = false;
		
		if(MessageSendRights::isOwnUnterrichtAllowed()) {
		    $canContactAnyPupil = true;
		    $canContactAnyParents = true;
		    $canContactOwnUnterricht = true;
		}
		
		if(MessageSendRights::isAllUnterrichtAllowed()) {
		    $canContactAnyPupil = true;
		    $canContactAnyParents = true;
		    $canContactAllUnterricht = true;
		}
		
		
		//////////////////////////
		
		
		
		$selectOptionsWholeGradesParents = '';
		
		$parentsOfGradeRecipients = MessageSendRights::getAllowedParentsOfPupilsGrades();
		
		$selectIDs = [];
		
		for($i = 0; $i < sizeof($parentsOfGradeRecipients); $i++) {
			$canContactAnyParents = true;
			
			$ks = $parentsOfGradeRecipients[$i]->getKlasse()->getKlassenstufe();
			if($ks == '') $ks = 'Andere Klassen';
			else $ks = $ks . ". Klassen";
			
			$selectIDs[$ks][] = $parentsOfGradeRecipients[$i];
			
			// $selectOptionsWholeGradesParents .= "<option value=\"" . $parentsOfGradeRecipients[$i]->getSaveString() . "\" id=\"PG" . $parentsOfGradeRecipients[$i]->getKlasse()->getKlassenName() . "\">" . $parentsOfGradeRecipients[$i]->getDisplayName() . "</option>\r\n";
		}
		
		
		
		
		$preSelect = "";
		
		$selectOptionsWholeGradesParents = '<table class="table table-bordered"><tr>';
		$selectAllParentsOfGrade = '';
		
		foreach($selectIDs as $jgs => $klassen) {
			
			$selectOptionsWholeGradesParents .= "<td>";
			
			$selectOptionsWholeGradesParents .= "<b>" . $jgs . "</b><br />";
			
			// $preSelect .= "<button class=\"btn\" type=\"button\" onclick=\"$('#wholeGradeParents').val([";
			
			for($i = 0; $i < sizeof($klassen); $i++) {
				
				$selectOptionsWholeGradesParents .= "<input type=\"checkbox\" name=\"" . $klassen[$i]->getSaveString() . "\" value=\"1\" id=\"" . md5($klassen[$i]->getSaveString()) . "\"> <label for=\"" . $klassen[$i]->getSaveString() . "\">" . $klassen[$i]->getKlasse()->getKlassenName() . "</label><br />";
			
				$selectAllParentsOfGrade .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('check');\r\n";
				$selectAllParentsOfGradeUncheck .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('uncheck');\r\n";
			}
			
			
			
			// $preSelect .= ']).trigger(\'change\');' . "\">$jgs</button>\r\n";
			
			$selectOptionsWholeGradesParents .= "</td>";
			
		}
		
		$anzahl = 0;
		
		$selectOptionsWholeGradesParents .= "</tr><tr>";
		
		foreach($selectIDs as $jgs => $klassen) {
			$anzahl++;
			$selectOptionsWholeGradesParents .= "<td>";
			
			$select56 = '';
			$select57 = '';
			
			for($i = 0; $i < sizeof($klassen); $i++) {
				
				$select56 .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('check');\r\n";
				$select57 .= "$('#" . md5($klassen[$i]->getSaveString()) . "').iCheck('uncheck');\r\n";
			
			}
			
			$selectOptionsWholeGradesParents .= "<button type=\"button\" onclick=\"javascript:" . $select56 . "\" class=\"btn\">Alle</button> ";
			$selectOptionsWholeGradesParents .= "<button type=\"button\" onclick=\"javascript:" . $select57 . "\" class=\"btn\">keine</button> ";
			
			$selectOptionsWholeGradesParents .= "</td>";
			
		}
		
		$selectOptionsWholeGradesParents .= "</tr><tr><td colspan=\"" . $anzahl . "\">";
		$selectOptionsWholeGradesParents .= "<button type=\"button\" onclick=\"javascript:" . $selectAllParentsOfGrade . "\" class=\"btn\">Alle Klassen</button> ";
		$selectOptionsWholeGradesParents .= "<button type=\"button\" onclick=\"javascript:" . $selectAllParentsOfGradeUncheck . "\" class=\"btn\">Keine Klassen</button> ";
		$selectOptionsWholeGradesParents .= "</td></table>";
		
		
		
		$selectOptionsParents = '';
		
		
		$canAskQuestions = MessageSendRights::canAskQuestions();
		$canRequestReadConfirmation = MessageSendRights::canRequestReadingConfirmation();
		
		$canContactAnyGroups = false;
		
		
		$groups = GroupRecipient::getAllInstances();
		
		$canContactAnyGroups = sizeof($groups) > 0;
		
		$htmlGroups = "";
		
		for($i = 0; $i < sizeof($groups); $i++) {
		    $htmlGroups .= "<tr><td><button type=\"button\" onclick=\"javascript:addRecipientAction({'key':'" . $groups[$i]->getSaveString() . "', 'name':'" . addslashes($groups[$i]->getDisplayName()) . "'})\" class=\"btn btn-primary btn-block\">" . ($groups[$i]->getDisplayName()) . "</button></td></tr>";
		}
		
		
		if($_REQUEST['recipient'] != "") {
            $saveString = $_REQUEST['recipient'];

            $messageHandler = new RecipientHandler("");
            $messageHandler->addRecipientFromSaveString($saveString);

            $recipientObject = $messageHandler->getAllRecipients()[0];

            if($recipientObject instanceof UnknownRecipient) {

            }
            else {
                $presetRecipient = true;

                $presetJsonData = [
                    'key' => $recipientObject->getSaveString(),
                    'name' => $recipientObject->getDisplayName()
                ];

                $presetJsonData = json_encode($presetJsonData);
            }

        }

        $presetCCRecipient = false;

        if($_REQUEST['ccrecipient'] != "") {
            $saveString = $_REQUEST['ccrecipient'];

            $messageHandler = new RecipientHandler("");
            $messageHandler->addRecipientFromSaveString($saveString);

            $recipientObject = $messageHandler->getAllRecipients()[0];

            if($recipientObject instanceof UnknownRecipient) {

            }
            else {
                $presetCCRecipient = true;

                $presetCCJsonData = [
                    'key' => $recipientObject->getSaveString(),
                    'name' => $recipientObject->getDisplayName()
                ];

                $presetCCJsonData = json_encode($presetCCJsonData);
            }

        }
		
		
		$signature = "";
		
		$userSig = DB::getSession()->getUser()->getSignature();
		if($userSig != "") {
		    $signature = "<br><br>" . $userSig;
		}
		
		eval("\$FRAMECONTENT = \"" . DB::getTPL()->get("messages/inbox/compose") . "\";");
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("messages/inbox/frame") . "\");");
		exit(0);
		
	}
	
	public static function getSettingsDescription() {
		$settings = [];
		
		
		return $settings;
	}
	
	public static function getSiteDisplayName() {
		return "Nachrichten - Schreiben";
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