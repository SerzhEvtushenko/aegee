<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 12.09.12 12:10
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slSocialACL {

    static private $_storage     = array();

    static private $_authorized  = array();

    static private $_last_authorized_key = null;

    static public function initialize() {
        if (!isset($_SESSION['__social_storage'])) {
            $_SESSION['__social_storage'] = array(
                '__authorized'  => array()
            );
        }
        self::$_storage         = &$_SESSION['__social_storage'];
        self::$_authorized      = &$_SESSION['__social_storage']['__authorized'];
    }

    static public function set($key, $value, $social_key = 'common') {
        if (!isset(self::$_storage[$social_key])) {
            self::$_storage[$social_key] = array();
        }
        SL::setDeepArrayValue(self::$_storage[$social_key], $value, $key);
    }

    static public function get($what, $default = null, $social_key = 'common') {
        $res = SL::getDeepArrayValue(self::$_storage[$social_key], $what);
        return $res ? $res : $default;
    }

    static public function getAuthorizedList() {
        return self::$_authorized;
    }

    static public function authorize($social_key, $info) {
        self::$_last_authorized_key = $social_key;
        self::set('user', $info, $social_key);
        self::$_authorized[$social_key] = true;
    }

    static public function unauthorize($social_key) {
        if (!empty(self::$_authorized[$social_key])) {
            unset(self::$_storage[$social_key]);
            unset(self::$_authorized[$social_key]);
        }
    }

    static public function getLastAuthorizedKey() {
        return self::$_last_authorized_key;
    }

    static public function isLoggedIn($social_key = null) {
        if ($social_key) {
            return !empty(self::$_authorized[$social_key]);
        } else {
            return !empty(self::$_authorized);
        }
    }

    /**
     * @param string $social_key if not specified - will be user last logged in
     * @return mixed
     */
    static public function getCurrentUser($key = null, $social_key = null) {
        if (!$social_key) $social_key = self::$_last_authorized_key;
        // if nobody logged in return null
        if (!$social_key) return null;
        if (!empty(self::$_storage[$social_key])) {
            return $key ? self::get('user/'.$key, null, $social_key) : self::get('user', null, $social_key);
        } else {
            return null;
        }
    }

}
