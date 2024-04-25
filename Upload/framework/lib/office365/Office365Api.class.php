<?php

class Office365Api {
    private static function getBearer() {
        $tenant = DB::getSettings()->getValue('office365-tenant');

        $url = 'https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/token';

        $currentBearer = DB::getSettings()->getValue('office365-bearer');
        $currentBearerLifeTime = DB::getSettings()->getValue('office365-bearer-lifetime');

        if($currentBearerLifeTime > time()) return $currentBearer;
        https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/token

        // Neuen Bearer anfordern


        $process = curl_init($url);

        curl_setopt($process, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/x-www-form-urlencoded'
            )
        );

        curl_setopt($process, CURLOPT_TIMEOUT, 30);

        curl_setopt($process, CURLOPT_POST, true);

        $postData = http_build_query(
            [
                'client_id' => DB::getSettings()->getValue('office365-app-id'),
                'scope' => 'https://graph.microsoft.com/.default',
                'client_secret' => DB::getSettings()->getValue('office365-app-secret'),
                'grant_type' => 'client_credentials'
            ]
        );

        curl_setopt($process, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

        $return = json_decode(curl_exec($process));

        $statusCode = curl_getinfo($process, CURLINFO_HTTP_CODE);

        if($return->token_type == 'Bearer') {
            DB::getSettings()->setValue('office365-bearer', $return->access_token);
            DB::getSettings()->setValue('office365-bearer-lifetime', time() + $return->expires_in);
        }

        return $return->access_token;

    }

    public static function getAllUsers() {

       // @odata.nextLink

        $completeData = [];

        $data = self::getCurlContext('GET', 'v1.0/users?$select=displayName,userPrincipalName,department&$top=999', []);

        for($i = 0; $i < sizeof($data['data']->value); $i++) {
            $completeData[] = $data['data']->value[$i];
        }

        // Mehr als 20.000 Benutzer pro Schule unwahrscheinlich.
        for($i = 0; $i < 20; $i++) {
            if($data['data']->{'@odata.nextLink'} != '') {
                $link = $data['data']->{'@odata.nextLink'};
                $link = str_replace("https://graph.microsoft.com/","",$link);

                $data = self::getCurlContext('GET', $link, []);

                for($b = 0; $b < sizeof($data['data']->value); $b++) {
                    $completeData[] = $data['data']->value[$b];
                }
            }
        }


        return $completeData;
    }

    public static function getTermine($username) {
        $completeData = [];
        
        $data = self::getCurlContext('GET', 'v1.0/users/' . $username . '/events?$top=1000', [], [
            'Prefer: outlook.timezone="W. Europe Standard Time"',
            'Prefer: outlook.body-content-type="text"'            
        ]);
        

        for($i = 0; $i < sizeof($data['data']->value); $i++) {
            $completeData[] = $data['data']->value[$i];
        }
        
        
        for($i = 0; $i < 999; $i++) {
            if($data['data']->{'@odata.nextLink'} != '') {
                $link = $data['data']->{'@odata.nextLink'};
                $link = str_replace("https://graph.microsoft.com/","",$link);
                
                $data = self::getCurlContext('GET', $link, []);
                
                for($b = 0; $b < sizeof($data['data']->value); $b++) {
                    $completeData[] = $data['data']->value[$b];
                }
            }
            else break;
        }
        
        
        return $completeData;
    }
    
    
    public static function getAllDomains() {
        $data =  self::getCurlContext('GET', 'V1.0/domains', []);


        $domains = [];

        for($i = 0; $i < sizeof($data['data']->value); $i++) {
            if($data['data']->value[$i]->isVerified > 0) {
                $domains[] = $data['data']->value[$i]->id;
            }
        }

        return $domains;
    }

    public static function getLicenseStatus() {
        $lizenzen = [];


        $data = self::getCurlContext('GET', 'v1.0/subscribedSkus', []);


        if ($data['data']->value) {
            for($i = 0; $i < sizeof($data['data']->value); $i++) {
                $l = $data['data']->value[$i];

                if($l->capabilityStatus == 'Enabled') {
                    $lizenzen[] = [
                        'name' => $l->skuPartNumber,
                        'availible' => $l->prepaidUnits->enabled,
                        'consumed' => $l->consumedUnits,
                        'id' => $l->skuId
                    ];
                }
            }
        }

        return $lizenzen;
    }


