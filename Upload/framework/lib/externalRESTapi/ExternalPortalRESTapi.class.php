<?php


class ExternalPortalRESTapi {
    
    public static function getCurlContext($method, $hostandpath, $data, $user = NULL, $password = NULL) {
        
        if(sizeof($data) > 0) $query = json_encode($data);
        else $query = "";
                
        $process = curl_init($hostandpath);
        
        curl_setopt($process, CURLINFO_HEADER_OUT , true);
        
        curl_setopt($process, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . (strlen($query))
            )
        );
        
        curl_setopt($process, CURLOPT_HEADER, false);
        
        if($user != NULL) curl_setopt($process, CURLOPT_USERPWD, $user . ":" . $password);
        
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        
        if($method == "POST") curl_setopt($process, CURLOPT_POST, true);
        else if($method == "PUT") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
        else if($method == "DELETE") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        else if($method == "GET") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
        else if($method == "PATCH") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");
        
        curl_setopt($process, CURLOPT_POSTFIELDS, $query);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        
        echo curl_getinfo($process, CURLINFO_HEADER_OUT);
        
        
        $return = curl_exec($process);
        
        
        
        $statusCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
                      
        curl_close($process);

        
        return [
            'hostandpath' => $hostandpath,
            'method' => $method,
            'data' => $data,
            'query' => $query,
            'statusCode' => $statusCode,
            'data' => json_decode($return),
            'rawdata' => $return
        ];
    }
}