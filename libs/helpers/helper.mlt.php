<?php

Class ftlHelperMlt extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {

        $res = StaticPage::loadCached($params[0]);
        $res = (isset($params[1]) && is_callable($params[1])) ? $params[1]($res) : $res ;

        return nl2br($res);
    }

}