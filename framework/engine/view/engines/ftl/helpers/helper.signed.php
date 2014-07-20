<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */


Class ftlHelperSigned extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        $format = isset($params[1]) ? substr($params[1], 1, -1) : '%+01.2f';
        $res = sprintf($format, $params[0]);
        if (abs($res) > 1000) {
            $res = substr($res, 0, strlen($res)-6).' '.substr($res, -6);
        }
        return $res;
    }

}