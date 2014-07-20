<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */

Class ftlHelperHtml extends ftlBlock {

    public function process($params) {
        $res = $params[0];
        return htmlentities($res, 2, 'utf-8');
    }

}