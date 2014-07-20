<?php
/**
 * @package
 * @version 0.1
 * created: 08.05.2010 14:31:01
 */
Class ftlHelperCompressCss extends ftlBlock {

    static private $_included_css = array();

    public function process($params) {
        $res = '';
        $compressed_string = '';
        $md5_string = '';
        if ((count($params) == 0)) {
            //proverka dlya production
            if(SL::getProjectConfig('dev_mode')) {
                //generim kontrolny md5 esli dev_mode true
                foreach(self::$_included_css as $file) {
                    $md5_string .= SL::getDirWeb() . $file . ':' . filemtime(SL::getDirWeb() . $file);
                }
                $md5_string = md5($md5_string);
            }
            if((!SL::getProjectConfig('dev_mode') && file_exists(SL::getDirWeb() . 'css.md5')) || (SL::getProjectConfig('dev_mode') && file_exists(SL::getDirWeb() . 'css.md5') && ($md5_string == file_get_contents(SL::getDirWeb() . 'css.md5')))) {
                $res .= '<link href="css/'.md5(filemtime(SL::getDirWeb() . 'css.md5')).'.css" type="text/css" rel="stylesheet" />' . "\n";
            } else {
                //ubivaem stary zakeshirovanniy file
                @unlink(SL::getDirWeb().'css/'.md5(filemtime(SL::getDirWeb() . 'css.md5')).'.css');
                $md5_file = fopen(SL::getDirWeb().'css.md5', 'w');
                fwrite($md5_file, $md5_string);
                fclose($md5_file);
                $result_file = fopen(SL::getDirWeb().'css/'.md5(filemtime(SL::getDirWeb() . 'css.md5')).'.css', 'w');
                //pishem v stroku soderzhimoe podkluchaemyh faylov
                foreach(self::$_included_css as $file) {
                    $compressed_string .= file_get_contents(SL::getDirWeb() . $file)."\n";
                }
                //sohranyaem novy file i vivodim rezultat
                fwrite($result_file, $compressed_string);
                fclose($result_file);
                $res .= '<link href="css/'.md5(filemtime(SL::getDirWeb() . 'css.md5')).'.css" type="text/css" rel="stylesheet" />' . "\n";
            }
        } else {
            foreach($params as $css) {
                if (!in_array($css, self::$_included_css)) {
                    self::$_included_css[] = $css;
                }
            }
        }

        return $res;
    }

}