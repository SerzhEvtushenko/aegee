<?php

Class MembersController extends slController {

    public function actionDefault() {
        $page   = $this->route->getVar('page_number', 1);

        StaticPage::setMetaData('member_of_the_month');

        $this->view->members         = MemberOfMonth::getList($page);
        $this->view->pager          = slPaginator::getInfo();
        $this->view->link__         = 'members/';
        $this->view->pre_page_link  = 'page/';
        $this->view->route_name     = 'members';
        $this->view->subRoutename   = 'members';

    }


    public function actionDetail(){
        if ($slug = $this->route->getVar('slug')) {
            $member = MemberOfMonth::loadOneBySlug($slug);
            if (!$member || (0 == $member->is_active)) {
                throw new slRouteNotFoundException('');
            }

            StaticPage::setMetaData('members');
            MetainfoAbility::mergeMetaInfoWithArray($member->toArray());

            $this->view->member              = $member;
            $this->view->another_members    =  $member->getAnotherMember();

        }else{
            throw new slRouteNotFoundException('');
        }
    }

    public function actionAEGEETodayDefault() {
        $page   = $this->route->getVar('page_number', 1);
        $this->view->setTemplate('members/aegee_today_default.tpl');
        StaticPage::setMetaData('aegee_today');

        $this->view->objects        = AEGEEToday::getList($page, 1);
        $this->view->pager          = slPaginator::getInfo();
        $this->view->link__         = 'aegee-today-list/';
        $this->view->pre_page_link  = 'page/';
        $this->view->route_name     = 'members';
        $this->view->subRoutename   = 'aegee_today';

    }


    public function actionAEGEETodayDetail(){
        if ($slug = $this->route->getVar('slug')) {
            $this->view->setTemplate('members/aegee_today_detail.tpl');
            $object = AEGEEToday::loadOneBySlug($slug);
            if (!$object || (0 == $object->is_active)) {
                throw new slRouteNotFoundException('');
            }

            StaticPage::setMetaData('aegee_today');
            MetainfoAbility::mergeMetaInfoWithArray($object->toArray());

            $this->view->object             = $object;
            $this->view->another_objects    = $object->getAnotherObjects();
            $this->view->route_name     = 'members';

        }else{
            throw new slRouteNotFoundException('');
        }
    }

    public function actionTravelingReportDefault() {
        $page   = $this->route->getVar('page_number', 1);
        $this->view->setTemplate('members/traveling_report_default.tpl');

        StaticPage::setMetaData('traveling_reports');

        $this->view->objects        = TravelingReport::getList($page, 5);
        $this->view->pager          = slPaginator::getInfo();
        $this->view->link__         = 'traveling-reports/';
        $this->view->pre_page_link  = 'page/';
        $this->view->route_name     = 'traveling_reports';
        $this->view->route_name     = 'members';
        $this->view->subRoutename   = 'traveling_report';

    }


    public function actionTravelingReportDetail(){
        if ($slug = $this->route->getVar('slug')) {
            $this->view->setTemplate('members/traveling_report_detail.tpl');
            $object = TravelingReport::loadOneBySlug($slug);
            if (!$object || (0 == $object->is_active)) {
                throw new slRouteNotFoundException('');
            }

            StaticPage::setMetaData('traveling_reports');
            MetainfoAbility::mergeMetaInfoWithArray($object->toArray());

            $this->view->object             = $object;
            $this->view->another_objects    =  $object->getAnotherobjects();
            $this->view->route_name         = 'members';

        }else{
            throw new slRouteNotFoundException('');
        }
    }

}