<?php

class extInboxCronSendMails extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {

        if (DB::getGlobalSettings()->schulnummer != "9400") {
            $this->result = self::sendBatchMails();
        } else {
            $this->result = "Debug Modus. Keine Mails versendet.";
        }

    }

    public static function sendBatchMails() {

        include_once( PATH_EXTENSIONS.'inbox'.DS.'models'.DS.'Message2.class.php' );
        $class = new extInboxModelMessage2();
        $mails = $class->getAllUnreadUnsendMessages();
        $count = 0;
        foreach ($mails as $mail) {

            $data = $mail->getCollection(true,true,false,false,true);

            if ($data) {
                $data['email'] = false;
                if ( $data['inbox']['user'] && $data['inbox']['user']['email']) {
                    $data['email'] = $data['inbox']['user']['email'];
                }

                if ( $data['inbox']['user_id'] ) {
                    $user = user::getUserByID($data['inbox']['user_id']);
                    if ($user) {
                        $data['receiveEmail'] = $user->receiveEMail();
                    }
                }
            }

            if(DB::isDebug()) {
                $data['email'] = 'post@zwiebelgasse.de';
            }



            if ( email::sendEMail($data) ) {
                $mail->setSend();
                $count++;
            }

        }
        return $count;


    }


    public function getName()
    {
        return "Send unread Mails fron Inbox";
    }

    public function getDescription()
    {
        return "generate und send unread mails from inbox per email";
    }


    public function getCronResult()
    {
        return ['success' => $this->result > 0 , 'resultText' => $this->result . " Mails versendet."];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 300;
    }


}