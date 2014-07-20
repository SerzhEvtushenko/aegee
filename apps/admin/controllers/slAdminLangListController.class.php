<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rekvizit
 * Date: 11/04/2013
 * Time: 01:38
 * To change this template use File | Settings | File Templates.
 */

class slAdminLangListController extends slAdminListController{

    public function performFilters(C $c) {
        parent::performFilters($c);
        $c->andWhere(array('lang'=>MLT::getActiveLanguage()));
    }

    protected function processData(&$data) {
        parent::processData($data);

        $data['lang'] = MLT::getActiveLanguage();
    }
}