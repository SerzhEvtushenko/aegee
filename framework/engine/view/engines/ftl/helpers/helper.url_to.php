<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */

Class ftlHelperUrlTo extends ftlBlock {

    private $_system_vars = array(
        'action'    => null
    );

    private $_from = array();
    private $_custom = array();

    public function process($params) {
        $route_alias = $params[0];
        if ($route_alias == 'index') {
            return slRouter::getUrlPrefix() . '/';
        } elseif ($route_alias == 'self') {
            $additional = array();
            if (isset($params['lang'])) {
                $additional['lang_alias'] = $params['lang'];
            }
            return slRouter::getUrlPrefix($additional) . slRouter::getUri();
        }

        $this->_from = isset($params['from']) ? $params['from'] : $params;
        $this->_custom  = isset($params['custom']) ? $params['custom'] : false;

        slRouteHandler::setUp($this->_from, $this->_custom);

        $res = slRouteHandler::getUrl($route_alias);

        return $res;
    }

}