<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alpha
 * Date: 04.02.11
 * Time: 18:04
 * To change this template use File | Settings | File Templates.
 */

/**
 * @todo переделать на модификатор :)
 */

class ftlHelperTruncate extends ftlBlock {

    protected $_is_inline = true;

    /**
     * Обрезает строку
     *
     * @param $params[0] string $string  - исходная строка
     * @param $params[1] int    $length  - Определяет максимальную длинну обрезаемой строки.
     * @param $params[2] string $etc     - Текстовая строка, которая заменяет обрезанный текст. Её длинна НЕ включена в максимальную длинну обрезаемой строки.
     * @param $params[3] boolean $break_words  - Определяет, обрезать ли строку в промежутке между словами (false) или строго на указаной длинне (true).
     * @param $params[4] boolean $middle       - Определяет, нужно ли обрезать строку в конце (false) или в середине строки (true). Обратите внимание, что при включении этой опции, промежутки между словами игнорируются.
     * @param $params[5] boolean $strip_tags   - удалятьли хтмл теги
     * @return unknown
     */
    public function process($params) {
        $string         = isset($params[0]) ? strval($params[0]) : '';
        $length         = isset($params[1]) ? intval($params[1]) : 60;
        $etc            = isset($params[2]) ? strval($params[2]) : '...';
        $break_words    = isset($params[3]) ? $params[3] : false;
        $middle         = isset($params[4]) ? $params[4] : false;
        $strip_tags     = isset($params[5]) ? $params[5] : true;

        if( $length == 0 )
            return '';

        if( $strip_tags ) {
            $string = strip_tags( html_entity_decode( $string, ENT_QUOTES, 'UTF-8' ) );
        }

        if( mb_strlen( $string ) > $length ) {
            $length -= mb_strlen( $etc );
            if( ! $break_words && ! $middle ) {
                $pcre = array('\s\–\s+?(\S+)?','[,]?\s+?(\S+)?');
                $pcre = '/('.implode('|',$pcre).')$/';
                $string = preg_replace( $pcre, '', mb_substr( $string, 0, $length + 1 ) );
                $string = mb_substr( $string, 0, $length + 1 );
            }
            if( ! $middle ) {
                return mb_substr( $string, 0, $length ) . $etc;
            } else {
                return mb_substr( $string, 0, $length / 2 ) . $etc . mb_substr( $string, - $length / 2 );
            }
        } else {
            return $string;
        }

    }
}