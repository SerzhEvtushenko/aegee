<?php

class StartupController extends slController {

    public function preAction() {
        AclUser::initialize();
        slMemcached::initialize();
        slRouter::baseRedirect();
        StaticPage::checkMemcachedMLT();
        StaticPage::setMetaData();

        $this->view->revision           = 5;
        $this->view->route_name         = slRouter::getCurrentRoute('route_name');
        $this->view->uri                = slRouter::getUri();
        $this->view->active_language    = MLT::getActiveLanguage();
        $this->view->current_uri        = slRouter::getCurrentUri();
        $this->view->social             = slSocial::getConfig();
	    $this->view->og_url             = slRouter::getBaseUrl().MLT::getActiveLanguage().slRouter::getUri();


    }

}
