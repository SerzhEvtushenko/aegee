<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 01.12.2009 10:32:38
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Abstract class for model ability
 *
 * @version 1.0
 *
 * @method slModelCollection loadRelative($relation = null)
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
abstract class slModelAbility {

    /**
     * @var array of params specified in slModel Structure
     */
    protected $_params      = array();

    /**
     * @var null|string primary key of current model
     */
    protected $_pk              = null;

    /**
     * @var string
     */
    protected $_table           = null;

    /**
     * @var array actions that available in suggestion
     */
    public $_mixed_methods   = array();
    

    /**
     * @var slModel
     */
    protected $_model   = null;

    /**
     * @param slModel $model
     */
    public function __construct($model) {
        if (!isset($model)) return true;
        $ability_name   = slInflector::directorize(substr(get_class($this), 0, -7));
        $this->_model   = $model;
        $this->_params  = $model->getStructure('abilities/'.$ability_name);
        $this->_pk      = $this->_model->getStructure()->getPrimaryField();
        $this->_table   = $this->_model->getStructure('table');
    }

    /**
     * execute when ability attaches to the model first time
     */
    public function setUp() {}

    /**
     * Execute when we detach ability from model
     * @param $objects
     */
    public function unSetUp(&$objects) {}

    /**
     * Execute when we delete objects
     * @param $objects
     */
    public function unlink(&$objects) {}


    /**
     * executes every time when model creating
     */
    public function bootstrap() {}

    /**
     * Use for dynamic publishing actions
     * @param string $action_name
     */
    protected function publishAction($action_name) {
        $this->_mixed_methods[$action_name] = array();
    }

    /**
     * published actions
     * @return array
     */
    public function getPublishedActions() {
        return $this->_mixed_methods;
    }

    public function preSave(&$changed, &$all) {}
    public function postSave(&$changed, &$all) {}


    static public function getInitialStructure(slModelStructure $structure) {
        return 'true';
    }

    /**
     * Used for check parameters count
     * @param $count
     * @param $params
     * @throws slModelException
     */
    public function requireParametersCount($count, &$params) {
        if (is_array($params) && count($params) < $count) {
            throw new slModelException('Method require at less '.$count.' parameters');
        }
    }

    /**
     * Use for check if we work with single object instead of model collection
     * @param $objects
     * @throws slModelException
     */
    public function requireModeSingle(&$objects) {
        if (!is_array($objects) || (count($objects) > 1)) {
            throw new slModelException('Method require single mode but you call it for collection');
        }
    }

}
