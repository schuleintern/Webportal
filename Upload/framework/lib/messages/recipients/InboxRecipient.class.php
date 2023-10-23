<?php


class InboxRecipient extends MessageRecipient {


    public function getDisplayName()
    {
        // TODO: Implement getDisplayName() method.
    }

    public function getSaveString()
    {
        // TODO: Implement getSaveString() method.
    }

    public function getRecipientUserIDs()
    {
        // TODO: Implement getRecipientUserIDs() method.
    }

    public static function getAllInstances()
    {
        $groups = usergroup::getAllOwnGroups();


        $myContactGroups = [];

        for($i = 0; $i < sizeof($groups); $i++) {
            if($groups[$i]->isMessageRecipient()) {
                $suc = false;

                if(DB::getSession()->isAdmin()) {
                    $suc = true;
                }

                if(DB::getSession()->isMember('Webportal_Elternmail')) $suc = true;

                if(DB::getSession()->isEltern() && $groups[$i]->canContactByParents()) $suc = true;

                if(DB::getSession()->isTeacher() && $groups[$i]->canContactByTeacher()) $suc = true;

                if(DB::getSession()->isPupil() && $groups[$i]->canContactByPupil()) $suc = true;

                // if($groups[$i]->isMember(DB::getSession()->getUser())) $suc = true;


                if($suc) {
                    $myContactGroups[] = new GroupRecipient($groups[$i]->getName());
                }
            }
        }


        return $myContactGroups;
    }

    public static function isSaveStringRecipientForThisRecipientGroup($saveString)
    {
        // TODO: Implement isSaveStringRecipientForThisRecipientGroup() method.
    }

    public function getMissingNames()
    {
        // TODO: Implement getMissingNames() method.
    }

    public static function getInstanceForSaveString($saveString)
    {
        // TODO: Implement getInstanceForSaveString() method.
    }
}

