<?php

class MessageRead extends AbstractPage {
    
  private $message = null;
  
  private $dialogsConfirmationStatus = "";
  
  private $dialogNumber = 0;
  
  public function __construct() {
    parent::__construct(array("Nachrichten"));
  }

  public function execute() {

    $this->message = Message::getByID(intval($_REQUEST['messageID']));
        
    MessageSendRights::init();

    if($this->message == null) {
      new errorPage("Nachricht nicht gefunden!");
    }

    if($this->message->getUserID() != DB::getSession()->getUserID()) {
      new errorPage("Zugriffsverletzung!");
    }
    
    if($_REQUEST['printFehlliste'] > 0 && $this->message->getFolder() == 'GESENDETE') {
        $this->printFehlliste($this->message);
    	exit(0);
    }
    
    if($_REQUEST['printAuswertungsliste'] > 0 && $this->message->hasQuestions() && $this->message->getFolder() == 'GESENDETE') {
        $this->printAuswertung($this->message);
        exit(0);
    }

    $folder = $this->message->getFolder();
    $folderID = $this->message->getFolderID();
    
    $folder = MessageFolder::getFolder(DB::getSession()->getUser(), $folder, $folderID);
    

    $this->message->setRead();
    
    $questions = $this->message->getQuestions();
    
    $answer = $this->message->getQuestionAnswers();
    
    $htmlInfoQuestions = "";
    $questionHtmlForm = "";
    
    
    for($i = 0; $i < sizeof($questions); $i++) {
        
        $htmlInfoQuestions .= $questions[$i]->getQuestionText() . "<br />";
        $questionsCols .= "<th>" . $questions[$i]->getQuestionText() . "</th>";
        
        if($i > 0) $questionHtmlForm .= "<hr>";
        $questionHtmlForm .= "<p><b><i class=\"fa fa-question-circle\"></i> " . $questions[$i]->getQuestionText() . "</b><br />";
        
        $currentAnswer = NULL;
        
        for($a = 0; $a < sizeof($answer); $a++) {
            if($questions[$i]->getID() == $answer[$a]->getQuestionID()) {
                $currentAnswer = $answer[$a]->getAnswerData();
            }
        }
        
        if($questions[$i]->isBooleanQuestion()) {
            if($currentAnswer == NULL) $currentAnswer = 0;
            $questionHtmlForm .= "<label><input type=\"radio\" name=\"question_" . $questions[$i]->getID() . "\" value=\"1\"" . (($currentAnswer == 1) ? "checked" : "") . "> Ja</label> ";
            $questionHtmlForm .= "<label><input type=\"radio\" name=\"question_" . $questions[$i]->getID() . "\" value=\"0\"" . (($currentAnswer == 0) ? "checked" : "") . "> Nein</label>";
        }
        
        if($questions[$i]->isTextQuestion()) {
            $questionHtmlForm .= "<input type=\"text\" name=\"question_" . $questions[$i]->getID() . "\" value=\"$currentAnswer\" class=\"form-control\">";
            
        }
        
        if($questions[$i]->isNumberQuestion()) {
            $questionHtmlForm .= "<input type=\"number\" name=\"question_" . $questions[$i]->getID() . "\" value=\"$currentAnswer\" class=\"form-control\" placeholder=\"Sie können hier nur ganze Zahlen eingeben\">";
            
        }
        
        if($questions[$i]->isFileQuestion()) {
            if($currentAnswer > 0) {
                $upload = FileUpload::getByID($currentAnswer);
                if($upload != null) {
                    if($upload->getUploader()->getUserID() == DB::getSession()->getUserID()) {
                        $questionHtmlForm .= "Ihre bereits hochgeladene Datei: <a href=\"" . $upload->getURLToFile(true) . "\">" . $upload->getFileName() . "</a><br /><small>Wenn Sie eine neue Datei auswählen, dann wird Ihre alte Datei gelöscht.";
                    }
                }
            }
            $questionHtmlForm .= "<input type=\"file\" name=\"question_" . $questions[$i]->getID() . "\" class=\"form-control\"><small>Sie können Office Dokumente, Bilder und ZIP Dateien hochladen.</small>";
            
        }
        
        $questionHtmlForm .= "</p>";
        
       //  if($questions[$i]->)
        
    }
    
    $message = $this->message;

    if($_POST['action'] == 'ConfirmMessage') {
        $this->message->confirmMessage('PORTAL');
      
        if($this->message->hasQuestions()) {
          // Fragendaten speichern
          $fileSuccess = true;
          
          
          for($i = 0; $i < sizeof($questions); $i++) {
              
              $rawData = $_REQUEST['question_' . $questions[$i]->getID()];
                            
              $finalData = "";
              
              if($questions[$i]->isBooleanQuestion()) {
                  if($rawData == "1") $finalData = 1;
                  else $finalData = 0;
              }
              
              if($questions[$i]->isTextQuestion()) {
                  $finalData = $rawData;
                  
              }
              
              if($questions[$i]->isNumberQuestion()) {
                  $finalData = intval($rawData);
              }
              
              if($questions[$i]->isFileQuestion()) {
                  $upload = FileUpload::uploadOfficePdfOrPicture('question_' . $questions[$i]->getID(), '');
                  if($upload['result']) {
                      $finalData = $upload['uploadobject']->getID();
                  }
                  else {
                      $fileSuccess = false;
                  }
              }
              
              
              $this->message->answerQuestion($questions[$i]->getID(), $finalData, false);
          }
          
          
          if($fileSuccess) header("Location: index.php?page=MessageRead&messageID=" . $message->getID() . "&confirmQuestionSuccess=1");
          else header("Location: index.php?page=MessageRead&messageID=" . $message->getID() . "&confirmQuestionSuccessFileFail=1");
      }
      else {
          header("Location: index.php?page=MessageRead&messageID=" . $message->getID() . "&confirmSuccess=1");
        exit(0);
      }
    }
    else if($_POST['action'] == 'forward') {
      header("Location: index.php?page=MessageCompose&forwardMessage=" . $message->getID());
      exit(0);
    } else if($_POST['action'] == 'reply') {
        header("Location: index.php?page=MessageCompose&replyMessage=" . $message->getID());
        exit(0);
    }
    else if($_POST['action'] == 'replyAll') {
        header("Location: index.php?page=MessageCompose&replyAllMessage=" . $message->getID());
        exit(0);
    }
    else if($_POST['action'] == 'deleteMessage') {
    	MessageFolder::getFolder(DB::getSession()->getUser(), $message->getFolder(), $folderID)->deleteMessages([$message->getID()]);
    	header("Location: index.php?page=MessageInbox&folder=" . $message->getFolder());
    	exit(0);
    }

    
    $recipients = $this->message->getRecipients();

    $allRecipients = [];
    for($i = 0; $i < sizeof($recipients); $i++) {
        if($recipients[$i] != null && $this->message->getMyRecipient() != null && $recipients[$i]->getSaveString() == $this->message->getMyRecipient()->getSaveString()) {
            $allRecipients[] = "<strong>" . $recipients[$i]->getDisplayName()  ."</strong>";
        }
        else if($recipients[$i] != null) {
            $allRecipients[] = $recipients[$i]->getDisplayName();
        }
        else $allRecipients[] = 'n/a';
    }
    
    $ccRecipients = $this->message->getCCRecipients();
    $bccRecipients = $this->message->getBCCRecipients();
    
    
    $allCCRecipients = [];
    for($i = 0; $i < sizeof($ccRecipients); $i++) {
        $allCCRecipients[] = $ccRecipients[$i]->getDisplayName();
    }
    
    $allBCCRecipients = [];
    for($i = 0; $i < sizeof($bccRecipients); $i++) {
        $allBCCRecipients[] = $bccRecipients[$i]->getDisplayName();
    }
    

    $allRecipients = implode(", ", $allRecipients);
    
    $allCCRecipients = implode(", ", $allCCRecipients);
    
    $allBCCRecipients = implode(", ", $allBCCRecipients);
    
    
    $allRecipientsWithConfirmationStatus = [];
    
    $allRecipientsWithConfirmationStatusCC = [];
    
    $allRecipientsWithConfirmationStatusBCC = [];
    
    $this->dialogsConfirmationStatus = "";
    
    $message = $this->message;  
    
    if($message->needConfirmation() && $message->getFolder() == 'GESENDETE') {

        $allRecipientsWithConfirmationStatus = $this->getConfirmationStatusHTML($this->message->getRecipients());
        $allRecipientsWithConfirmationStatusCC = $this->getConfirmationStatusHTML($this->message->getCCRecipients());
        $allRecipientsWithConfirmationStatusBCC = $this->getConfirmationStatusHTML($this->message->getBCCRecipients());
    	
    }
    else if($message->getFolder() == 'GESENDETE') {
        
        $allRecipientsWithConfirmationStatus = $this->getConfirmationStatus($this->message->getRecipients());
        $allRecipientsWithConfirmationStatusCC = $this->getConfirmationStatus($this->message->getCCRecipients());
        $allRecipientsWithConfirmationStatusBCC = $this->getConfirmationStatus($this->message->getBCCRecipients());
        
       
    }
    else {
        $allRecipientsWithConfirmationStatus = $allRecipients;
        $allRecipientsWithConfirmationStatusCC = $allCCRecipients;
        $allRecipientsWithConfirmationStatusBCC = $allBCCRecipients;
        
    }


    if($message->isForward()) {
		$forwardMessage = $message->getForwardMessage();
    }
    
    if($message->isReply()) {
		$replyMessage = $message->getReplyMessage();
    }
    
    
    // Attachments
    
    $attachmentHTML = '';
    
    $attachments = $message->getAttachments();
    
    for($i = 0; $i < sizeof($attachments); $i++) {
        
        $type = "fa fa-file";
        
        $file = $attachments[$i]->getUpload();
        
        if($file->isImage()) {
            $attachmentHTML .= '<li>
                  <span class="mailbox-attachment-icon has-img"><img src="' . $file->getURLToFile() . '&maxWidth=200"></span>
                
                  <div class="mailbox-attachment-info">
                    <a href="' . $file->getURLToFile(true) . '" class="mailbox-attachment-name"><i class="fa fa-camera"></i> ' . $file->getFileName() . '</a>
                        <span class="mailbox-attachment-size">
                         ' . $file->getFileSize() . '
                          <a href="' . $file->getURLToFile(true) . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
                        </span>
                  </div>
                </li>';
        }
        
        else if($file->isPDF()) {
            $attachmentHTML .= '<li>
                  <span class="mailbox-attachment-icon has-img"><img src="' . $file->getURLToFile() . '&showPDFPreview=200"></span>
                      
                  <div class="mailbox-attachment-info">
                    <a href="' . $file->getURLToFile(true) . '" class="mailbox-attachment-name"><i class="fa fa-file-pdf"></i> ' . $file->getFileName() . '</a>
                        <span class="mailbox-attachment-size">
                         ' . $file->getFileSize() . '
                          <a href="' . $file->getURLToFile(true) . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
                        </span>
                  </div>
                </li>';
        }
        else {
            $attachmentHTML .= '<li>
                  <span class="mailbox-attachment-icon"><i class="' . $file->getFileTypeIcon() . '"></i></span>
                      
                  <div class="mailbox-attachment-info">
                    <a href="' . $file->getURLToFile(true) . '" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i> ' . $file->getFileName() . '</a>
                        <span class="mailbox-attachment-size">
                         ' . $file->getFileSize() . '
                          <a href="' . $file->getURLToFile(true) . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
                        </span>
                  </div>
                </li>';
        }
        

        
        
        
    	// $attachmentHTML .= "<form><button type=\"button\" onclick=\"window.location.href='index.php?page=MessageAttachmentDownload&aid=" . $attachments[$i]->getID() . "&ac=" . $attachments[$i]->getAccessCode() . "'\" class=\"btn btn-info\"><i class=\"fa fa-download\"></i> " .  $attachments[$i]->getUpload()->getFileName() . "</button></form>";
    }


    // Ordner Status

    $posteingangOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "POSTEINGANG", 0);
    $gesendetOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "GESENDET", 0);
    $papierkorbOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "PAPIERKORB", 0);
    $archivOrdner = MessageFolder::getFolder(DB::getSession()->getUser(), "ARCHIV", 0);
    

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
    
    
    if($_REQUEST['action'] == 'print') {

        if($message->isForward()) {
            $forwardMessage = $message->getForwardMessage();
        }

        $html = "";
        
        $attachmentHTML = "";
        
        $attachments = $message->getAttachments();
        
        for($i = 0; $i < sizeof($attachments); $i++) {
            $attachmentHTML .= $attachments[$i]->getUpload()->getFileName() . "<br />";
            
        }
        
        if($attachmentHTML != "") $attachmentHTML = "<b>Anhänge:</b><br />" . $attachmentHTML;
        
        
        eval("\$html = \"" . DB::getTPL()->get("messages/inbox/print") . "\";");
        
        $print = new PrintNormalPageA4WithHeader("Nachricht drucken - " . $message->getSubject());
        $print->setHTMLContent($html);
        $print->setPrintedDateInFooter();
        $print->send();
        exit(0);
    }
    
    

    eval("\$FRAMECONTENT = \"" . DB::getTPL()->get("messages/inbox/read") . "\";");
    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("messages/inbox/frame") . "\");");
    //exit(0);
    PAGE::kill(true);
  }
  
  /**
   * 
   * @param MessageRecipient[] $recipients
   * @return string
   */
  private function getConfirmationStatusHTML($recipients) {
      
      // Debugger::debugObject($recipients);
      
      $message = $this->message;
      $questions = $this->message->getQuestions();
      
      
      if($message->hasQuestions()) {
          $answerStat = [];
          
          for($i = 0; $i < sizeof($questions); $i++) {
              if($questions[$i]->isBooleanQuestion()) {
                  $answerStat[] = [
                      'ja' => 0,
                      'nein' => 0,
                      'keine' => 0
                  ];
              }
              
              if($questions[$i]->isFileQuestion() || $questions[$i]->isTextQuestion()) {
                  $answerStat[] = [
                      'abgegeben' => 0,
                      'keine' => 0
                  ];
              }
              
              if($questions[$i]->isNumberQuestion()) {
                  $answerStat[] = [
                      'summe' => 0,
                      'keine' => 0
                  ];
              }
          }
      }
      
      
      for($i = 0; $i < sizeof($recipients); $i++) {
          
          
          $gelesen = $recipients[$i]->getPercentConfirmed();
         
          
          $this->dialogNumber++;
          
          $allRecipientsWithConfirmationStatus[] = "<button type=\"button\" class=\"btn btn-info btn-sm\" data-toggle=\"modal\" data-target=\"#confirmationStatus{$this->dialogNumber}\">" .  $recipients[$i]->getDisplayName() . " (<progress  max=\"100\" value=\"" . $recipients[$i]->getPercentConfirmed() . "\"></progress> $gelesen % gelesen) </button>";
          
          
          $htmlConfirmation = '';
          
          $messages = $recipients[$i]->getSentMessagesWithThisRecipient();
          
          $htmlConfirmation .= $recipients[$i]->getSentInfoTable($message);
          
          
          if($message->hasQuestions()) {
              for($m = 0; $m < sizeof($messages); $m++) {
                  
                  for($q = 0; $q < sizeof($questions); $q++) {
                      
                      $answers = $questions[$q]->getAllAnswers();
                      
                      $data = null;
                      
                      for($a = 0; $a < sizeof($answers); $a++) {
                          if($answers[$a]->getMessageID() == $messages[$m]->getID()) {
                              $data = $answers[$a]->getAnswerData();
                          }
                      }
                      
                      if($data == null) {
                          $answerStat[$q]['keine']++;
                      }
                      else {
                          if($questions[$q]->isBooleanQuestion()) {
                              if($data == 1) {
                                  $answerStat[$q]['ja']++;
                              }
                              else {
                                  $answerStat[$q]['nein']++;
                              }
                          }
                          
                          if($questions[$q]->isFileQuestion()) {
                              $answerStat[$q]['abgegeben']++;
                          }
                          
                          if( $questions[$q]->isTextQuestion()) {
                              $answerStat[$q]['abgegeben']++;        			        }
                              
                              
                              if( $questions[$q]->isNumberQuestion()) {
                                  $answerStat[$q]['summe'] += $data;
                                  $answerStat[$q]['abgegeben']++;
                              }
                      }
                  }
              }
          }
          
          if($message->hasQuestions()) {
              
              $htmlConfirmation .= "<tr><td><b>Auswertung</b></td><td>&nbsp;</td>";
              
              
              for($q = 0; $q < sizeof($questions); $q++) {
                  $htmlConfirmation .= "<td>";
                  
                  if($questions[$q]->isBooleanQuestion()) {
                      $summe = $answerStat[$q]['ja'] + $answerStat[$q]['nein'] + $answerStat[$q]['keine'];
                      
                      $htmlConfirmation .= "Ja: " . $answerStat[$q]['ja'] . " (" . round($answerStat[$q]['ja']/$summe*100) . " %)<br />";
                      $htmlConfirmation .= "Nein: " . $answerStat[$q]['nein'] . " (" . round($answerStat[$q]['nein']/$summe*100) . " %)<br />";
                      $htmlConfirmation .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe*100) . " %)<br />";
                      
                  }
                  
                  if($questions[$q]->isFileQuestion() || $questions[$q]->isTextQuestion()) {
                      $summe = $answerStat[$q]['abgegeben'] + $answerStat[$q]['keine'];
                      $htmlConfirmation .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . " (" . round($answerStat[$q]['abgegeben']/$summe*100) . " %)<br />";
                      $htmlConfirmation .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe*100) . " %)<br />";
                      
                  }
                  
                  if($questions[$q]->isNumberQuestion()) {
                      
                      $htmlConfirmation .= "Summe: " . $answerStat[$q]['summe'] . "<br />";
                      $htmlConfirmation .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . "<br />";
                      $htmlConfirmation .= "Mittelwert: " . round($answerStat[$q]['summe'] / $answerStat[$q]['abgegeben'], 2) . "<br />";
                      
                  }
                  
                  $htmlConfirmation .= "</td>";
              }
              
              $htmlConfirmation .= "</tr>";
              
          }
          
          
          eval("\$this->dialogsConfirmationStatus .= \"" . DB::getTPL()->get("messages/inbox/confirmationStatusDialog") . "\";");
          
          
          
      }
      
      $allRecipientsWithConfirmationStatus = implode("; ", $allRecipientsWithConfirmationStatus);
      
      return $allRecipientsWithConfirmationStatus;
      
  }
  
  private function getConfirmationStatus($recipients) {
      for($i = 0; $i < sizeof($recipients); $i++) {
          
          
          $gelesen = $recipients[$i]->getPercentConfirmed();
          
          $allRecipientsWithConfirmationStatus[] = "<button type=\"button\" class=\"btn btn-info btn-sm\" data-toggle=\"modal\" data-target=\"#confirmationStatus$i\">" .  $recipients[$i]->getDisplayName() . "</button>";
          
          
          $htmlConfirmation = '';
          
          $messages = $recipients[$i]->getSentMessagesWithThisRecipient();
          
          for($m = 0; $m < sizeof($messages); $m++) {
              if($messages[$m]->getUser() != null) {
                  $htmlConfirmation .= $messages[$m]->getUser()->getDisplayName() . "<br /><small>" . $messages[$m]->getUser()->getUserName() . "</small><br />";
                  
              }
              else {
                  $htmlConfirmation .= "<i>Unbekannter Empfänger</i><br />";
                  
              }
          }
          
          
          
          
          //  eval("\$this->dialogsConfirmationStatus .= \"" . DB::getTPL()->get("messages/inbox/confirmationStatusDialog") . "\";");
          
          
          $this->dialogsConfirmationStatus .= '<div class="modal fade" id="confirmationStatus' . $i . '" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
               <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user"></i> Empfänger von ' . $recipients[$i]->getDisplayName() . '</h4>
		      </div>
		            
		      <div class="modal-body">' . $htmlConfirmation . '</div></div></div></div>';
      }
      
      $allRecipientsWithConfirmationStatus = implode(" ", (array)$allRecipientsWithConfirmationStatus);
      
      return $allRecipientsWithConfirmationStatus;
  }
  
  /**
   * 
   * @param Message $message
   */
  private function printFehlliste($message) {
  	
  	$recipients = $message->getRecipients();
  	
  	$print = new PrintNormalPageA4WithHeader('Fehlliste ' . $message->getSubject());
  	$print->setPrintedDateInFooter();
  	$print->showHeaderOnEachPage();
  	
  	$added = false;
  	
  	$attachments = $message->getAttachments();
  	
  	$attachmentList = [];
  	
  	for($a = 0; $a < sizeof($attachments); $a++) {
  	    $attachmentList[] = $attachments[$a]->getUpload()->getFileName();
  	}
  	
  	$infoTable = "<h1>Gesamtübersicht</h1><table border=\"1\" cellpadding=\"3\" width=\"100%\">";
  	
  	$infoTable .= "<tr><th>Empfänger</th><th>Fehlende Empfänger</th></tr>";
  	
  	$htmlPages = [];
  	
  	$totalMissing = 0;
  	
  	
  	for($i = 0; $i < sizeof($recipients); $i++) {
  		$missing = $recipients[$i]->getMissingNames();
  		
  		if(sizeof($missing) > 0) {
  			
  			$added = true;
  			
  			$html = '<h2>Fehlliste ' . $recipients[$i]->getDisplayName() . "</h2>";
  			
  			$html .= "<table border=\"0\" cellpadding=\"3\" width=\"100%\"><tr><td width=\"30%\">Betreff der Nachricht:</td><td>" . $message->getSubject() . "</td></tr>";
  			$html .= "<tr><td>Anhänge:</td><td>" . implode(", ", $attachmentList) . "</td></tr>";
  			$html .= "<tr><td>Datum:</td><td>" . functions::makeDateFromTimestamp($message->getTime()) . "</td></tr></table><br /><br /><br />";
  			
  			$html .= "<table width=\"100%\" border=\"1\" cellpadding=\"3\"><tr><th width=\"5%\"><b>#</b></th><th width=\"90%\"><b>Name</b></th><th width=\"5%\"><input type=\"checkbox\" name=\"box\" value=\"1\" readonly=\"true\" /></th></tr>";
  			
  			
  			for($m = 0; $m < sizeof($missing); $m++) {
  				$html .= "<tr><td>" . ($m+1) . "</td><td>" . $missing[$m] . "</td><td width=\"5%\"><input type=\"checkbox\" name=\"box\" value=\"1\" readonly=\"true\" /></td></tr>\r\n";
  			}
  			
  			
  			$html .= "</table>";
  			
  			
  			$totalMissing += sizeof($missing);
  			
  			$htmlPages[] = $html;
  			
  		}
  		
  		$infoTable .= "<tr><td>" . $recipients[$i]->getDisplayName() . "</td><td>" . sizeof($missing) . "</td></tr>";
  		
  	}
  	
  	$infoTable .= "<tr><td><b>Gesamt</b></td><td><b>" . $totalMissing. "</b></td></tr>";
  	
  	$infoTable .= "</table>";
  	
    
  	
  	if(!$added) {
  		$print->setHTMLContent('<h3>Keine fehlenden Empfänger. Alle elektronisch erreichbar.</h3>');
  	}
  	else {
  	    $print->setHTMLContent($infoTable);
  	    
  	    for($i = 0; $i < sizeof($htmlPages); $i++) {
  	        $print->setHTMLContent($htmlPages[$i]);
  	    }
  	}
  	
  	$print->send();
  	exit(0);
  }
  
  /**
   * 
   * @param Message $message
   */
  private function printAuswertung($message) {
      
      $recipients = $message->getRecipients();
      
      $ccRecipients = $message->getCCRecipients();
      $bccRecipients = $message->getBCCRecipients();
      
      $recipients = array_merge($recipients,$ccRecipients,$bccRecipients);
      
      $print = new PrintNormalPageA4WithHeader('Auswertung ' . $message->getSubject());
      $print->setPrintedDateInFooter();
      $print->showHeaderOnEachPage();
      
      
      $questions = $message->getQuestions();
      
      $answerStatTotal = [];
      
      for($i = 0; $i < sizeof($questions); $i++) {
          if($questions[$i]->isBooleanQuestion()) {
              $answerStatTotal[] = [
                  'ja' => 0,
                  'nein' => 0,
                  'keine' => 0
              ];
          }
          
          if($questions[$i]->isFileQuestion() || $questions[$i]->isTextQuestion()) {
              $answerStatTotal[] = [
                  'abgegeben' => 0,
                  'keine' => 0
              ];
          }
          
          if($questions[$i]->isNumberQuestion()) {
              $answerStatTotal[] = [
                  'summe' => 0,
                  'keine' => 0
              ];
          }
      }
      
      
      //   	Debugger::debugObject($recipients,1);
      
      for($i = 0; $i < sizeof($recipients); $i++) {
              
              $missing = $recipients[$i]->getSentMessagesWithThisRecipient();
          
              $answerStat = [];
              
              for($q = 0; $q < sizeof($questions); $q++) {
                  if($questions[$q]->isBooleanQuestion()) {
                      $answerStat[] = [
                          'ja' => 0,
                          'nein' => 0,
                          'keine' => 0
                      ];
                  }
                  
                  if($questions[$q]->isFileQuestion() || $questions[$q]->isTextQuestion()) {
                      $answerStat[] = [
                          'abgegeben' => 0,
                          'keine' => 0
                      ];
                  }
                  
                  if($questions[$q]->isNumberQuestion()) {
                      $answerStat[] = [
                          'summe' => 0,
                          'keine' => 0
                      ];
                  }
              }
              
              $html = '<h2>Auswertung ' . $recipients[$i]->getDisplayName() . "</h2>";
              
              $html .= "<table border=\"0\" cellpadding=\"3\" width=\"100%\"><tr><td width=\"30%\">Betreff der Nachricht:</td><td>" . $message->getSubject() . "</td></tr>";
              $html .= "<tr><td>Anhänge:</td><td>n/a</td></tr>";
              $html .= "<tr><td>Datum:</td><td>" . functions::makeDateFromTimestamp($message->getTime()) . "</td></tr></table><br /><br /><br />";
              
              $html .= "<table width=\"100%\" border=\"1\" cellpadding=\"3\"><tr><th width=\"5%\"><b>#</b></th><th><b>Name</b></th>";
              
              for($q = 0; $q < sizeof($questions); $q++) {
                  $html .= "<th>" . $questions[$q]->getQuestionText() . "</th>";
              }
              
              $html .= "</tr>";
              
              
              for($m = 0; $m < sizeof($missing); $m++) {
                  
                  if($missing[$m]->getUser() != null) {
                      
                      $html .= "<tr><td>" . ($m+1) . "</td><td>" . $missing[$m]->getUser()->getDisplayNameWithFunction() . "</td>\r\n";
                      
                      
                      for($q = 0; $q < sizeof($questions); $q++) {
                      
                          $answers = $questions[$q]->getAllAnswers();
                          
                          $html .= "<td>";
                                            
                          $data = null;
                          
                          for($a = 0; $a < sizeof($answers); $a++) {
                              if($answers[$a]->getMessageID() == $missing[$m]->getID()) {
                                  $data = $answers[$a]->getAnswerData();
                              }
                          }
                          
                          if($data == null) {
                              $html .= "<i>Keine Antwort</i>";
                              $answerStat[$q]['keine']++;
                          }
                          else {
                              if($questions[$q]->isBooleanQuestion()) {
                                  if($data == 1) {
                                      $answerStat[$q]['ja']++;
                                      $html .= "Ja";
                                  }
                                  else {
                                      $answerStat[$q]['nein']++;
                                      $html .= "Nein";
                                  }
                              }
                              
                              if($questions[$q]->isFileQuestion()) {
                                  $answerStat[$q]['abgegeben']++;
                                  $upload = FileUpload::getByID($data);
                                  if($upload != null) {
                                      $html .= $upload->getFileName();
                                  }
                              }
                              
                              if( $questions[$q]->isTextQuestion()) {
                                  $answerStat[$q]['abgegeben']++;
                                  $html .= $data;
                              }
                              
                              
                              if( $questions[$q]->isNumberQuestion()) {
                                  $answerStat[$q]['summe'] += $data;
                                  $answerStat[$q]['abgegeben']++;
                                  $html .= $data;
                              }
                          }
                          
                          $html .= "</td>";
                      }
                      
                      $html .= "</tr>";
                  }
              }
              
              $html .= "<tr><td colspan=\"2\"><b>Auswertung</b></td>";
              
              
              
              for($q = 0; $q < sizeof($questions); $q++) {
                  $html .= "<td>";
                  
                  
                  $answerStatTotal[$q]['keine'] += $answerStat[$q]['keine'];
                  
                  
                  if($questions[$q]->isBooleanQuestion()) {
                      $summe = $answerStat[$q]['ja'] + $answerStat[$q]['nein'] + $answerStat[$q]['keine'];
                      
                      $html .= "Ja: " . $answerStat[$q]['ja'] . " (" . round($answerStat[$q]['ja']/$summe*100) . " %)<br />";
                      $html .= "Nein: " . $answerStat[$q]['nein'] . " (" . round($answerStat[$q]['nein']/$summe*100) . " %)<br />";
                      $html .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe*100) . " %)<br />";
                      
                      $answerStatTotal[$q]['ja'] += $answerStat[$q]['ja'];
                      $answerStatTotal[$q]['nein'] += $answerStat[$q]['nein'];
                      
                  }
                  
                  if($questions[$q]->isFileQuestion() || $questions[$q]->isTextQuestion()) {
                      $summe = $answerStat[$q]['abgegeben'] + $answerStat[$q]['keine'];
                      $html .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . " (" . round($answerStat[$q]['abgegeben']/$summe*100) . " %)<br />";
                      $html .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe*100) . " %)<br />";
                      
                      $answerStatTotal[$q]['abgegeben'] += $answerStat[$q]['abgegeben'];
                      
                  }
                  
                  if($questions[$q]->isNumberQuestion()) {
                      
                      $html .= "Summe: " . $answerStat[$q]['summe'] . "<br />";
                      $html .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . "<br />";
                      $html .= "Mittelwert: " . round($answerStat[$q]['summe'] / $answerStat[$q]['abgegeben'], 2) . "<br />";
                      
                      $answerStatTotal[$q]['summe'] += $answerStat[$q]['summe'];
                      $answerStatTotal[$q]['abgegeben'] += $answerStat[$q]['abgegeben'];
                      
                  }
                  
                  $html .= "</td>";
              }
              
              $html .= "</tr>";
              
              
              $html .= "</table>";
                            
              $print->setHTMLContent($html);
              
      }
      
      $html = '<h2>Gesamtauswertung</h2>';
      
      $html .= "<table border=\"0\" cellpadding=\"3\" width=\"100%\"><tr><td width=\"30%\">Betreff der Nachricht:</td><td>" . $message->getSubject() . "</td></tr>";
      $html .= "<tr><td>Datum:</td><td>" . functions::makeDateFromTimestamp($message->getTime()) . "</td></tr></table><br /><br /><br />";
      
      $html .= "<table width=\"100%\" border=\"1\" cellpadding=\"3\">";
      
      $html .= "<tr><th><b>Frage</b></th>";
      
      for($q = 0; $q < sizeof($questions); $q++) {
          $html .= "<th>" . $questions[$q]->getQuestionText() . "</th>";
      }
      
      $html .= "</tr><tr>";
      
      $answerStat = $answerStatTotal;
      
      for($q = 0; $q < sizeof($questions); $q++) {
          $html .= "<td>&nbsp;</td>";
          $html .= "<td>";
          
          
          
          
          if($questions[$q]->isBooleanQuestion()) {
              $summe = $answerStat[$q]['ja'] + $answerStat[$q]['nein'] + $answerStat[$q]['keine'];
              
              $html .= "Ja: " . $answerStat[$q]['ja'] . " (" . round($answerStat[$q]['ja']/$summe) . ")<br />";
              $html .= "Nein: " . $answerStat[$q]['nein'] . " (" . round($answerStat[$q]['nein']/$summe) . ")<br />";
              $html .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe) . ")<br />";
              
              
          }
          
          if($questions[$q]->isFileQuestion() || $questions[$q]->isTextQuestion()) {
              $summe = $answerStat[$q]['abgegeben'] + $answerStat[$q]['keine'];
              $html .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . " (" . round($answerStat[$q]['abgegeben']/$summe) . ")<br />";
              $html .= "Keine Antwort: " . $answerStat[$q]['keine'] . " (" . round($answerStat[$q]['keine']/$summe) . ")<br />";
              
              
          }
          
          if($questions[$q]->isNumberQuestion()) {
              
              $html .= "Summe: " . $answerStat[$q]['summe'] . "<br />";
              $html .= "Abgegeben: " . $answerStat[$q]['abgegeben'] . "<br />";
              $html .= "Mittelwert: " . round($answerStat[$q]['summe'] / $answerStat[$q]['abgegeben'], 2) . "<br />";
              
              
          }
          
          $html .= "</td>";
      }
      
      $html .= "</tr>";
      
      
      $html .= "</table>";
      
      $print->setHTMLContent($html);
      
      
      
      $print->send();
      exit(0);
  }

  public static function getSettingsDescription() {
    $settings = [];


    return $settings;
  }

  public static function getSiteDisplayName() {
    return "Nachrichten - Nachricht lesen";
  }

  public static function hasSettings() {
    return false;
  }

  public static function siteIsAlwaysActive() {
    return true;
  }


}


?>
