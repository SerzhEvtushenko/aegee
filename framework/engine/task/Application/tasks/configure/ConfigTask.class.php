<?php
/**
 * @author mounter (mounters@gmail.com)
 * @date 10.11.2009 0:44:53
 */

class ConfigTask extends slTask {

    protected $help_messages = array(
        'project'       => "config:project [--PROJECT_CONFIG_ITEM=VALUE]",
        'clear-cache'       => "config:clear-cache",
    );

    public function actionProject() {
        if ($this->route->getVarsCount()){
            for($i=0; $i < $this->route->getVarsCount();$i=+2) {
                $key = $this->route->getVar($i);
                $value = $this->route->getVar($i+1);
                SL::setProjectConfig($key, $value, false);
                echo $this->colorize('%G+ '.$key.'%n = '.$value." stored".PHP_EOL);
            }
        } else {
//            $this->warning('No options specified' . "\n");
            foreach(SL::getProjectConfig() as $key=>$item) {
                echo $this->colorize("%Y".$key."%n")  . ": " . sfYaml::dump($item) . "\n";
            }
        }
    }

    public function actionClearCache() {
        $sl_root = SL::getDirFramework();
        $this->clearAutoloadCache(substr($sl_root, 0, -1));
        $this->message("Cache was cleaned.");
    }

    public function actionActivateMlt() {
        $mlt_initial_info = array(
            'mlt'   => array(
                'languages'                     => array('en', 'ru'),
                'fill_with_default_language'    => true,
                'models_auto_translate'         => true,
                'applications'                  => array('frontend'),
                'force_compile'                 => true,
            ),
        );
        if (SL::getProjectConfig('mlt')) {
            $this->error('MLT already added to the project');
        } else {
            SL::setProjectConfig('mlt', $mlt_initial_info, false);
            $this->message('MLT added successfully');
        }
    }

    private function clearAutoloadCache($dir) {
        $files = GLOB($dir . '/*', GLOB_NOSORT);
        foreach($files as $file) {
            if (is_dir($file)) {
                $this->clearAutoloadCache($file);
            }
        }
        if (is_file($dir . '/.autoload')) {
            unlink($dir . '/.autoload');
        }
    }

}
