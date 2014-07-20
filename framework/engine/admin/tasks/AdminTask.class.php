<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 12.11.12 09:23
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class AdminTask extends slTask {

    protected $help_messages = array(
        'install'        => "admin:install [application_name]",
    );

    public function actionInstall() {
        die('Disabled due to security reasons'.PHP_EOL);

        $app = $this->route->getVar(0, 'admin');

        $app_path = SL::getDirRoot() . 'apps/' .$app . '/';
//        if (is_dir($app_path)) {
//            $this->error('Application '.$app.' already exists!');
//        }

        slLocator::makeWritable($app_path);
        slLocator::makeWritable($app_path . 'controllers');

        $current_dir = realpath(dirname(__FILE__) .'/..') . '/';

        slLocator::copyRecursive($current_dir . 'skeleton/', $app_path);
        slLocator::copyRecursive($current_dir . 'templates/controllers/AdminController.skeleton.php', $app_path . 'controllers/AdminController.class.php');
        slLocator::copyRecursive($current_dir . 'templates/controllers/StartupController.skeleton.php', $app_path . 'controllers/StartupController.class.php');
        slLocator::copyRecursive($current_dir . 'assets/', SL::getDirWeb());

    }

    private function createFromTemplate($skeleton, $destination, $vars) {
        $data = file_get_contents(dirname(__FILE__) . '/templates/' . $skeleton);
        foreach($vars as $name=>$value) {
            $data = str_replace($name, $value, $data);
        }
        if (is_file($destination)) @unlink($destination);
        file_put_contents($destination, $data);
    }


}
