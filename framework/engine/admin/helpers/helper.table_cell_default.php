<?php


Class ftlHelperTableCellDefault extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        $res = '';
        $info   = $params[1];
        $value  = $params[0];
        $default_index = $params[0];
        if ($info['type'] == 'image') {
            $res = '<img src="'.$value['link'].'" alt="" width="100px" />';
        } elseif ($info['type'] == 'checkbox') {
            $res = $value ? 'Да' : 'Нет';
        } elseif ($info['type'] == 'select') {
            foreach($info['field_values'] as $fv) {
                if ($fv['id'] == $value) {
                    $value = $fv['title'];
                    break;
                }
            }
            $res = $value;
        } else {
            $res = $value;
        }
        if (isset($info['badge'])) {
            if (!is_array($info['badge'])) {
                $res = '<span class="badge">'.$res.'</span>';
            } else {
                $res = '<span class="badge badge-'.strtolower($info['badge'][$default_index]).'">'.$res.'</span>';
            }
        }
        return $res;
    }
}