<?php

class delBoard extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();

        if (!$this->canDelete()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];
        if (!$id) {
            return [
                'error' => true,
                'msg' => 'Missing Data: id'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Board.class.php';
        $class = new extBoardModelBoard([
            'id' => $id
        ]);

        include_once PATH_EXTENSION . 'models' . DS . 'Item.class.php';
        $Item = new extBoardModelItem();

        $items = $Item->getByParentID($id);
        if ($items) {
            foreach ($items as $item) {

                $classDel = new extBoardModelItem([
                    'id' => $item->getID()
                ]);

                if ( !$classDel->deleteAll() ) {
                    return [
                        'error' => true,
                        'msg' => 'Error Item'
                    ];
                }
            }
        }

        if ($class->delete()) {

            return [
                'success' => true
            ];
        }

        return [
            'error' => true,
            'msg' => 'Error'
        ];

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'POST';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }

    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth()
    {
        return false;
    }

}

?>