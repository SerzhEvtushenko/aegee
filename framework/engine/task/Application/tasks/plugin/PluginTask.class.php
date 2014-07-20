<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 21.12.2009 20:06:54
 */

/**
 * Plugin generator
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * @package
 * @version 0.1
 * created: 21.12.2009 20:06:54
 */
Class PluginTask extends slTask {

    public function actionList() {
        $server_resource = 'http://solve.local/';

    }

    public function actionInstall() {
        $this->requireParametersCount(1);
        $plugin_name = $this->route->getVar(0);
        $res = null;
        if (class_exists($plugin_name . 'Plugin')) {
            $res = call_user_func(array($plugin_name . 'Plugin', 'install'));
        } else {
            echo 'plugin '.$plugin_name . 'Plugin was not found.<br/>';
        }
        if ($res) {
            echo "Plugin ".$plugin_name.' successfully installed!<br/>';
        } else {
            echo "An error occured while installing plugin ".$plugin_name.'<br/>';
        }
    }

    public function actionPublish() {
        $this->requireParametersCount(1);
        $plugin_name = $this->route->getVar(0);
        if (is_dir(SL::getDirPlugins().$plugin_name.'/assets/css')) {
            slLocator::copyRecursive(SL::getDirPlugins().$plugin_name.'/assets/css/', SL::getDirWeb().'css/assets/'.$plugin_name.'/');
        }
        echo "Assets were published<br/>";
    }

    public function actionConfigure() {
        $this->requireParametersCount(1);
        $plugin_name = $this->route->getVar(0);

        if (class_exists($plugin_name . 'Plugin')) {
            $res = call_user_func(array($plugin_name . 'Plugin', 'configure'));
        } else {
            $res = false;
        }

        echo $this->colorize($res ? "Plugin configured"."\n" : "Plugin not found!");
    }

}