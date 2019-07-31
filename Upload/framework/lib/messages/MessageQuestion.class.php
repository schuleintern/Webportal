<?php
class MessageQuestion {
    private static $cache = [];
    private $data;
    
    private $allAnswers = null;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getID() {
        return $this->data['questionID'];
    }

    public function getQuestionText() {
        return $this->data['questionText'];
    }

    public function getQuestionType() {
        return $this->data['questionType'];
    }

    public function isTextQuestion() {
        return $this->data['questionType'] == 'TEXT';
    }

    public function isBooleanQuestion() {
        return $this->data['questionType'] == 'BOOLEAN';
    }

    public function isNumberQuestion() {
        return $this->data['questionType'] == 'NUMBER';
    }

    public function isFileQuestion() {
        return $this->data['questionType'] == 'FILE';
    }
    
    public function getUserID() {
        return $this->data['questionUserID'];
    }
    
    public function getSecret() {
        return $this->data['questionSecret'];
    }
    
    public function getQuestionTypeAsText() {
        switch($this->data['questionType']) {
            case 'TEXT': return 'Textfrage';
            case 'BOOLEAN': return 'Ja / Nein Frage';
            case 'NUMBER': return 'Ganze Zahl Frage';
            case 'FILE': return 'Dateiabfrage';
        }
    }

    /**
     * 
     * @param Message $message
     */
    public function getAnswer($message) {
        return MessageAnswer::getByMessageIDAndQuestionID($message->getID(), $this->getID());
    }

    public function getAllAnswers() {
        if($this->allAnswers != null) return $this->allAnswers;
        else {
            $this->allAnswers = MessageAnswer::getByQuestionID($this->getID());
            return $this->allAnswers;
        }   
    }

    /**
     *
     * @param int[] $ids
     * @return MessageQuestion[]
     */
    public static function getByIDs($ids) {

        $questions = [];

        for($i = 0; $i < sizeof($ids); $i++) {
            if(self::$cache[$ids[$i]] != null) {
                $questions[] = self::$cache[$ids[$i]];
            }
            else {
                $q = self::getByID($ids[$i]);
                if($q != null) {
                    $questions[] = $q;
                }
            }
        }

        return $questions;
    }

    /**
     * 
     * @param unknown $id
     * @return MessageQuestion|NULL
     */
    public static function getByID($id) {
        $data = DB::getDB()->query_first("SELECT * FROM messages_questions WHERE questionID='" . DB::getDB()->escapeString($id) . "'");
        if($data['questionID'] > 0) {
            self::$cache[$id] = new MessageQuestion($data);
            return self::$cache[$id];
        }
                
        return null;
    }
    
    /**
     * 
     * @param String $question
     * @param String $type
     * @return MessageQuestion neue Nachricht
     */
    public static function createQuestion($question, $type) {
        DB::getDB()->query("INSERT INTO messages_questions (questionText, questionType, questionUserID, questionSecret) values('" . DB::getDB()->escapeString($question) . "','" . DB::getDB()->escapeString($type) . "','" . DB::getSession()->getUserID() . "','" . substr(md5(rand()),0,5) . "')");
        $newID = DB::getDB()->insert_id();
        
        return MessageQuestion::getByID($newID);
    }
}

