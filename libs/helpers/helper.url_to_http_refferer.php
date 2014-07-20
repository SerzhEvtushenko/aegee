<?php

Class ftlHelperUrlToHttpRefferer extends ftlBlock {

//    protected $_is_inline = true;

    public function process($params) {

        $res = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : slRouter::getBaseUrl().MLT::getActiveLanguage().'/';

        return $res;
    }

}