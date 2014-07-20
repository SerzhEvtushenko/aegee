<?php

class SocialTask extends slTask {

    protected $help_messages = array(
        'install'        => "social:install [application_name]",
    );

    public function actionInstall() {
        $app = $this->route->getVar(0, 'grey');

        $app_path = SL::getDirRoot() . 'apps/' .$app . '/';

        slLocator::makeWritable(SL::getDirRoot(). 'libs/slDB');
        slLocator::makeWritable($app_path . 'config');

        $current_dir = realpath(dirname(__FILE__) .'/..') . '/';

        $files = array(
            array(  'from' => $current_dir . 'skeleton/config/acl.yml',
                    'to' => $app_path .'config/acl.yml'),
            array(  'from' => $current_dir . 'skeleton/config/social.yml',
                    'to' => SL::getDirRoot() .'config/social.yml'),
            array(  'from' => $current_dir . 'skeleton/js/social.js',
                    'to' => SL::getDirWeb().'js/social.js'),
            array(  'from' => $current_dir . 'skeleton/structure/SocialUser.yml',
                    'to' => SL::getDirRoot().'libs/slDB/structure/SocialUser.yml'),
        );

        foreach($files as $file){
            if (!file_exists($file['to'])){
                copy($file['from'], $file['to']);
            }else{
                echo $this->colorize('%R+' . $file['to'] . ' already exist' . PHP_EOL);
            }
        }

        echo $this->colorize('%B+Social successfully INSTALLED' . PHP_EOL);
        echo $this->colorize('%B+In order for the application to work correctly - please install ACL.' . PHP_EOL);
    }
}