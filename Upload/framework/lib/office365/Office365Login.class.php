<?php

class Office365Login {

    /**
     * Triggert den Login und liefert die employeeID
     * @return string employeeId
     */
    public static function triggerLogin() {

        session_start();

        $provider = new TheNetworg\OAuth2\Client\Provider\Azure([
            'clientId'          => DB::getSettings()->getValue("office365-single-sign-on-app-id"),
            'clientSecret'      => DB::getSettings()->getValue("office365-single-sign-on-app-secret"),
            'redirectUri'       => DB::getGlobalSettings()->urlToIndexPHP . "?page=oAuth2Auth"
        ]);

        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid oauth state!');
        } else {

            try{
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code'],
                    'resource' => 'https://graph.windows.net',
                ]);
            } catch(Exception $e) {
                session_destroy();
                new errorPage("Keine Erfolgreiche Anmeldung bei Office365!");
            }

            try {
                $me = $provider->get("me?\$select=employeeId,userPrincipalName", $token);

                return [
                    'asvID' => $me['employeeId'],
                    'username' => $me['userPrincipalName']
                ];


            } catch (Exception $e) {
                return null;
            }

            return null;
        }
    }



}