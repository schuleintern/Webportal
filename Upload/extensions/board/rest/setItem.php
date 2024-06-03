<?php

class setItem extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];

        $state = (int)$input['state'];
        if ( !$state ) {
            $state = 0;
        }
        $title = (string)$input['title'];
        if ( !$title ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        $board_id = (int)$input['board_id'];
        if ( !$board_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Board'
            ];
        }

        $text = trim((string)$input['text']);
        $pdf = trim((string)$input['pdf']);
        $cover = trim((string)$input['cover']);
        $enddate = $input['enddate'];
        if ( !$enddate ) {
            $enddate = NULL;
        }

        include_once PATH_EXTENSION . 'models' . DS .'Item.class.php';
        $class = new extBoardModelItem();

        $data = [
            'id' => $id,
            'state' => $state,
            'title' => $title,
            'board_id' => $board_id,
            'createdTime' => date('Y-m-d', time()),
            'createdUserID' => $user->getUserID(),
            'text' => $text,
            'enddate' => $enddate
        ];

        if ($id) {
            $db = $class->getByID($id);
            $oldPdf = $db->getData('pdf');
            $oldCover = $db->getData('cover');
        }


        $newPdf = $class->uploadMoveOrDelete($pdf, $oldPdf, $board_id);
        if ( $newPdf !== false ) {
            $data['pdf'] = $newPdf;
        } else {
            return [
                'error' => true,
                'msg' => 'Nicht Erfolgreich! - Upload PDF'
            ];
        }

        $newCover = $class->uploadMoveOrDelete($cover, $oldCover, $board_id);
        if ( $newCover !== false ) {
            $data['cover'] = $newCover;
        } else {
            return [
                'error' => true,
                'msg' => 'Nicht Erfolgreich! - Upload Cover'
            ];
        }



        if ( $class->save($data) ) {

            return [
                'success' => true
            ];

        }


        return [
            'error' => true,
            'msg' => 'Nicht Erfolgreich!'
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