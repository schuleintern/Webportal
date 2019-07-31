<?php
class MessageAnswer {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getID() {
        return $this->data['answerID'];
    }

    public function getQuestionID() {
        return $this->data['answerQuestionID'];
    }

    public function getAnswerData() {
        return $this->data['answerData'];
    }

    public function getMessageID() {
        return $this->data['answerMessageID'];
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM messages_questions_answers WHERE answerID='" . $this->getID() . "'");
    }
    
    public function updateAnswer($data) {
        DB::getDB()->query("UPDATE messages_questions_answers SET answerData='" . DB::getDB()->escapeString($data) . "' WHERE answerID='" . $this->getID() . "'");
    }

    /**
     * 
     * @param int $id
     * @param int $questionID
     * @return MessageAnswer|NULL
     */
    public static function getByMessageIDAndQuestionID($id, $questionID) {
        $data = DB::getDB()->query_first("SELECT * FROM messages_questions_answers WHERE answerMessageID='" . DB::getDB()->escapeString($id) . "' AND messageQuestionID='" . DB::getDB()->escapeString($questionID) . "'");
        if($data['answerID'] > 0) return new MessageAnswer($data);
        return null;
    }

    /**
     * 
     * @param int $id
     * @return MessageAnswer[]
     */
    public static function getByQuestionID($id) {
        $all = [];

        $data = DB::getDB()->query("SELECT * FROM messages_questions_answers WHERE answerQuestionID='" . DB::getDB()->escapeString($id) . "'");

        while($d = DB::getDB()->fetch_array($data)) {
            $all[] = new MessageAnswer($d);
        }
                
        return $all;
    }
    
    /**
     *
     * @param int $id
     * @return MessageAnswer[]
     */
    public function getByMessageID($id) {
        $all = [];
        
        $data = DB::getDB()->query("SELECT * FROM messages_questions_answers WHERE answerMessageID='" . DB::getDB()->escapeString($id) . "'");
        
        while($d = DB::getDB()->fetch_array($data)) {
            $all[] = new MessageAnswer($d);
        }
        
        return $all;
    }
    
    public static function createAnswer($questionID, $messageID, $data) {
        DB::getDB()->query("INSERT INTO messages_questions_answers (answerQuestionID, answerMessageID, answerData) values('" . $questionID . "','" . $messageID . "','" . DB::getDB()->escapeString($data) . "')");
    }
}

