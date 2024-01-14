<?php


class sms {
    
    private static $netze = ['01511',
        '01512',
        '01514',
        '01515',
        '01516',
        '01517',
        '0160',
        '0170',
        '0171',
        '0175',
        '01520',
        '01522',
        '01523',
        '01525',
        '0162',
        '0172',
        '0173',
        '0174',
        '01570',
        '01573',
        '01575',
        '01577',
        '01578',
        '0163',
        '0177',
        '0178',
        '01590',
        '0176',
        '0179'
    ];
	
    private $recipient = "";
    private $text = "";
    
    private $apiKeyProduction = "MQPLeNY8mb3qrpr39R4wBhEFl";
    private $apiKeyTest = "I5pE6ZyiZxfcaZfBfwdc7ycZP";
    
    public function __construct($recipient, $text) {
        $this->recipient = $recipient;
        $this->text = $text;
    }
    
    public function send() {
        // Header: Authorization: AccessKey {accessKey}
        
        include_once("../framework/lib/sms/messagebirdlib/autoload.php");
        
        $MessageBird = new \MessageBird\Client(DB::isDebug() ? $this->apiKeyTest : $this->apiKeyProduction);
        
        $Message = new \MessageBird\Objects\Message();
        $Message->originator = '+491771783898';
        $Message->recipients = array($this->recipient);
        $Message->body = $this->text;
        
        $result = $MessageBird->messages->create($Message);
                
        if($result->recipients->items[0]->status == 'sent') {
            DB::getSettings()->setValue("sms-total-messages", DB::getSettings()->getValue("sms-total-messages") + 1);
            
            return true;
        }
        else return false;
    }
    
    /**
     * Überprüft, ob die SMS Funktionalität aktiviert ist.
     */
    public static function isActive() {
        return DB::getSettings()->getBoolean("sms-is-active");
    }
    
    public static function getTotalSMS() {
        return DB::getSettings()->getValue("sms-total-messages");
    }
    
    public static function getPricePerSMS() {
        return 0.08;
    }
	
    public static function getSenderNumber() {
        return "+49171000000";
    }
	
    public static function isMobilePhoneNumber($number) {
        $number = str_replace("/","",$number);
        $number = str_replace(" ", "", $number);
        
        
        if(substr($number, 0,1) == "+") {
            if(substr($number,1,2) != 49) return false;     // Keine Deutsche Nummer
            
            $number = substr($number, 3);
            if(substr($number,0,1) != "0") $number = "0" . $number;
        }
        
        for($i = 0; $i < sizeof(self::$netze); $i++) {
            if(substr($number,0,strlen(self::$netze[$i])) == self::$netze[$i]) {
                
                // Nummer mindestens 6 Zeichen:
                
                return strlen($number) >= (strlen(self::$netze[$i]) + 6);
            }
        }
                
        return false;
    }
    
    public static function isActiveForTeacher() {
        return DB::getSettings()->getBoolean("sms-active-teacher");
    }
    
    public static function isActiveForParents() {
        return DB::getSettings()->getBoolean("sms-active-parents");
    }
    
    public static function isActiveForStudents() {
        return DB::getSettings()->getBoolean("sms-active-students");
    }
}