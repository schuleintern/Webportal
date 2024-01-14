<?php

/**
 *
 */
class extCckModelArticle
{

    /**
     * @var data []
     */
    private $data = [];

    private $fields = false;



    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->data = $data;
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getFormID() {
        return $this->data['form_id'];
    }
    public function getTitle() {
        return $this->data['title'];
    }
    public function getCreatedBy() {
        return $this->data['createdBy'];
    }
    public function getCreatedTime() {
        return $this->data['createdTime'];
    }
    public function getModifyBy() {
        return $this->data['modifyBy'];
    }
    public function getModifyTime() {
        return $this->data['modifyTime'];
    }


    public function getFields() {
        if ($this->fields == false) {
            $this->fields = self::getAllFields($this->getID());
        }
        return $this->fields;
    }


    public function getCollection($full = false, $renderTemplate = false) {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle(),
            "createdBy" => $this->getCreatedBy(),
            "createdTime" => $this->getCreatedTime(),
            "modifyBy" => $this->getModifyBy(),
            "modifyTime" => $this->getModifyTime(),
            "form_id" => $this->getFormID()
        ];
        if ($full) {
            $collection["fields"] = $this->getFields();
        }
        if ($renderTemplate) {
            $collection["template"] = $this->renderTemplate();
        }

        return $collection;
    }

    public function renderTemplate() {

        $fields = $this->getFields();

        $template = '';
        if ($this->getFormID()) {
            include_once PATH_EXTENSIONS.'cck'.DS.'models'.DS.'Form.class.php';
            $form = extCckModelForm::getByID( $this->getFormID() );
            $template = $form->getTemplate();
            if ($template) {
                foreach($fields as $field) {
                    if ($field['template']) {
                        $content = str_replace('{content}', $field['content'], $field['template']);
                    } else {
                        $content = $field['content'];
                    }
                    $template = str_replace('{'.$field['formfield_id'].'}', $content, $template);
                    //formfield_id field_id
                }
            }
        }
        return $template;
    }


    /**
     * @return Array[]
     */
    public static function getAll() {

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM cck_articles ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function getByID($id = false) {

        if (!(int)$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT * FROM cck_articles WHERE id = ".(int)$id, true);
        return new self($dataSQL);
    }

    public static function getAllFields($article_id = false) {

        if (!(int)$article_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT a.content, a.article_id, a.field_id,a.formfield_id , b.title, b.template
            FROM cck_content as a 
            LEFT JOIN cck_fieldtyp as b ON a.field_id = b.id
            WHERE a.article_id = ".(int)$article_id);

        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = $data;
        }
        return $ret;
    }



}