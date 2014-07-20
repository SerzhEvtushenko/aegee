<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */


Class ftlHelperNoHtml extends ftlBlock {

    public function process($params) {
        $res = $params[0];
        return $res;
    }

}