<?php

class slDevController extends slController {

    public function __construct(slRoute $route) {
        if (!SL::getProjectConfig('dev_mode') && !(slRouter::getCurrentMode() == 'console')) throw new Exception('Not in dev mode!');
        SL::setProjectConfig('dev_mode', true);
        error_reporting(E_ALL);
        parent::__construct($route);
    }

}