<?php
/**
 * @package 
 * @version 0.1
 * created: 05.05.2010 2:35:38
 */

Class ftlHelperDate extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        if (isset($params[0])) {
            $date = $params[0];
            $date = strtotime($date);
        } else {
            $date = date('d/m/Y');
            return $date;
        }
        if ($date) {
            $res = date(!empty($params[1]) ? trim($params[1],'\'"') : "d/m/Y H:i s", $date);
            if ($res[0] == '"') $res = substr($res, 1, -1);

            $en = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
            $ua = array('Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень');
            $ru = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');

//            if (isset($params[2]) && $params[2] == 'genetive') {
//            if (in_array('genetive', $params)) {
                $ua = array('січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня');
                $ru = array('Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
//            }

            if (MLT::getActiveLanguage() == 'en') {

            } elseif (MLT::getActiveLanguage() == 'ua') {
                $res = str_replace($en, $ua, $res);
            } else {
                $res = str_replace($en, $ru, $res);
            }

            if (in_array('strtolower', $params)) {
                $res = mb_strtolower($res,'utf8');
            }

            return $res;
        } else {
            return '';
        }
    }
 
}