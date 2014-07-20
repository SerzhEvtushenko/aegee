<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Dec 18, 2009 12:50:21 AM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Work with objects as tree
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class TreeAbility extends slModelAbility {

    static private $_nodes      = array();
    static private $_loaded     = false;

    static private $_tree       = array();
    static private $_builded    = false;

    public  $_mixed_methods   = array(
        'nodeGetTree'       => array(),
        'nodeSetParent'     => array(),
        'nodeGetParentId'   => array(),
        'nodeGetRootPath'   => array(),
        'nodeGetNested'     => array(),
    );

    private $_field             = '_position';

    public function setUp() {
        $this->_model->getStructure()->addColumn('_node_id_parent', array(
            'type'      => 'int(11) unsigned',
            'default'   => '0',
            'not_null'  => true
        ));
        $this->_model->getStructure()->addColumn('_position', array(
            'type'      => 'int(11) unsigned',
            'default'   => '0',
            'not_null'  => true
        ));
        $this->_model->setStructure(array(
                'unique_field'  => '_node_id_parent'
            ),
            'abilities/sort'
        );
        $this->_model->getStructure()->saveStructure();
    }

    public function nodeSetParent(&$objects, $params) {
        $object = $objects[0];
        $id     = $object[$this->_pk];
        $pid    = is_object($params[0]) ? $params[0][$this->_pk] : $params[0];
        $where  = isset($params[1]) ? $params[1] : null;

        if ($pid == $object['_node_id_parent']) {
            if ($where !== null) $this->_model->moveAfterId($where);
            return $this->_model;
        }

        if ($where === false) {
            // exclude from list
            $current = Q::create($this->_table)->select(array($this->_pk, '_position'))->where(array($this->_pk=>$id))->one()->exec();

            Q::create($this->_table)->update($this->_field . ' = '. $this->_field .' -1')
                ->where(array($this->_field . '>' . $current[$this->_field]))
                ->where(array('_node_id_parent'=>$object['_node_id_parent']))->exec();
            Q::create($this->_table)->update(array($this->_field=>0, '_node_id_parent'=>$pid))->where(array($this->_pk=>$id))->exec();
            $this->_model[$this->_field] = 0;
            $this->_model['_node_id_parent'] = $pid;
            $this->_model->moveFirst();
        } else {
            $this->_model->moveAfterId($where);
        }

        return $this->_model;
    }

    public function nodeGetParentId(&$objects, $params) {
        $this->requireModeSingle($objects);
        if ($this->_model->isNew()) {
            $this->_model->isNew();
        }
        return $this->_model->_node_id_parent;
    }

    public function nodeGetTree(&$objects, $params) {
        $this->requireModeSingle($objects);
        
        if (count($params) && isset($params[0])) {
            $id = $params[0];  
        } else {
            if (empty($objects[0])) return array();
            $id = $objects[0][$this->_pk];
        }
        $this->_loadNodes();
        $this->_buildTree(self::$_tree);
        return $id == 0 ? self::$_tree : $this->_findNode($id, self::$_tree);
    }

    public function nodeGetRootPath(&$objects, $params) {
        $this->requireModeSingle($objects);
        $current = $objects[0];
        
        $path = array();
        $fields_to_select = array($this->_pk, '_node_id_parent');
        if (count($params)) {
//            vd($params);
            $fields_to_select = array_merge($fields_to_select, $params);
        }
        if (!isset($current['_node_id_parent'])) return array();
        while($current['_node_id_parent'] != 0) {
            $current = Q::create($this->_table)->select($fields_to_select)->where(array('id'=>$current['_node_id_parent']))->one()->exec();
            $path[] = $current;
        }
        return $path;
    }

    public function nodeGetNested(&$objects, $params) {
        $this->requireModeSingle($objects);
        $current = $objects[0];

        $this->_loadNodes();
        return $this->_getNested($current[$this->_pk]);
    }

    private function _getNested($id) {
        $res = array();
        if (isset(self::$_nodes[$id])) {
            foreach(self::$_nodes[$id] as $node) {
                $res[] = $node[$this->_pk];
                $res = array_merge($res, $this->_getNested($node[$this->_pk]));
            }
        }
        return $res;
    }

    private function _loadNodes() {
        if (self::$_loaded) return true;

        self::$_nodes = Q::create($this->_table)->foldBy('_node_id_parent')->orderBy('_position')->exec();
        self::$_loaded = true;
    }

    private function _buildTree(&$tree, $id_parent = 0) {
        if (self::$_builded) return true;
        if (isset(self::$_nodes[$id_parent])) {
            $tree = self::$_nodes[$id_parent];
            foreach($tree as $key=>$node) {
                $tree[$key]['nodes'] = array();
                $this->_buildTree($tree[$key]['nodes'], $node[$this->_pk]);
            }
        }
        if ($id_parent === 0) self::$_builded = true;
    }

    private function _findNode($id, &$tree) {
        foreach($tree as $node) {
            if ($node[$this->_pk] == $id) {
                return $node;
            } elseif (!empty($node['nodes'])) {
                if ($t = $this->_findNode($id, $node['nodes'])) {
                    return $t;
                }
            }
        }
        return null;
    }

    private function _parseID($params) {
        if (isset($params[0]) && ((count($params[0]) == 1) || is_object($params[0]))) {
            return is_object($params[0]) ? $params[0][$this->_pk] : $params[0];
        } else {
            throw new Exception('You need to specify 1 Node or ID');
        }
    }

}