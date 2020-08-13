<?php

/**
 * Ein einzelnes ToDo
 * @author Christian Spitschka
 *
 */
class ToDo {
    private $data = [];
    
    private $flags = [];
    
    private $title = "";
    private $dueDate = null;
    private $priority = 0;  // -1, 1: low / high
    private $comment = "";
    
    /**
     * 
     * @var FileUpload
     */
    private $fileAttachment = null;
       
    public function __construct($data) {
        $this->data = $data;
    }


    /**
     * 
     */
    public static function isActive() {
        return AbstractPage::isActive('ToDoManagement');
    }
    
    
}