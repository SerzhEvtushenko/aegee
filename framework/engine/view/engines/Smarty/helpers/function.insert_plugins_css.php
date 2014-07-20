<?php

function smarty_function_insert_plugins_css($params, $smarty) {

    $files = slLocator::getInstance()->in(SL::getDirWeb().'css/assets', true)->find('*.css');
    $html = '';
    foreach($files as $file) {
        $html .= '<link href="'.substr($file, strpos($file, 'css/assets')).'" rel="stylesheet" type="text/css" />';
    }
    return $html;
//    if ($plugin = $quicky->fetch_plugin('function.insert_css')) {
//        include_once $plugin;
//    }
//    return quicky_function_insert_css(array('files'=>$files), $quicky);
}