    public static function getCurlContext($method, $path, $data, $contentType = 'application/x-www-form-urlencoded', $addHeader = []) {
        $query = json_encode($data);

        // URL : https://graph.microsoft.com/

        // application/json

        $process = curl_init('https://graph.microsoft.com/' . $path);
        
        $header =             array(
            'Content-Type: ' . $contentType,
            'Authorization: Bearer ' . self::getBearer()
        );
        
        for($i = 0; $i < sizeof($addHeader); $i++) {
            $header[] = $addHeader[$i];
        }

        curl_setopt($process, CURLOPT_HTTPHEADER,
            $header
        );

        // curl_setopt($process, CURLOPT_HEADER, 0);

        curl_setopt($process, CURLOPT_TIMEOUT, 30);

        if($method == "POST") curl_setopt($process, CURLOPT_POST, true);
        else if($method == "PUT") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
        else if($method == "DELETE") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        else if($method == "GET") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
        else if($method == "PATCH") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");

        curl_setopt($process, CURLOPT_POSTFIELDS, $query);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

        $return = curl_exec($process);

        $statusCode = curl_getinfo($process, CURLINFO_HTTP_CODE);

        curl_close($process);


        return [
            'statusCode' => $statusCode,
            'data' => json_decode($return)
        ];
    }

    public static function getGroup($groupID) {
        $answer = self::getCurlContext('GET', 'v1.0/groups/' . $groupID, []);
        return $answer;
    }

    public static function getGroups() {
        return self::getCurlContext('GET', 'v1.0/groups?$top=999', []);
    }
    
    public static function getGroupID($name) {
        $gruppen = self::getGroups();
        
        for($i = 0; $i < sizeof($gruppen['data']->value); $i++) {
            if($gruppen['data']->value[$i]->displayName == $name) {
                return $gruppen['data']->value[$i]->id;
            }
        }
        
        return null;
    }

    public static function createGroup($groupName, $groupDesc) {
        return self::getCurlContext('POST', 'v1.0/groups', [
            'description' => $groupDesc,
            'displayName' => $groupName,
            'mailEnabled' => false,
            'mailNickname' => $groupName,
            'securityEnabled' => true
        ], 'application/json');
    }

    public static function getGroupMembers($groupID) {
        // die('v1.0/groups/' . $groupID . "/members"); // ')

        
        
        $data = self::getCurlContext('GET', 'v1.0/groups/' . $groupID . "/members", []);
                        
        $completeData = [];
        
        for($i = 0; $i < sizeof($data['data']->value); $i++) {
            $completeData[] = $data['data']->value[$i];
        }
        
        
        for($i = 0; $i < 10; $i++) {
            if($data['data']->{'@odata.nextLink'} != '') {
                $link = $data['data']->{'@odata.nextLink'};
                $link = str_replace("https://graph.microsoft.com/","",$link);
                
                $data = self::getCurlContext('GET', $link, []);
                
                for($b = 0; $b < sizeof($data['data']->value); $b++) {
                    $completeData[] = $data['data']->value[$b];
                }
            }
        }
        
        
        return $completeData;
    }

    public static function createUser($userName, $userDomain, $password, $displayName, $firstName, $lastName, $asvID) {

        $results = self::getCurlContext('POST', 'v1.0/users', [
            'accountEnabled' => true,
            'displayName' => $displayName,
            'mailNickname' => $userName,
            'passwordProfile' => [
                'forceChangePasswordNextSignIn' => false,
                'password' => $password
            ],
            'employeeID' => $asvID,
            'userPrincipalName' => $userName . '@' . $userDomain,
            'givenName' => $firstName,
            'surname' => $lastName,
        ],'application/json');

        return $results;

    }
    
    public static function deleteUser($userName, $userDomain) {
        $results = self::getCurlContext('DELETE', 'v1.0/users/' . $userName . "@" . $userDomain, [],'application/json');
    
        return $results;    
    }
    
