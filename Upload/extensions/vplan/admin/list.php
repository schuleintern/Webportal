<?php



class extVplanAdminList extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-upload"></i> Vertretungsplan - Liste';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        //$this->getAcl();
        //echo URL_SELF;

        include_once PATH_EXTENSIONS . 'vplan'.DS.'models' . DS . 'List.class.php';

        $userTyp = DB::getSession()->getUser()->getUserTyp(true);

        $show = [];
        if ( extVplanModelList::aclRule('extVplan-col-show-klasse', $userTyp) ) {
            $show['klasse'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-user_alt', $userTyp) ) {
            $show['user_alt'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-user_neu', $userTyp) ) {
            $show['user_neu'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-fach_alt', $userTyp) ) {
            $show['fach_alt'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-fach_neu', $userTyp) ) {
            $show['fach_neu'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-raum_alt', $userTyp) ) {
            $show['raum_alt'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-raum_neu', $userTyp) ) {
            $show['raum_neu'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-info_1', $userTyp) ) {
            $show['info_1'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-info_2', $userTyp) ) {
            $show['info_2'] = true;
        }
        if ( extVplanModelList::aclRule('extVplan-col-show-info_3', $userTyp) ) {
            $show['info_3'] = true;
        }

        $this->render([
            "tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/vplan",
                "selfURL" => URL_SELF,
                "showCol" => $show
            ]

        ]);
    }

}
