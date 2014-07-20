<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Jan 24, 2011 01:29:23 AM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * History abilities. Versions model data.
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class HistoryAbility extends slModelAbility {

    public $_mixed_methods = array(
        'getHistory'        => array(),
        'getDataVersion'    => array(),
        'revertToVersion'   => array(),
    );

    public function setUp() {
        $table_name = $this->_model->getStructure('table').'_history';
        $table = slDBOperator::getInstance()->getTableStructure($table_name);
        if (!$table) {
            $table_structure = array(
                'table'      => $table_name,
                'columns'   => array(
                    'id'        => array(
                        'type'              => 'int(11) unsigned',
                        'auto_increment'    => true,
                        'not_null'          => true
                    ),
                    'datetime'  => array(
                        'type'          => 'datetime',
                        'not_null'      => true
                    ),
                    'id_object' => array(
                        'type'          => 'int(11) unsigned'
                    ),
                    'id_user'   => array(
                        'type'          => 'int(11) unsigned'
                    ),
                    'data'      => array(
                        'type'          => 'blob'
                    ),
                    'version'      => array(
                        'type'          => 'int(11)'
                    ),
                ),
                'indexes'      => array(
                    'primary'   => array(
                        'columns'   => array('id')
                    ),
                    'id_version_unique' => array(
                        'columns'   => array('id', 'version'),
                        'type'      => 'unique'
                    )
                )
            );
            $sql = slDBOperator::getInstance()->generateTableSQL($table_structure);
            Q::execSQL($sql);
        }
    }

    /**
     * Store current version of current model, on the next save we'll already have old version in the table
     * @param $changed
     * @param $all
     * @return bool
     */
    public function postSave(&$changed, &$all) {
        if (!$this->_model->isNew() && !empty($changed)) {
            $changed_data = $changed;
            if (!empty($this->_params['skip'])) {
                foreach($this->_params['skip'] as $field) if (array_key_exists($field, $changed_data)) unset($changed_data[$field]);
            }
            if (empty($changed_data)) return false;

            $new_version_id = Q::create($this->_table . '_history')
                ->select('version + 1 as v')
                ->orderBy('version DESC')
                ->limit(1)
                ->one()
                ->useValue('v')
                ->andWhere(array('id_object'=>$this->_model->getID()))
                ->exec();


            $data = array(
                'id_object' => $this->_model->getID(),
                'datetime'  => '#sql#NOW()',
                'data'      => serialize($changed_data),
                'id_user'   => slACL::getCurrentUser('id'),
                'version'   => $new_version_id ? $new_version_id : 1
            );
            Q::create($this->_table.'_history')->insert($data)->exec();
        }
    }

    /**
     * Return all history for current object
     * @return array|mixed
     */
    public function getHistory() {
        $versions = array();
        if (!$this->_model->isNew()) {
            $versions = Q::create($this->_table.'_history')
                    ->where(array('id_object'=>$this->_model->getID()))->exec();
            foreach($versions as $key=>$value) {
                $versions[$key]['data'] = unserialize($versions[$key]['data']);
            }
        }
        return $versions;
    }

    /**
     * Return data for specified version for current model
     * @param $objects
     * @param array $params
     * @return array|null
     */
    public function getDataVersion(&$objects, $params = array()) {
        $id_version = $params[0];
        $data = Q::create($this->_table . '_history')
            ->where(array('id_object'=>$this->_model->getID(), 'version'=>$id_version))
            ->one()
            ->exec();
        if (!empty($data)) {
            $current_data = $this->_model->toArray();
            $data['data'] = unserialize($data['data']);
            foreach($data['data'] as $key=>$value) {
                $current_data[$key] = $value;
            }
            return $current_data;
        } else {
            return null;
        }

    }

    /**
     * Overwrite all data in database with saved into history for specified version
     * @param $objects
     * @param array $params
     * @return bool
     */
    public function revertToVersion(&$objects, $params = array()) {
        $id_version = $params[0];
        if ($version_data = $this->getDataVersion($objects, $params)) {
            $this->_model->mergeData($version_data);
            $this->_model->save();
            return true;
        } else {
            return false;
        }
    }

}