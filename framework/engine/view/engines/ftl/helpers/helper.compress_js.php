<?php
/**
 * @package
 * @version 0.1
 * created: 08.05.2010 14:31:01
 */

Class ftlHelperCompressJs extends ftlBlock {

    static private $_included_js = array();

    public function process($params) {
        $res = '';
        $compressed_string = '';
        $md5_string = '';
        if ((count($params) == 0)) {
            //proverka testovy server
            if(SL::getProjectConfig('dev_mode')) {
                //generim kontrolny md5 esli dev_mode true
                foreach(self::$_included_js as $file) {
                    $md5_string .= SL::getDirWeb() . $file . ':' . filemtime(SL::getDirWeb() . $file);
                }
                $md5_string = md5($md5_string);
            }
            if((!SL::getProjectConfig('dev_mode') && file_exists(SL::getDirWeb() . 'js.md5')) || (SL::getProjectConfig('dev_mode') && file_exists(SL::getDirWeb() . 'js.md5') && ($md5_string == file_get_contents(SL::getDirWeb() . 'js.md5')))) {
                $res .= '<script type="text/javascript" src="js/c/'.md5(filemtime(SL::getDirWeb() . 'js.md5')).'.js"></script>' . "\n";
            } else {
                slLocator::unlinkRecursive(SL::getDirWeb().'js/c');
                slLocator::makeWritable(SL::getDirWeb().'js/c/');
                $md5_file = fopen(SL::getDirWeb().'js.md5', 'w');
                fwrite($md5_file, $md5_string);
                fclose($md5_file);
                $result_file = fopen(SL::getDirWeb().'js/c/'.md5(filemtime(SL::getDirWeb() . 'js.md5')).'.js', 'w');
                //pishem v stroku soderzhimoe podkluchaemyh faylov
                foreach(self::$_included_js as $file) {
                    $compressed_string .= file_get_contents(SL::getDirWeb() . $file)."\n";
                }
                //sohranyaem novy file i vivodim rezultat
                fwrite($result_file, $compressed_string);
                fclose($result_file);
                $res .= '<script type="text/javascript" src="js/c/'.md5(filemtime(SL::getDirWeb() . 'js.md5')).'.js"></script>' . "\n";
            }
        } else {
            foreach($params as $js) {
                if (!in_array($js, self::$_included_js)) {
                    self::$_included_js[] = $js;
                }
            }
        }

        return $res;
    }

}