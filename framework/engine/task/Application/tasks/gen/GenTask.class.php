<?php
/**
 * Task controller for generation proect, application, module,etc.
 *
 * @author mounter (mounters@gmail.com)
 * @date 10.11.2009 0:44:53
 */

class GenTask extends slTask {

    protected $help_messages = array(
        'project'       => "gen:project PROJECT_NAME [--folder=PROJECT_FOLDER] [--main-appname=MAIN_APP_NAME]",
        'application'   => "gen:application APP_NAME [--set-dafault]",
        'module'        => "gen:module MODULE_NAME APP_NAME",
        'helper'        => "gen:helper HELPER_NAME",
    );


    /**
     * Generate module for specified application with specified name
     * also used in generateProject action
     *
     * @param string $application_name
     * @param string $module_name
     * @return void
     */
    public function actionModule($application_name = null, $module_name = null, $application_dir = null) {
        if (!$application_name) {
            $this->requireParametersCount(2);
            $module_name = $this->route->getVar(0);
            if (!$module_name) {
                echo $this->colorize('%RError:%n You must specify module name');
                die();
                //$module_name = ucfirst($this->consolePrompt('Enter Module name'));
            }
            $module_name = ucfirst($module_name);
            $application_name = $this->route->getVar(1);
            if (!$application_name) {
                echo $this->colorize('%RError:%n You must specify application name');
                die();
                //$application_name = $this->consolePrompt('Enter APP name', 'frontend');
            }
        } else {
            $module_name = ucfirst($module_name);
        }
        $vars = array(
            '__NAME__'  => $module_name
        );

        $app_path = $application_dir ? $application_dir : SL::getApplicationConfig('dir', strtolower($application_name));

        if (!is_dir($app_path)) {
            echo $this->colorize('%RError:%n No application ['.$application_name.'] found');
            die();
        }

        if (!is_file($app_path . 'controllers/' . $module_name . 'Controller.class.php')) {
            $this->createFromTemplate('module/__controller.php', $app_path . 'controllers/' . $module_name . 'Controller.class.php', $vars);
        } else {
            echo $this->colorize('%RError:%n Module ['.$module_name.'] already exists in application ['.$application_name.']');
            die();
        }
        
        if (!is_file($app_path . 'templates/' . strtolower($module_name) . '/default.tpl')) {
            slLocator::makeWritable($app_path . 'templates/' . strtolower($module_name));
            $tpl_name = (strtolower($module_name) == 'index' ? '__index_default.tpl' : '__default.tpl');
            $this->createFromTemplate('module/' . $tpl_name, $app_path . 'templates/' . strtolower($module_name) . '/default.tpl', $vars);
        }
        echo $this->colorize('%G+ %nModule '.$module_name.' in app '.$application_name.' successfully created' . PHP_EOL);
    }

    /**
     *
     *
     * @throws Exception if application with $application_name is exists
     * @param string $application_name
     * @return void
     */
    public function actionApplication($application_name = null, $project_root = null, $create_index_module = true) {
        if (!$application_name) {
            $this->requireParametersCount(1);
            $application_name = $this->route->getVar(0);
            if (!$application_name) {
                echo $this->colorize('%RError:%n You must specify application name');
                die();
            }
        }
        $application_dir = ($project_root ? $project_root : SL::getDirRoot()) . 'apps/' . $application_name . '/';

        if (is_dir($application_dir)) {
            echo $this->colorize('%RError:%n slApplication "'.$application_name.'" is already configured'.PHP_EOL);
            die();
//            throw new Exception('slApplication "'.$application_name.'" is already configured');
        }

        slLocator::makeWritable($application_dir);
        slLocator::makeWritable($application_dir . '/controllers');
        slLocator::makeWritable($application_dir . '/templates');
        slLocator::copyRecursive(dirname(__FILE__) . '/skeletons/application/', $application_dir);
        $vars = array(
            '__PROJECT__'       => SL::getProjectConfig('name', null, true)
        );
        $this->createFromTemplate('templates/__layout.tpl', $application_dir . '/templates/_layout.tpl', $vars);

        $project_config = SL::getProjectConfig(null, null, true);

        /*
         * redeclared in slRouter in registerProjectApplications method
        $project_config['applications'][$application_name] = array(
            'routing' => array(
                'modes' => array('web')
            )
        );
        */
        if ($this->route->getVar(':options/set-default')) {
            $project_config['default_application'] = $application_name;
        }
        file_put_contents(($project_root ? $project_root : SL::getDirRoot()) . 'config/project.yml', sfYaml::dump($project_config));
        echo $this->colorize('%G+ %nApplicaiton '.$application_name.' successfully created' . PHP_EOL);
        if ($create_index_module) {
            $this->actionModule($application_name, 'index', $application_dir);
        }
    }

