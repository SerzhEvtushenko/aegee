<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created 30.11.2009 19:13:11
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validator class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slValidator {

    /**
     * @var array process rules
     */
    static private $pr  = array(); 

    /**
     * @var array validators rules
     */
    static private $vr  = array();

    /**
     * @var array rules names and params
     */
    private $_rules     = array();

    /**
     * @var array errors returned by validators
     */
    private $_errors    = array();

    /**
     * Initialize rules for validator
     * @param array $all_rules
     */
    public function __construct($all_rules = array()) {
        foreach($all_rules as $column=>$rules) {
            if (!isset($rules['validation']) && !isset($rules['process'])) {
                $rules['validation'] = $rules;
                $rules['process'] = array();
            }

            if (!isset($rules['process'])) $rules['process'] = array();
            $all_rules[$column] = $rules;
        }
        $this->_rules = $all_rules;
    }

    /**
     * Create new instance of slValidator
     * @static
     * @param array $all_rules
     * @return slValidator
     */
    static public function create($all_rules = array()) {
        return new slValidator($all_rules);
    }

    /**
     * Add rules for validator to current scope
     * @param $rules
     */
    public function addValidationRules($rules) {
        foreach($rules as $field=>$rules) {
            if (isset($rules['validation'])) {
                $rules = $rules['validation'];
            }
            if (!isset($this->_rules[$field]['validation'])) $this->_rules[$field]['validation'] = array();
            $this->_rules[$field]['validation'] = array_merge($this->_rules[$field]['validation'], $rules);
        }
    }

    /**
     * Add processors to current scope
     * @param $rules
     */
    public function addProcessRules($rules) {
        foreach($rules as $field=>$rules) {
            if (isset($rules['process'])) {
                $rules = $rules['process'];
            }
            if (!isset($this->_rules[$field]['process'])) $this->_rules[$field]['process'] = array();
            $this->_rules[$field]['process'] = array_merge($this->_rules[$field]['process'], $rules);
        }
    }

    /**
     * Process data with processors and validate it
     * @param $data to be processed
     * @param bool $apply_to_source
     * @return array
     * @throws slDPTException
     */
    public function process(&$data, $apply_to_source = false) {

        $result_data = $apply_to_source ? $data : array();
        if ($data instanceof slModel) {
            $array_data = $data->toArray();
        } else {
            $array_data = $data;
        }

        foreach($this->_rules as $field=>$rules) {
            if (empty($rules['validation']) && empty($rules['process'])) continue;
            $post_rules = array();
            foreach($rules['process'] as $rule_name => $params) {
                if (is_int($rule_name) && is_string($params)) {
                    $rule_name = $params;
                    $params = array();
                } elseif(!is_array($params)) {
                    $params = array($params);
                }
                $params['field_name'] = $field;

                if (!isset(self::$pr[$rule_name])) {
                    if (isset($params['class'])) {
                        $process_class  = $params['class'];
                        $method         = 'process'. ucfirst($rule_name);
                    } else {
                        $process_class  = slInflector::camelCase($rule_name) . 'ProcessRule';
                        $method         = 'execute';
                    }
                    if (class_exists($process_class)) {
                        $process_obj = new $process_class($data);
                        self::$pr[$rule_name]   = array($process_obj, $method);
                    } elseif (function_exists($rule_name) && !isset($params['class'])) {
                        self::$pr[$rule_name]   = $rule_name;
                    } else {
                        throw new slDPTException('There is no handler found for process: '.$rule_name);
                    }
                }

                if (isset($params['post']) && !isset($params['pre'])) {
                    $post_rules[$rule_name] = $params;
                    continue;
                }
                if (array_key_exists($field, $array_data) || isset($params['pre'])) {

                    $result_data[$field] = call_user_func(self::$pr[$rule_name], /*empty($data[$field]) ? null : */$data[$field], $params);
                }
            }

            foreach($rules['validation'] as $rule_name => $params) {
                if (is_int($rule_name) && is_string($params)) {
                    $rule_name = $params;
                    $params = array();
                } elseif(!is_array($params)) {

                    if ($params != 'true') {
                        $params = array('error'=>$params);
                    } else {
                        $params = array($params);
                    }
                }
                $params['field_name'] = $field;
                if (!isset(self::$vr[$rule_name])) {
                    if (isset($params['class'])) {
                        $process_class  = $params['class'];
                        $method         = 'process'. ucfirst($rule_name);
                    } else {
                        $process_class  = ucfirst($rule_name) . 'ValidationRule';
                        $method         = 'execute';
                    }
                    if (class_exists($process_class)) {
                        $process_obj = new $process_class($data);

                        self::$vr[$rule_name]   = array($process_obj, $method);
                    } elseif (function_exists($rule_name) && !isset($params['class'])) {
                        self::$vr[$rule_name]   = $rule_name;
                    } else {
                        throw new slDPTException('There is no handler found for validation: '.$rule_name);
                    }
                }

                /**
                 * If no mandatory rule set and no data in the field - skip it
                 */
                if (empty($rules['validation']['mandatory']) && empty($data[$field])) {
                    $res = true;
                } else {
                    $res = call_user_func(self::$vr[$rule_name], empty($data[$field]) ? null : $data[$field], $params);
                }
                if (!$res) {
                    if (is_array(self::$vr[$rule_name]) && is_callable(array(self::$vr[$rule_name][0], 'getError'))) {
                        $error = call_user_func(array(self::$vr[$rule_name][0], 'getError'), $field, $params);
                    } else {
                        $error = 'Error while validate '.$rule_name;
                    }
                    $this->setError(isset($params['error_field']) ? $params['error_field'] : $field, $error, $rule_name);

                } else {
                    if (!isset($result_data[$field])) {
                        $result_data[$field] = isset($data[$field]) ? $data[$field] : null;
                    }
                }
            }
//            if ($field == "password") {
//                vd($data['password'], $rules['validation']['mandatory'], $this->_errors);
//            }

            foreach($post_rules as $rule_name => $params) {
                if (array_key_exists($field, $data) || isset($params['post'])) {
                    $result_data[$field] = call_user_func(self::$pr[$rule_name], empty($data[$field]) ? null : $data[$field], $params);
                }
            }
        }
        return array('errors'=>$this->_errors, 'data'=>$result_data);
    }

    /**
     * Add error to result
     * @param string $field
     * @param string $message
     * @param string $raiser
     */
    public function setError($field, $message, $raiser = null) {
        if (!isset($this->_errors[$field])) $this->_errors[$field] = array();

        $this->_errors[$field][$raiser] = array(
            'message'   => $message,
            'raiser'    => $raiser,
            'dpt'       => true
        );
    }

}
