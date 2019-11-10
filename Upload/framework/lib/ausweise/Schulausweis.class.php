<?php

class Schulausweis extends AbstractAusweis {
    /**
     * {@inheritDoc}
     * @see AbstractAusweis::getAusweisPDFFront()
     */
    public function getAusweisPDFFront() {
        
        $pdf = new SchulausweisTCPDF($this, true, false);
        return $pdf;
    }

    /**
     * {@inheritDoc}
     * @see AbstractAusweis::getAusweisPDFBack()
     */
    public function getAusweisPDFBack() {
        $pdf = new SchulausweisTCPDF($this, false, true);
        return $pdf;
    }

    /**
     * {@inheritDoc}
     * @see AbstractAusweis::getGueltigkeitForNewAusweis()
     */
    public function getGueltigkeitForNewAusweis($type) {
        
        if($type == 'SCHUELER' || $type == 'LEHRER' || $type == 'MITARBEITER') {
            // 2 Jahre // Ende des nächsten Schuljahres
                        
            
            $schuljahr = DB::getSettings()->getValue('general-schuljahr');
            
            $jahr = explode("/",$schuljahr);
            
            $jahr = $jahr[1]+1;
            
            $datum = ($jahr+2000) . "-07-31";
            
            
            return $datum;
            
        }
        
        
        if($type == 'GAST') {
            // 14 Tage
            
            $datum = time();
            
            $datum += (14 * 24 * 60 * 60);
            
            return date("Y-m-d",$datum);
        }
        

        return date("Y-m-d");
        
        
    }

    
}