<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */

Class ftlHelperUnsigned extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        return $params[0] < 0 ? -$params[0] : $params[0];
    }

}