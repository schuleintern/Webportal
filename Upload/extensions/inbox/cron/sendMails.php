<?php

class extInboxCronSendMails extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {

        if (!DB::isSchulnummern(9400)) {
            $this->result = self::sendBatchMails();
        } else {
            $this->result = "Debug Modus. Keine Mails versendet.";
        }

    }

    public static function sendBatchMails() {


        $maxCount = (int)DB::getSettings()->getValue("extInbox-cron-sendBatchMailCount");
        if (!$maxCount) {
            $maxCount = 20;
        }

        include_once( PATH_EXTENSIONS.'inbox'.DS.'models'.DS.'Message2.class.php' );
        $class = new extInboxModelMessage2();
        $mails = $class->getAllUnreadUnsendMessages();
        $count = 0;
        foreach ($mails as $mail) {

            if ($count < $maxCount) {
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


                // Prepare HTML MAIL Body
                $mailTmp = file_get_contents(PATH_EXTENSIONS.'inbox'.DS.'tmpl'.DS.'sendMail.tmpl.php');

                $logo = str_replace("index.php", '' , str_replace("./",DB::getGlobalSettings()->urlToIndexPHP , PAGE::logo()));
                $mailTmp = str_replace("{LOGO}", $logo, $mailTmp);
                $mailTmp = str_replace("{SKINCOLOR}", DB::getSkinColor(), $mailTmp);
                $mailTmp = str_replace("{SITENAME}", DB::getGlobalSettings()->siteNamePlain, $mailTmp);
                $mailTmp = str_replace("{BODY}", $data['text'], $mailTmp);
                $mailTmp = str_replace("{SUBJECT}", $data['subject'], $mailTmp);
                $mailTmp = str_replace("{SENDER}", $data['from']['title'], $mailTmp);
                $to = [];
                foreach ($data['to'] as $foo ) {
                    $to[] = $foo['title'];
                }
                $mailTmp = str_replace("{EMPFAENGERS}", join(', ', $to), $mailTmp);
                $mailTmp = str_replace("{EMPFAENGER}", $data['inbox']['title'], $mailTmp);

                if ($data['files']) {
                    $mailTmp = str_replace("{FILES}", 'Die Nachricht enthält einen Dateianhang.', $mailTmp);
                } else {
                    $mailTmp = str_replace("{FILES}", '', $mailTmp);
                }
                if ($data['isConfirm']) {
                    $mailTmp = str_replace("{CONFIRM}", 'Der Empfang dieser Nachricht muss bestätigt werden.', $mailTmp);
                } else {
                    $mailTmp = str_replace("{CONFIRM}", '', $mailTmp);
                }

                $replyLink = DB::getGlobalSettings()->urlToIndexPHP."?page=ext_inbox&iid=".$data['inbox_id']."&mid=".$data['id'];
                $mailTmp = str_replace("{REPLAYLINK}", $replyLink, $mailTmp);
                $mailTmp = str_replace("{PORTALLINK}", DB::getGlobalSettings()->urlToIndexPHP, $mailTmp);

                $impressumText = DB::getSettings()->getValue("impressum-text");
                $mailTmp = str_replace("{IMPRESSUM}", $impressumText, $mailTmp);

                $data['body'] = $mailTmp;


                if ( email::sendEMail($data) ) {
                    $mail->setSend();
                    $count++;
                }

            }

        }
        return $count;


    }


    public function getName()
    {
        return "E-Mails versenden (Inbox)";
    }

    public function getDescription()
    {
        return "Versendet die ungelesenen Nachrichten als E-Mail";
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