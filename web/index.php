<?php
/*
 * @author Alexandr Viniychuk(alexandr@vinihychuk.com)
 */
require_once '../framework/bootstrap.php';

if (SL::getProjectConfig('index_disabled')) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || ($_SERVER['PHP_AUTH_USER'] !== 'test') || ($_SERVER['PHP_AUTH_PW'] !== 'aegee')) {
        header('WWW-Authenticate: Basic realm="Project private area"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You must enter correct username/password.';
        exit;
    }

}

SL::loadApplication(slRouter::getInstance()->detectApplication())->run();