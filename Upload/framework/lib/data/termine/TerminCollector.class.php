<?php

/**
 * Sammelt Termine zusammen, um sie auf einem Kalender oder per iCAL zu liefern.
 * @author Christian Spitschka
 *
 */
class TerminCollector {
    private $startDate = "";
    private $endDate = "";
    
    private $collectorSettings = [
        'externeKalender' => [],
        'klassenkalender' => [],
        'andereKalender' => []
    ];
    
    public function __construct($startDate, $endDate) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    /**
     * Einstellungen kommen z.B. aus einer JSON Datei
     * @param unknown $settings
     */
    public function setCollectorSettings($settings) {
        $this->collectorSettings = $settings;
    }
    
    private function collectLNWs() {
        
    }
    
    public function getTotalJSON() {
        
    }
    
    public function getTotalICAL() {
        
    }
}

