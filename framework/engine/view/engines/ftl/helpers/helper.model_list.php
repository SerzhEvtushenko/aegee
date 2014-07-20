<?php

Class ftlHelperModelList extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        foreach($params as $name=>$model) {
            // it will have only one iteration
            if (is_numeric($name)) {
                $name = slInflector::pluralize($model);
            }
            $model = slInflector::camelCase($model);
            if (class_exists($model)) {
                $method = isset($params['method']) ? $params['method'] . '();' : 'loadList()->switchPKAccess();';
                $p = isset($params['params']) ? $params['params'] : array();
                return '<?php $__lv[\'' . $name . '\'] = '.$model.'::'.$method.' ?>';
            }
        }
    }

}