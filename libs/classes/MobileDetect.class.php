<?php
/**
 * Created by JetBrains PhpStorm.
 * User: serg
 * Date: 29.01.13
 * Time: 18:10
 * To change this template use File | Settings | File Templates.
 */


class MobileDetect extends slMobileDetect{

    static public function isMobileVersion(){
//        return true;
        if(!self::$handler){
            self::$handler = new MobileDetect();
        }

        if(!isset($_COOKIE['mobile_version'])){
            $version  = (self::$handler->isMobile() ? '' : 'not_') . 'mobile';
            setcookie('mobile_version', $version , time()+96000*30, '/');
            $_COOKIE['mobile_version'] = $version ;
        }

        if (!isset($_SESSION['mobile_version'])){
            $_SESSION['mobile_version'] = (self::$handler->isMobile() && !self::$handler->isTablet()) ? true : false;
        }

        $mobile_version = isset($_COOKIE['mobile_version']) ? $_COOKIE['mobile_version'] : 'mobile';

        return ($_SESSION['mobile_version'] && ('mobile' == $mobile_version)) ? true : false;
    }

    static public function isTabletVersion(){
        if(!self::$handler){
            self::$handler = new MobileDetect();
        }
        if(!isset($_COOKIE['tabled_version'])){
            $version = (self::$handler->isMobile() ? '' : 'not_') . 'tabled';
            setcookie('tabled_version', $version , time()+96000*30, '/');
            $_COOKIE['tabled_version'] = $version;
        }
        if (!isset($_SESSION['tabled_version'])){
            $_SESSION['tabled_version'] = self::$handler->isTablet() ? true : false;
        }

        return ($_SESSION['tabled_version'] && ('tabled' == $_COOKIE['tabled_version'])) ? true : false;
    }

    static public function setVersionCookies($key, $version){
        $_COOKIE[$key] = $version;
        setcookie($key, $version , time()+96000*30, '/');
    }

}
