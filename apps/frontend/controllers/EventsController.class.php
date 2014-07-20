<?php

Class EventsController extends slController {

    public function actionDefault() {
        $page   = $this->route->getVar('page_number', 1);
        $tag    = $this->route->getVar('tag', false);
	    $filter = $this->route->getVar('filter','all');

        StaticPage::setMetaData('events');

        $this->view->events         = Event::getList($page, $tag, $filter);
        $this->view->pager          = slPaginator::getInfo();
        $this->view->link__         = 'events/'. (strlen($tag) > 0 ? 'tag/'.$tag.'/' : '');
        $this->view->pre_page_link  = 'page/';
        $this->view->route_name     = 'events';
	    $this->view->currentFilter  = $filter;
	    $this->view->post_page_link = ('all' <> $filter) ? '?filter='.$filter : '';;

    }

    public function actionDetail(){
        if ($slug = $this->route->getVar('slug')) {
            $event = Event::loadOneBySlug($slug);
            if (!$event || (0 == $event->is_active)) {
                throw new slRouteNotFoundException('');
            }

            StaticPage::setMetaData('events');
            MetainfoAbility::mergeMetaInfoWithArray($event->toArray());

            $this->view->event              = $event;
            $this->view->another_events     = $event->getAnotherEvent();
        }else{
            throw new slRouteNotFoundException('');
        }
    }

}