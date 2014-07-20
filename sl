#!/usr/bin/env php
<?php
define("__PROJECT_ROOT__", getcwd().'/');
require_once './framework/bootstrap.php';
SL::loadApplication(slRouter::getInstance()->detectApplication())->run();
