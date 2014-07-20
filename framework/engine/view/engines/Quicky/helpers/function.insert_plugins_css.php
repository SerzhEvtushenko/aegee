<?php

function quicky_function_insert_plugins_css($params,Quicky $quicky) {
    $files = slLocator::getInstance()->in(SL::getDirWeb().'css/assets', true)->find('*.css');
    $html = '';
    foreach($files as $file) {
        $html .= '<link href="'.substr($file, strpos($file, 'css/assets')).'" rel="stylesheet" type="text/css" />';
    }
    return $html;
}