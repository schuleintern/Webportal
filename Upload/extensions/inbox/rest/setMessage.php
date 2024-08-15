<?php


class setMessage extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $sender_id = (int)$input['sender'];
        if (!$sender_id) {
            return [
                'error' => true,
                'msg' => 'Missing SID'
            ];
        }
        $receiver = (string)$_POST['receiver'];
        if (!$receiver) {
            return [
                'error' => true,
                'msg' => 'Missing RID'
            ];
        }
        $receivers_cc = (string)$_POST['receiver_cc'];
        if (!$receivers_cc) {
            $receivers_cc = '';
        }

        $subject = (string)$input['subject'];
        if (!$subject || $subject == 'undefined') {
            $subject = '- Kein Betreff -';
        }

        $text = (string)$_POST['text'];
        if (!$text || $text == 'undefined') {
            $text = '';
        }

        $confirm = (int)$input['confirm'];
        if (!$confirm || $confirm == 'undefined') {
            $confirm = 0;
        }

        $priority = (int)$input['priority'];
        if (!$priority || $priority == 'undefined') {
            $priority = 0;
        }
        $noAnswer = (int)$input['noAnswer'];
        if (!$noAnswer || $noAnswer == 'undefined') {
            $noAnswer = 0;
        }
        $isPrivat = (int)$input['isPrivat'];
        if (!$isPrivat || $isPrivat == 'undefined') {
            $isPrivat = 0;
        }
        $filesFolder = '';
        $files = (string)$input['files'];
        if (!$files || $files == 'undefined') {
            $files = 0;
        }


        /*
        if ($files && EXTENSION::isActive('ext.zwiebelgasse.fileshare') ) {
            include_once PATH_EXTENSIONS . 'fileshare' . DS . 'models' . DS . 'List.class.php';
            $FileShare = new extFileshareModelList();
            $fileshare = $FileShare->getByFolderID($files);

            if ($fileshare) {
                $filesFolder = $files;
            }
        }
        */

        include_once PATH_EXTENSION . 'models' . DS . 'Message2.class.php';
        $class = new extInboxModelMessage2();

        if (!$class->sendMessage([
            'receiver' => $receiver,
            'receivers_cc' => $receivers_cc,
            'sender_id' => $sender_id,
            'subject' => $subject,
            'text' => $text,
            'confirm' => $confirm,
            'priority' => $priority,
            'noAnswer' => $noAnswer,
            'isPrivat' => $isPrivat,
            'files' => $files
        ])) {
            return [
                'error' => true,
                'msg' => 'Fehler'
            ];
        }


        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Message.class.php';
        if ( !extInboxModelMessage::sendMessage($receiver,$receiver_cc, $sender_id, $subject, $text, $confirm, $priority) ) {
            return [
                'error' => true,
                'msg' => 'Fehler'
            ];
        }
        */


        return [
            'done' => true
        ];

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'POST';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }

    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth()
    {
        return false;
    }

}

?>