<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Apr 08, 2010 17:17:16 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability to sort objects in database
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class SortAbility extends slModelAbility {

    public $_mixed_methods = array(
        'moveBack'              => array(),
        'moveForward'           => array(),
        'moveFirst'             => array(),
        'moveLast'              => array(),
        'moveAfterPosition'     => array(),
        'moveAfterId'           => array(),
        'getPosition'           => array(),
        'getBackId'             => array(),
        'rebuildPositions'      => array(),

    );

    private $_field         = '_position';

    private $_unique_c      = false;
    private $_unique_field  = false;

    public function setUp() {
        try {
            $this->_model->getStructure()->addColumn('_position', array(
                'type'      => 'int(11) unsigned',
                'default'   => '0'
            ));
            slDBOperator::getInstance(true)->updateDBFromStructure($this->_model->getModelName());
        } catch(Exception $e) {
            vd($e->getMessage());
        }

        $this->rebuildPositions();
    }


    public function bootstrap() {
//        $this->publishAction('rebuildPositions');
    }

    public function preSave(&$changed, &$all) {
        if (empty($all[$this->_field])) {
            $changed[$this->_field] = $this->getNewNumber();
        }
    }

    public function moveForward(&$objects, $params) {
        $this->_moveTo($objects, 'forward');
    }

    public function moveBack(&$objects, $params) {
        $this->_moveTo($objects, 'back');
    }

    public function moveFirst(&$objects, $params) {
        $this->_moveTo($objects, 'first');
    }

    public function moveLast(&$objects, $params) {
        $this->_moveTo($objects, 'last');
    }

    public function getPosition(&$objects, $params = array()) {
        $this->requireModeSingle($objects);
        return $this->_model->getRawValue($this->_field);
    }


    public function getPreviousId(&$objects, $params) {
        $this->requireModeSingle($objects);

        $unique_c = $this->_model->getStructure('abilities/sort/unique_field');
        $c = new C();
        if ($unique_c) {
            $c->andWhere(array($unique_c=>$this->_model[$unique_c]));
        }

        return Q::create($this->_table)
                ->orderBy($this->_field .' DESC')
                ->where($this->_field . ' < '. $this->_model[$this->_field])
                ->andWhere($c)
                ->one()
                ->useValue($this->_pk)
                ->exec();
    }

    public function getNewNumber() {
        $number = Q::create($this->_table)
            ->select($this->_field .' +1 as pos')
            ->orderBy($this->_field . ' DESC')
            ->limit(1)
            ->one()
            ->useValue('pos')
            ->andWhere($this->_unique_c)
            ->exec();
        return $number ? $number : 1;
    }

    public function rebuildPositions(&$objects = null, $params = null) {
        $this->_detectUniqueC();

        $c = new C();
        if (isset($params[0])) {
            $c->andWhere($params[0]);
        }
        if (!$this->_unique_c) {
            Q::execSQL('begin');
            Q::execSQL('SET @a:=0');
            Q::create($this->_table)->update($this->_field.' = (SELECT @a:=@a+1 )')->orderBy($this->_field)->where($c)->exec();
            Q::execSQL('SET @a:=0');
            Q::execSQL('commit');
        } else {
            $uniques = Q::create($this->_table)->select('DISTINCT '.$this->_unique_field)->useValue($this->_unique_field)->exec();
            Q::execSQL('begin');
            foreach($uniques as $uv) {
                Q::execSQL('SET @a:=0');
                Q::create($this->_table)->update($this->_field.' = (SELECT @a:=@a+1 )')->andWhere(array($this->_unique_field => $uv))->orderBy($this->_field)->exec();
                Q::execSQL('SET @a:=0');                
            }
            Q::execSQL('commit');
        }

        return true;
    }


    private function _detectUniqueC() {
        $this->_unique_field = $this->_model->getStructure('abilities/sort/unique_field');
        $this->_unique_c = $this->_unique_field ? array($this->_unique_field=>$this->_model[$this->_unique_field] ? $this->_model[$this->_unique_field] : 0) : false;
    }

    private function _moveTo($objects, $where) {
        $this->requireModeSingle($objects);
        $this->_detectUniqueC();

        $object = array_pop($objects);
		if (!isset($object[$this->_field])) {
            $object[$this->_field] = 0;
            if ($where == 'back') {
                $where = 'first';
            } elseif ($where == 'forward') {
                $where = 'last';
            }
        }

        $q      = Q::create($this->_table)->one()->select(array($this->_field, 'id'))->andWhere($this->_unique_c);
        $pos    = false;

        if ($where == 'back') {
			$pos = $q->andWhere($this->_field.' < '.$object[$this->_field])->orderBy($this->_field.' DESC')->exec();
		} elseif ($where == 'forward') {
			$pos = $q->andWhere($this->_field.' > '.$object[$this->_field])->orderBy($this->_field)->exec();
		} elseif ($where == 'first') {
            if ($object[$this->_field] > 0) {
                $this->_excludeIdFromList($object[$this->_pk]);
                Q::create($this->_table)->update(array($this->_field=>0))->where(array($this->_pk=>$object[$this->_pk]))->exec();
            }
            Q::create($this->_table)->andWhere($this->_unique_c)->update($this->_field . ' = ' . $this->_field . ' +1')->exec();
            $this->_model[$this->_field] = 1;
            return true;
        } elseif ($where == 'last') {
            $pos = Q::create($this->_table)->select($this->_field.' as pos')->orderBy($this->_field .' DESC')->one()->useValue('pos')->exec();
            if ($pos == $object[$this->_pk]) {
                return true;
            }
            
            if ($object[$this->_field] > 0) {
                $this->_excludeIdFromList($object[$this->_pk]);
                $pos = Q::create($this->_table)->select($this->_field.' as pos')->orderBy($this->_field .' DESC')->one()->useValue('pos')->exec();
            }
            Q::create($this->_table)
                ->where(array($this->_pk=>$object[$this->_pk]))
                ->update($this->_field . ' = ' . ($pos+1))
                ->exec();
            return true;
        }
        
        if ($pos) {
            Q::create($this->_table)->update(array($this->_field=>$pos[$this->_field]))->where(array($this->_pk=>$object[$this->_pk]))->exec();
            Q::create($this->_table)->update(array($this->_field=>$object[$this->_field]))->where(array($this->_pk=>$pos[$this->_pk]))->exec();            
        }
    }

    public function moveAfterId($objects, $params) {
        $this->requireModeSingle($objects);
        $this->_detectUniqueC();
        $object = $objects[0];

        $after_id = is_scalar($params[0]) ? $params[0] : $params[0][$this->_pk];
        if (!$after_id) {
            $this->_moveTo($objects, 'first');
            return true;
        }

        if ($object[$this->_pk] == $after_id) return true;
        
        $where_current = array($this->_pk => $object[$this->_pk]);
        if ($this->_unique_field) {
            $this->_excludeIdFromList($object[$this->_pk]);
            $after_data = Q::create($this->_table)->select($this->_pk, $this->_field, $this->_unique_field)->one()->where(array($this->_pk=>$after_id))->exec();
            if ($after_data[$this->_unique_field] !== $object[$this->_unique_field]) {
                Q::create($this->_table)
                    ->update(array($this->_unique_field=>$after_data[$this->_unique_field]))
                    ->where($where_current)
                    ->exec();
                $this->_unique_c = array($this->_unique_field=>$after_data[$this->_unique_field]);
            }
            Q::create($this->_table)
                ->update($this->_field . ' = '. $this->_field . '+1')
                ->where($this->_unique_c)
                ->andWhere($this->_field . ' > ' . $after_data[$this->_field])
                ->exec();
            Q::create($this->_table)
                ->update(array($this->_field=>$after_data[$this->_field]+1))
                ->where($where_current)
                ->exec();
            
        }

        return true;
    }

    public function getBackId(&$objects, $params) {
        $this->requireModeSingle($objects);
        $this->_detectUniqueC();

        return Q::create($this->_table)
                ->orderBy($this->_field .' DESC')
                ->where($this->_field . ' < '. $this->_model[$this->_field])
                ->andWhere($this->_unique_c)
                ->one()
                ->useValue($this->_pk)
                ->exec();
    }    

    private function _excludeIdFromList($id) {
        $this->_detectUniqueC();

        $current = Q::create($this->_table)->select(array($this->_pk, $this->_field))->where(array($this->_pk=>$id))->one()->exec();
        if ($current[$this->_field] < 1) return true;
        
        Q::create($this->_table)->update($this->_field . ' = '. $this->_field .' -1')
            ->where(array($this->_field . '>' . $current[$this->_field]))
            ->where($this->_unique_c)->exec();
        Q::create($this->_table)->update(array($this->_field=>0))->where(array($this->_pk=>$id))->exec();
    }
}