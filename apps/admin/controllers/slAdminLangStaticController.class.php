<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rekvizit
 * Date: 11/04/2013
 * Time: 01:38
 * To change this template use File | Settings | File Templates.
 */

class slAdminLangStaticController extends slAdminStaticController{

    public function preAction() {
        parent::preAction();
        $this->_module['select']['lang'] = MLT::getActiveLanguage();
    }

}