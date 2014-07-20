<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 29.08.12 15:10
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slSocial {

    static private $_config = null;

    static public function getConfig($key = null) {
        if (!isset(self::$_config)) {
            self::$_config = SL::getConfig('social');

            $current_domain = substr(slRouter::getBaseUrl(), 7,-1);
            if (isset(self::$_config['environments'][$current_domain])) {
                $tmp = self::$_config['environments'][$current_domain];
                self::$_config += $tmp;
                unset(self::$_config['environments']);
            }
        }
        if (is_null(self::$_config )) throw new slBaseException('Social config not found');

        return SL::getDeepArrayValue(self::$_config, $key);
    }

    static public function logoutUser() {

    }


}
