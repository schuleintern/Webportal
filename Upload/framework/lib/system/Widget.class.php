<?php

/**
 *
 */
class Widget
{

    private $data = false;

    /**
     * Constructor
     */
    public function __construct($data = false)
    {
        if ($data) {
            $this->data = $data;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function render()
    {
        return '';
    }

    public function getScripts()
    {
        return false;
    }

}