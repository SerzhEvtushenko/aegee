<?php

class CheckConfig extends slDevController {

    private $params = array(
        'General Info'   => array(
            'php_version'   => array(
                'title' => 'PHP Version',
                'rule'  => '>= 5.2.6'
            ),
            'php_extensions'    => array(
                'title' => 'Installed extensions',
                'rule'  => 'pdo,mbstring,curl,tidy,openssl'
            ),
        ),
        'ImageMagick'   => array(
            'imagick_installed' => array(
                'title' => 'ImageMagick Installed',
            ),
        ),
    );


    public function actionDefault() {
        $result = array();
        foreach($this->params as $key=>$rules) {
            foreach($rules as $rule=>$info) {
                $result[$key][$rule] = $this->$rule($info);
            }
        }
        $this->view->params = $result;
    }

    public function php_version($res) {
        $res['value'] = PHP_VERSION;

        if (PHP_VERSION >= '5.2.6') {
            $res['result'] = 'pass';
        } else {
            $res['result'] = 'need to be upgraded';
        }
        return $res;
    }

    public function php_extensions($res) {
        $need_extensions = explode(',', $res['rule']);
        $installed = '';
        $need = '';
        foreach($need_extensions as $item) {
            if (extension_loaded($item)) {
                $installed .= $item . ', ';
            } else {
                $need .= $item . ', ';
            }
        }
        $installed = substr($installed, 0, -2);
        if ($need) {
            $need = substr($need, 0, -2);
            $res['result'] = 'install: '.$need;
        } else {
            $res['result'] = 'pass';
        }
        $res['value'] = $installed;
        return $res;
    }

    public function imagick_installed($res) {
        $output = array();
        $code = 0;
        exec('convert --version', $output, $code);
        if (isset($output[0])) {
            if (strpos($output[0], 'Version') !== false) {
                $res['result'] = 'pass';
                $res['value'] = substr($output[0], strpos($output[0], ' ', 10)+1, 7);
            }
        } else {
            $res['result'] = 'not found, using GD';
        }
        return $res;
    }

}