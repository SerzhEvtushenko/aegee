<?php
/**
 * @package 
 * @version 0.1
 * created: 02.05.2010 11:58:19
 */

Class ftlBlockIgnoreLocal extends ftlBlock{


    public function process($tag) {
        $res = '<?php if (!empty($_SERVER["HTTP_HOST"]) && (strpos($_SERVER["HTTP_HOST"], ".local") === false)) : ?>';

        return $res;
    }

    public function processEnd($tag) {
        return '<?php endif; ?>';
    }

}