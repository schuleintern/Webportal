<?php

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Globale PUSH class
 *
 * @author: Christian Marienfeld
 */
class PUSH
{


    public static function subscribe($sub, $uid)
    {

        if (DB::getDB()->query("UPDATE users SET 
                         userPush='" . DB::getDB()->escapeString((string)$sub) . "'
                         WHERE userID=" . (int)$uid)) {
            return true;
        }
        return false;

    }

    /**
     * @author: Christian Marienfeld
     *
     */

    public static function send($user_id)
    {

        if (DB::getSettings()->getBoolean('global-push-active') != true) {
            return false;
        }

        $userSub = DB::getDB()->query_first("SELECT userPush FROM users WHERE userID='" . (int)$user_id . "'");

        if ($userSub && $userSub['userPush']) {
            $arr = json_decode($userSub['userPush'], true);
            if ($arr && is_array($arr)) {
                // (B) GET SUBSCRIPTION
                $sub = Subscription::create($arr);

                $publicKey = DB::getSettings()->getValue('global-push-publicKey');
                $privateKey = DB::getSettings()->getValue('global-push-privateKey');

                if ($publicKey && $privateKey) {


                    // (C) NEW WEB PUSH OBJECT - CHANGE TO YOUR OWN!
                    $push = new WebPush(["VAPID" => [
                        "subject" => "support@schule-intern.de",
                        "publicKey" => $publicKey,
                        "privateKey" => $privateKey
                    ]]);


                    // (D) SEND TEST PUSH NOTIFICATION
                    $result = $push->sendOneNotification($sub, json_encode([
                        "title" => "Schule-Intern",
                        "body" => "Neue Nachricht",
                        "icon" => "i-loud.png",
                        "image" => "i-zap.png"
                    ]));
                    $endpoint = $result->getRequest()->getUri()->__toString();

                    // (E) SHOW RESULT - OPTIONAL
                    if ($result->isSuccess()) {
                        //echo "Successfully sent {$endpoint}.";
                        return true;
                    } else {
                        return false;
                        //echo "Send failed {$endpoint}: {$result->getReason()}";
                        // $result->getRequest();
                        // $result->getResponse();
                        // $result->isSubscriptionExpired();
                    }
                }
            }
        }

        return false;
    }

}


?>