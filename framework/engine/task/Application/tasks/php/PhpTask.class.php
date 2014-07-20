<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 26.12.2009 14:57:45
 */

/**
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class PhpTask extends slTask {

    public function actionDefault() {
        echo "eval:<br/>";
    }

    public function actionSession() {
        echo "<pre>";
        var_dump($_SESSION);
        echo "</pre>";
    }

    public function actionCleanTpl() {
        $path = SL::getDirTmp() . 'templates/';
        slLocator::unlinkRecursive($path);
        die('cleaned' . "\n");
    }

}