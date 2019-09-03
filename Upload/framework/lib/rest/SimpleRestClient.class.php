<?php


class SimpleRestClient {
    
    /**
     *
     * @param unknown $url
     * @param unknown $method
     * @param unknown $path
     * @param unknown $data
     * @param unknown $user
     * @param unknown $password
     * @return mixed[]|unknown[]
     */
    public static function doRESTApiCall($url, $method, $path, $data, $addheaders = [], $user = NULL, $password = NULL) {
        
        $query = json_encode($data);
        
        $addheaders[] = "schuleinternapirequest: true";
        
        $process = curl_init($url . "/" . $path);
        
        // curl_setopt($process, CURLOPT_HEADER, 0);
        
        if(sizeof($addheaders) > 0) {
            curl_setopt($process, CURLOPT_HTTPHEADER, $addheaders);
        }

        if($user != NULL) {
            curl_setopt($process, CURLOPT_USERPWD, $user . ":" . $password);
        }
        
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        
        if($method == "POST") curl_setopt($process, CURLOPT_POST, true);
        else if($method == "PUT") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
        else if($method == "DELETE") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        else if($method == "GET") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
        else if($method == "PATCH") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");
        else die("No valid Method: " . $method);
        
        
        curl_setopt($process, CURLOPT_POSTFIELDS, $query);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        
        $return = curl_exec($process);
        
        $statusCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
        
        curl_close($process);
        
        
        return [
            'statusCode' => $statusCode,
            'data' => json_decode($return)
        ];
    }
}