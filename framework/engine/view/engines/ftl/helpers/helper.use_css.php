<?php
/**
 * @package 
 * @version 0.1
 * created: 08.05.2010 14:31:01
 */

 
Class ftlHelperUseCss extends ftlBlock {

    static private $_included_css = array();

    protected $is_inline = true;

    public function process($params) {
        $res = '';
        if ((count($params) == 0)) {
            foreach(self::$_included_css as $file) {
                if (mb_substr($file,0,1) == '!') {
                    $res .= '<link rel="stylesheet" href="'.mb_substr($file, 1).'" type="text/css" media="screen" />' . "\n";
                } else {
                    $res .= '<link rel="stylesheet" href="css/'.$file.'" type="text/css" media="screen" />' . "\n";
                }
            }
        } else {
            foreach($params as $css) {
                if (!array_key_exists(md5($css), self::$_included_css)) {
                    self::$_included_css[md5($css)] = $css;
                }
            }
        }
        return $res;
    }
 
}