<?php
/**
 * @package 
 * @version 0.1
 * created: 04.05.2010 2:04:58
 */
 
 Class ftlHelperUsePluginsCss extends ftlBlock{

    public function process($params) {
        $files = slLocator::getInstance()->in(SL::getDirWeb().'css/assets', true)->find('*.css');
        //vd($files,"!@#");
        $html = '';
        foreach($files as $file) {
            $html .= '<link href="'.substr($file, strpos($file, 'css/assets')).'" rel="stylesheet" type="text/css" />';
            //vd($file,substr($file, strpos($file, 'css/assets')),"!@#");
        }
        //vd($html);
        return $html;
    }

 }