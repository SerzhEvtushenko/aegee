<?php
/**
 * @package 
 * @version 0.1
 * created: 08.05.2010 14:31:01
 */


Class ftlHelperUseJs extends ftlBlock {

    static private $_included_js = array();

    protected $is_inline = true;

    public function process($params) {
        $res = '';
        if ((count($params) == 0)) {
            foreach(self::$_included_js as $file) {
                if (mb_substr($file,0,1) == '!') {
                    $res .= '<script type="text/javascript" src="'.mb_substr($file, 1).'"></script>' . "\n";
                } else {
                    $res .= '<script type="text/javascript" src="js/'.$file.(slView::getInstance()->getVar('revision') ? '?'.slView::getInstance()->getVar('revision') : '').'"></script>' . "\n";
                }
            }
        } else {
            foreach($params as $js) {
                if (!array_key_exists(md5($js), self::$_included_js)) {
                    self::$_included_js[md5($js)] = $js;
                }
            }
        }

        return $res;
    }
 
}