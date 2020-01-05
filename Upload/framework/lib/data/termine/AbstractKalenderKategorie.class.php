<?php

abstract class AbstractKalenderKategorie {
    protected $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public abstract function getID();
    
    public abstract function getKalenderID();
    
    public abstract function getKategorieName();
    
    public abstract function getFarbe();
    
    public abstract function getIcon();
}

