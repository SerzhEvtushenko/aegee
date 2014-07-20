<?php
/**
 * @package 
 * @version 0.1
 * created: 05.05.2010 2:35:38
 */

Class ftlHelperRand extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        return md5(time());
    }
 
}