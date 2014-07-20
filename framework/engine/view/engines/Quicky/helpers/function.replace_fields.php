<?php
function quicky_function_replace_fields($params, Quicky $quicky) {
    $str = $params[0];

    if (strpos($str, '{') === false) return $str;
    $data = $params[1];
    $match = array();
    preg_match_all('#\{(.*)\}#isU', $str, $match);
    if (!empty($match[1])) {
        $replace = array();
        foreach($match[1] as $field) {
            if (isset($data[$field])) {
                $replace['{'.$field.'}'] = $data[$field];
            }
        }
        $str = str_replace(array_keys($replace), $replace, $str);
    }
    return $str;
}