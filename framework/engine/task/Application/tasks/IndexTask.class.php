<?php

Class IndexTask extends slTask {

    public function actionDefault() {
        $out = "\n" . 'Usage: sl [task] [option1] [option2] ...' . "\n\n";
        $out.= $this->colorize("Available tasks:" . "\n");
        $dirs = slLocator::getInstance()->in(SL::getDirEngine().'task/Application/tasks')->find('*', slLocator::TYPE_DIR, slLocator::HYDRATE_NAMES);
        $loaded_classes = slAutoloader::getRegistered();
        foreach($loaded_classes as $class=>$file) {
            if ((strpos($file, 'Application') === false) && (strpos($file, 'Task.class.php') !== false)) {
                $task_name = substr(slInflector::directorize($class), 0, -5);
                if ($task_name == 'sl') continue;
                $out .= '  '. $task_name . "\n";
            }
        }
        foreach($dirs as $dir) {
            $out .= '  '.$dir. "\n";
        }
        echo $this->colorize($out);
    }

    public function actionCC() {
        $dir = SL::getDirTmp() . 'templates/';
        slLocator::unlinkRecursive($dir);
        echo "Cache cleaned";
    }
}