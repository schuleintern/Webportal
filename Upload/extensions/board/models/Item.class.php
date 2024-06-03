<?php

class extBoardModelItem extends ExtensionModel
{

    static $table = 'ext_board_item';

    static $fields = [
        'id',
        'createdUserID',
        'createdTime',
        'state',
        'title',
        'board_id',
        'text',
        'pdf',
        'cover',
        'enddate'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, [
            'parent_id' => 'board_id'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $files = false)
    {

        $collection = parent::getCollection();

        $collection['textHTML'] = nl2br($collection['text']);

        if ($full) {

            if ($this->getData('board_id')) {
                include_once PATH_EXTENSION . 'models' . DS . 'Board.class.php';
                $class = new extBoardModelBoard();
                $tmp_data = $class->getByID($this->getData('board_id'));
                $collection['boardTitle'] = $tmp_data->getData('title');
            }
        }
        if ($files) {
            $collection['pdfURL'] = FILE::makeThumb($collection['pdf'], 'board_' . $collection['board_id'] . '_pdf_' . $collection['id'], 'ext_board');
            $collection['coverURL'] = FILE::makeThumb($collection['cover'], 'board_' . $collection['board_id'] . '_cover_' . $collection['id'], 'ext_board');
        }
        return $collection;
    }

    public function deleteAll()
    {

        $item = $this->getByID($this->getID());
        if ($item) {
            $cover = $item->getData('cover');
            if ($cover && file_exists($cover)) {
                unlink($cover);
            }

            $pdf = $item->getData('pdf');
            if ($pdf && file_exists($pdf)) {
                unlink($pdf);
            }
        }

        if ( $this->delete() ) {
            return true;
        }
        return false;
    }


}
