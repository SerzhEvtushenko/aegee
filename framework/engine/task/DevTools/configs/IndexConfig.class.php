<?php

class IndexConfig extends slDevController {

    public function actionDefault() {
        if (!SL::getProjectConfig('dev_mode')) throw new Exception('Not in dev mode!');
    }

}