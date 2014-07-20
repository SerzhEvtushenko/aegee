<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 18.10.12 01:46
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
 
class TwitterInfo {

    static public function fetchUserInfo($user, $cache = 3600) {
        $path   = SL::getDirCache() . 'twitter_'.$user;
        $result = false;
        if (file_exists($path) && (time() - filectime($path) < $cache)) {
            $raw_data = file_get_contents($path);
        } else {
            $raw_data = file_get_contents('http://twitter.com/users/show/aimbulance');
            file_put_contents($path, $raw_data);
        }

        if ($raw_data) {
            $result = simplexml_load_string($raw_data);
        }

        return $result;
    }

}
