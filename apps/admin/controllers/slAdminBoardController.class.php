<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rekvizit
 * Date: 11/04/2013
 * Time: 01:38
 * To change this template use File | Settings | File Templates.
 */

class slAdminBoardController extends slAdminListController{

    public function actionInfo(){
        parent::actionInfo();
        $this->view->users          = AclUser::loadList(C::create()->orderBy('title ASC'));
    }

}