    public static function setAsvID($userName, $userDomain, $asvID) {
        $results = self::getCurlContext('PATCH', 'v1.0/users/' . $userName . "@" . $userDomain, [
            'schools' => [$asvID]
        ],'application/json');
        
        return $results;
    }
    
    public static function setUsageLocation($userName, $userDomain, $usageLocation) {
        $results = self::getCurlContext('PATCH', 'v1.0/users/' . $userName . "@" . $userDomain, [
            'usageLocation' => $usageLocation
        ],'application/json');
        
        return $results;
    }
    
    public static function getUserInfo($userName, $userDomain) {
        $results = self::getCurlContext('GET', 'v1.0/users/' . $userName . "@" . $userDomain . '?$select=displayName,givenName,schools', []);
        
        return $results;
    }
    
    public static function addUserToGroup($userName, $userDomain, $groupID) {
        $result = self::getCurlContext('POST', 'v1.0/groups/' . $groupID . '/members/$ref',
            [
                "@odata.id" => "https://graph.microsoft.com/v1.0/users/" . $userName . '@' . $userDomain
            ]
        , 'application/json');
        
        // /users/{id | userPrincipalName}
        
        return $result;
    }

    /**
     * 
     * @param String $userName
     * @param String $userDomain
     * @param String $licenses
     * @return String[] $licenses
     */
    public static function addLicensesToUser($userName, $userDomain, $licenses) {
       
        self::setUsageLocation($userName, $userDomain, "DE");
        
        $plans = [];
        
        for($i = 0; $i < sizeof($licenses); $i++) {
            $plans[] = [
                'disabledPlans' => [],
                'skuId' => $licenses[$i]
            ];
        }
        
        $result = self::getCurlContext('POST', 'v1.0/users/' . $userName . "@" . $userDomain . "/assignLicense", [
            'addLicenses' => $plans,
            'removeLicenses' => []
        ], 'application/json');
        
        return $result;
    }
    
    

    /**
     * Bereitet die Office 365 Installation vor.
     */
    public static function prepapreOffice365() {

        if(DB::getSettings()->getValue('office365-schueler-groupid') == "") {
            $newGroup = self::createGroup('SchuleIntern_Schueler', 'In dieser Gruppe befinden sich alle Schüler, die von SchuleIntern automatisch erstellt wurden.');
            if($newGroup['statusCode'] == 201) DB::getSettings()->setValue('office365-schueler-groupid', $newGroup['data']->id);
        }

        if(DB::getSettings()->getValue('office365-lehrer-groupid') == "") {
            $newGroup = self::createGroup('SchuleIntern_Lehrer', 'In dieser Gruppe befinden sich alle Lehrer, die von SchuleIntern automatisch erstellt wurden.');
            if($newGroup['statusCode'] == 201) DB::getSettings()->setValue('office365-lehrer-groupid', $newGroup['data']->id);
        }
        
    }


    /**
     * Erstellt ein Meeting beim Nutzer im Kalender und liefert die Join URL für Externe zurück.
     * @param $upn
     * @param $dateTimeStart
     * @param $dateTimeEnd
     * @param $subject
     * @return string|null
     *
     */
    public static function createMeeting($upn, $dateTimeStart, $dateTimeEnd, $subject, $body="") {

        if(!Office365Meetings::isActiveForTeacher()) return null;

        $plans = [];

        $result = self::getCurlContext('POST', 'v1.0/users/' . $upn . '/calendar/events', [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body
            ],
            'start' => [
                'dateTime' => $dateTimeStart,                       // "2017-04-15T12:00:00"
                'timeZone' => 'Europe/Berlin'
            ],
            'end' => [
                'dateTime' => $dateTimeEnd,
                'timeZone' => 'Europe/Berlin'
            ],
            'allowNewTimeProposals' => false,
            'isOnlineMeeting' => true,
            'onlineMeetingProvider' => 'teamsForBusiness'
        ], 'application/json');

        if($result['statusCode'] == 201) {
            return $result['data']->onlineMeeting->joinUrl;
        }
        else return null;
    }
}
