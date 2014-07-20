<?php
/**
 * @package Solve Project
 * @subpackage Bootstrap
 * @version 1.0
 * created: Dec 23, 2009 12:04:05 AM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

require_once dirname(__FILE__) . '/../framework/engine/tools/functions.php';
require_once dirname(__FILE__) . '/../framework/engine/autoload/slAutoloader.class.php';

slAutoloader::register();
if (isset($_SERVER['DOCUMENT_ROOT'])) session_start(); //to ignore session using CLI
slProfiler::checkPoint('slAutoloader registered');
SL::initialize(defined('__PROJECT_ROOT__') ? __PROJECT_ROOT__ : null);