    public function actionProject() {
        $project_name   = $this->route->getVar(0);
        if (!$project_name) {
            $this->paramsError('project', 'You must specify project name');
            die();
        }
        $framework_root = SL::getDirRoot();
        $project_root   = SL::getDirRoot();

        if ($dir_root = $this->route->getVar(':options/folder')) {
            if (is_dir($dir_root)) {
                echo $this->colorize('%YWarning:%n Directory ['.$dir_root.'] is not empty. Some data might be overwritten.'.PHP_EOL);
            }
            slLocator::makeWritable($dir_root);
            $project_root = $dir_root . '/';
        }

        $project_config = $project_root . 'config/project.yml';
        if (is_file($project_config)) {
            echo $this->colorize('%RError:%n Project is already configured.'.PHP_EOL);
            die();
        }

        $app_name       = $this->route->getVar(':options/main-appname', 'frontend');

        $dirs_to_create = array(
            $project_root . 'apps',
            $project_root . 'config',
            $project_root . 'config/local',
            $project_root . 'common',
            $project_root . 'common/routes',
            $project_root . 'libs' ,
            $project_root . 'libs/classes' ,
            $project_root . 'libs/tests' ,
            $project_root . 'libs/helpers' ,
            $project_root . 'libs/plugins' ,
            $project_root . 'tmp' ,
            $project_root . 'tmp/templates' ,
            $project_root . 'web',
            $project_root . 'web/upload',
        );

        slLocator::makeWritable($dirs_to_create);
        slLocator::copyRecursive(dirname(__FILE__) . '/skeletons/web/',     $project_root . 'web/');
        slLocator::copyRecursive(dirname(__FILE__) . '/skeletons/common/',  $project_root . 'common/');

        $framework_path_const = $dir_root ? 'define("__PROJECT_ROOT__", getcwd().\'/\');'
                ."\n".'require_once \''.$framework_root.'framework/bootstrap.php\';' : 'require_once \'framework/bootstrap.php\';';
        $index_init = $dir_root ? 'define("__PROJECT_ROOT__", getcwd().\'/../\');'
                ."\n".'require_once \''.$framework_root.'framework/bootstrap.php\';' : 'require_once \'../framework/bootstrap.php\';';
        $vars = array(
            '__NAME__'          => $project_name,
            '__DEFAULT_APP__'   => $app_name,
            '__DEV_KEY__'       => substr(md5(time()), 0, 8),
            '__SL_INIT__'       => $framework_path_const,
            '//__INDEX_INIT__'  => $index_init,
        );
        $this->createFromTemplate('__index.php', $project_root . 'web/index.php', $vars);
        $this->createFromTemplate('config/project.yml', $project_root . 'config/project.yml', $vars);
        $this->createFromTemplate('config/database.yml',     $project_root . 'config/database.yml', $vars);
        $this->createFromTemplate('config/acl.yml',     $project_root . 'config/acl.yml', $vars);

        if ($dir_root) {
            if ($this->route->getVar(':options/with-framework')) {
                slLocator::copyRecursive($framework_root, $project_root);
            } else {
                $this->createFromTemplate('sl', $project_root . 'sl', $vars);
                copy(dirname(__FILE__) . '/skeletons/sl.bat', $project_root . 'sl.bat');
            }
            chmod($project_root . 'sl', 0777);

        }
        SL::initializeDirectories($project_root);
        $this->actionApplication($app_name, $project_root);
        $this->colorize('%G+ %nProject '.$project_name.' successfully created' . PHP_EOL);
    }

    public function actionHelper() {
        $this->requireParametersCount(1);
        $name = $this->route->getVar(0);
        if (!$name) {
            echo $this->colorize('%RError:%n You must specify helper name.'.PHP_EOL);
            die();
        }
        $name = slInflector::camelCase($name);
        $helper_filename = SL::getDirUserLibs().'helpers/helper.'.slInflector::directorize($name).'.php';
        new FTL(); //@todo define if other engines is going to be used
        if (is_file($helper_filename) || class_exists('ftlHelper'.$name)) {
            echo $this->colorize('%RError:%n Helper ['.$name.'] already exists.'.PHP_EOL);
            die();
        }
        $this->createFromTemplate('helper/_helper.skeleton', $helper_filename, array('__NAME__'=>$name));
        echo $this->colorize('%G+ %nHelper '.$name.' generated in ['.SL::getDirUserLibs().'helpers/helper.'.strtolower($name).'.php'.']'."\n");
    }


    public function actionModel() {
        $this->route->forward(array('controller'=>'DbTask', 'action'=>'actionGenModel'));
    }

    public function actionTest() {
        $this->requireParametersCount(1);
        $name = $this->route->getVar(0);
        if (!$name) {
            echo $this->colorize('%RError:%n You must specify test name.'.PHP_EOL);
            die();
        }
        $name = slInflector::camelCase($name);
        slLocator::makeWritable(SL::getDirUserLibs().'tests/unit/');
        $helper_filename = SL::getDirUserLibs().'tests/unit/'.slInflector::directorize($name).'Test.php';
        new FTL(); //@todo define if other engines is going to be used
        if (is_file($helper_filename)) {
            echo $this->colorize('%RError:%n Test ['.$name.'] already exists.'.PHP_EOL);
            die();
        }
        $this->createFromTemplate('tests/__test.php', $helper_filename, array('__NAME__'=>$name));
        echo $this->colorize('%G+ %nTest '.$name.' generated in ['.SL::getDirUserLibs().'tests/'.strtolower($name).'Test.php'.']'."\n");

    }

    public function actionPassword() {
        echo md5($this->route->getVar(0));
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
