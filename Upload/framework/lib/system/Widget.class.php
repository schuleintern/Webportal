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
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function render() {
        return '';
    }

}