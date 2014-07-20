<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 18.01.2012 14:06:13
 */

/**
 * System class for working with routes
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slRouteHandler {

    private static $_from = array();
    private static $_custom = array();
    private static $_system_vars = array(
        'action'    => null
    );

    static public function setUp($from, $custom) {
        self::$_from = $from;
        self::$_custom = $custom;
    }


    static public function getUrl($route_alias) {
        $route = slRouter::getInstance()->getRoutes($route_alias);

        if (!$route && (($route_root = substr($route_alias, 0, strrpos($route_alias, '_'))) != $route_alias)) {
            $route = slRouter::getInstance()->getRoutes('__'.$route_root);
            self::$_system_vars['action'] = substr($route_alias, strlen($route_root)+1);
        }

        $pre_url = slRouter::getUrlPrefix();


        $res = $route ? $route['url'] : $route_alias;
        if ($route) {
            if (strpos($route['url'], '{') !== false) {
                $res = preg_replace_callback('#\{(\w+)\}\??#is', array('slRouteHandler', '_varReplace'), $route['url']);
            } else {
            }
        } else {
            if (isset($route_alias) && is_string($route_alias) && $pre_url) {
                $pre_url = strpos($route_alias, $pre_url) === 0 ? '' : $pre_url;
                $res = $route_alias;
            }
        }
        if (!$route && ($res[count($res)-1] !== '/') && (strpos($res, '.') === false)) {
            $res .= '/';
        }

        if ($pre_url) $pre_url = $pre_url.'/';

        $res = slRouteHandler::cleanupUrl($pre_url . $res);
        return $res;
    }

    static public function cleanupUrl($url) {
        $url = str_replace('(', '', $url);
        $url = str_replace(')?', '', $url);
        $url = str_replace(')', '', $url);
        $url = str_replace('/?', '/', $url);
        $url = str_replace('?/', '/', $url);
        $url = str_replace('//', '/', $url);
        return $url;
    }


    static private function _varReplace($var) {
        $is_optional = false;
        if (is_array($var)) {
            $is_optional = $var[0][strlen($var[0])-1] == '?';
            $var = array_pop($var);
        }

        $result = '';
        if (isset(self::$_from[$var]) ) {
            $result = self::$_from[$var] ? self::$_from[$var] : '';
        } elseif (array_key_exists($var, self::$_system_vars) ) {
            $result = self::$_system_vars[$var] ? self::$_system_vars[$var] : '';
        } else {
            if (!$is_optional) {
                throw new Exception('No '.$var.' found for make URL ');
            }
        }
        return $result;
    }

    private function _varReplaceCustom($var) {
        if (is_array($var)) {
            $var = array_pop($var);
        }
        if (array_key_exists($var, self::$_custom) ) {
            $result = self::$_custom[$var] ? self::$_custom[$var] : '';
        } elseif (array_key_exists($var, self::$_from) ) {
            $result = self::$_from[$var] ? self::$_from[$var] : '';
        } elseif (array_key_exists($var, self::$_system_vars) ) {
            $result = self::$_from[$var] ? self::$_system_vars[$var] : '';
        } else {
            $f = ffFileLogger::newInstance( self::$log_route, SL::getDirTmp() . 'log/helper_url_to.log');
            $f->log($var);

            $from = self::$_from;
            if( $from instanceof slModel ) {
                $from = $from->toArray();
            }
            $from   = dumperGet($from);
            $custom = dumperGet(self::$_custom);

            $f->log($from);
            $f->log($custom);

            $result = '';
            //throw new slBaseException('No '.$var.' found for make URL');
        }
        return $result;

    }

}
