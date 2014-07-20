<?php

Class ftlHelperModelField extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        $class_name = slInflector::camelCase($params[0]);
        if (class_exists($class_name) && ($object = (call_user_func(array($class_name, 'loadOne'), $params[1])))) {

            return $object[$params[2]];
        } else {
            return "Object not found";
        }
    }

}