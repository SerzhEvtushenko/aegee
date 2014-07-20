<?php
/*
 * @package SolveProject
 * @subpackage Database
 * created 02.12.2009 16:12:22
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability for relation between models
 *
 * @version 1.0
 *
 * @method slModelCollection loadRelative($relation = null)
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class RelativeAbility extends slModelAbility {

    /**
     * @var array types supported for now
     */
    private $_supported_types   = array(
        'many_to_one'   => true,
        'many_to_many'  => true,
        'one_to_many'   => true,
        'one_to_one'    => true
    );

    /**
     * Loading relative objects
     * @param $objects
     * @param $params
     * @return bool
     * @throws slDBException|slModelException
     */
    public function load(&$objects, $params) {
        $relations = array();
        if (isset($params[0]) && is_string($params[0])) {
            $tmp = explode(',', $params[0]);
            foreach($tmp as $relation_name) {
                $r = $this->_model->getStructure('relations/'.trim($relation_name));
                if ($r) {
                    $relations[$relation_name] = $r;
                } else {
                    throw new slModelException('No relation '.$relation_name.' defined for Model '.$this->_model->getClassName());
                }
            }
        }

        /**
         * @var $local_key
         * @var $local_field
         * @var $foreign_key
         * @var $foreign_field
         * @var $local_table
         * @var $foreign_table
         * @var $many_table
         * @var $type
         * @var $related_model
         * @var $alias
         */
        foreach($relations as $relation_name=>$r) {
            extract(slDBOperator::calculateRelationVariables($this->_model, $r, $relation_name));

            $ids_map        = array();
            $related_ids    = array();
            $value_is_array = substr($type, -7) == 'to_many' ? true : false;

            if ($value_is_array) {
                $local_ids      = array();
                foreach($objects as $object) {
                    if (is_null($object[$local_key])) continue;
                    $local_ids[]    = $object[$local_key];
                }
                $local_ids      = array_unique($local_ids);
                $fold_by        = $local_field;
                $use_value      = $foreign_field;

                if ($type == 'one_to_many') {
                    $many_table     = $foreign_table;
                    $fold_by        = $foreign_field;
                    $use_value      = $foreign_key;

                }
                // we select also "many_fields" if it specified in realtion description
                $fields_to_select = array($fold_by, $use_value);
                if (isset($r['many_fields'])) $fields_to_select = array_merge($fields_to_select, $r['many_fields']);
                $foreign_data    = Q::create($many_table)->select($fields_to_select)->foldBy($fold_by)->indexBy($foreign_key)->where(array($local_field=>$local_ids))->orderBy($foreign_field)->exec();
                // get IDs of related objects folded by IDs of current objects
                $ids_map = array();
                foreach($foreign_data as $key=>$item) {
                    $ids = array();
                    foreach($item as $k=>$v) {
                        if (isset($v[$use_value])) $ids[] = $v[$use_value];
                    }
                    $ids_map[$key] = $ids;
                    $related_ids = array_merge($related_ids, $ids);
                }
                

            } else {
                foreach($objects as $object) {
                    if (empty($object[$local_key])) continue;
                    $related_ids[$object[$local_key]]  = $object[$local_field];
                }
                // here is IDs of related objects indexed by IDs of current objects
                $ids_map        = $related_ids;
            }
            $related_ids    = array_unique($related_ids);

            if (empty($related_ids)) return false;


            if (count($related_ids)) {
                $related_q = Q::create($foreign_table)->where(array($foreign_key=>$related_ids))->indexBy($foreign_key);
                $related_data   = $related_q->exec();
            } else {
                $related_data   = array();
            }
            $hydrate_model = false;
            if (($related_model && (!isset($r['hydration']) || ($r['hydration'] == 'model'))) && !isset($r['use_value'])) {
                if ($value_is_array) {
//                    $related_collection = new slModelCollection(slModel::getModel($related_model), $related_data);
                    /**
                     * @var $related_collection slModelCollection
                     */
                    $related_collection = call_user_func(array($related_model, 'loadList'), $related_ids);
//                    vd($related_collection->getPKs());
                }
//                vd($related_collection->toArray());
                $hydrate_model = true;
            }

            // set data to the targets objects
            foreach($objects as $key=>$object) {
                $objects[$key][$alias] = null;

                if (!isset($ids_map[$object[$local_key]])) {
                    if ($hydrate_model) {
                        $objects[$key][$alias] = $value_is_array ? new slModelCollection(slModel::getModel($related_model)) : new $related_model();
                        $objects[$key][$alias]->setParentObject($this->_model);
                    }
                    continue;
                }
                if ($value_is_array) {
                    if ($hydrate_model) {
                        $array_value = $related_collection->getSubCollection($ids_map[$object[$local_key]]);
                        $array_value->setParentObject($this->_model);
                    } else {
                        $array_value = array();
                        foreach($ids_map[$object[$local_key]] as $rel_id) {
                            $value = $related_data[$rel_id];
                            if (isset($r['use_value'])) {
                                $value = $value[$r['use_value']];
                            }
                            if (isset($r['many_fields'])) {
                                foreach($r['many_fields'] as $mf_name) {
                                    $value[$mf_name] = isset($foreign_data[$object[$local_key]][$rel_id][$mf_name]) ? $foreign_data[$object[$local_key]][$rel_id][$mf_name] : null;
                                }
                            }
                            if (isset($r['index'])) {
                                if (!isset($$r['index'])) throw new slDBException('Unknowon index type');
                                $array_value[$value[$$r['index']]] = $value;
                            } else {
                                $array_value[]  = $value;
                            }
                        }
                    }
                    $objects[$key][$alias]     = $array_value;
                } else {
                    $value = isset($related_data[$ids_map[$object[$local_key]]]) ? $related_data[$ids_map[$object[$local_key]]] : null;

                    if ($hydrate_model) {
                        $model = new $related_model();
                        $model->setOriginalData($value);
                        $value = $model;
                    } else {
                        if (isset($r['use_value'])) {
                            $value = $value[$r['use_value']];
                        } elseif(isset($r['extract_fields'])) {
                            $fields_to_extract = explode(',', $r['extract_fields']);
                            foreach($fields_to_extract as $ef) {
                                $object[$ef] = $value[$ef];
                            }
                        }
                    }
                    $objects[$key][$alias] = $value;

                }
            }

        }

    }

    public function attach(&$objects, $rel_objects, $relation_name = null) {
        if (is_object($rel_objects) && !($objects instanceof slModelCollection)) {
            $rel_objects = array($rel_objects);
        } elseif (!is_array($rel_objects)) {
            $rel_objects = array($rel_objects);
        }
        if (!$relation_name) $relation_name = strtolower(get_class($rel_objects[0]));
        
        $relations = $this->_model->getStructure('relations');

        if (!isset($relations[$relation_name])) {
            $relation_name = slInflector::pluralize($relation_name);
        }
        if (!isset($relations[$relation_name])) {
            throw new slDBException('Cannot detect relation');
        }

        // categories
        /**
         * @var $local_key
         * @var $local_field
         * @var $foreign_key
         * @var $foreign_field
         * @var $local_table
         * @var $foreign_table
         * @var $many_table
         * @var $type
         */
        extract(slDBOperator::calculateRelationVariables($this->_model, $relations[$relation_name], $relation_name));

        $ids_map        = array();
        $related_ids    = array();
        $value_is_array = substr($type, -7) == 'to_many' ? true : false;

        if ($value_is_array) {
            $local_ids      = array();
            foreach($objects as $object) {
                $local_ids[]    = $object[$local_key];
            }
            $local_ids      = array_unique($local_ids);
            $fold_by        = $local_field;
            $use_value      = $foreign_field;

            if ($type == 'one_to_many') {
                $many_table     = $foreign_table;
                $fold_by        = $foreign_field;
                $use_value      = $foreign_key;

            }
        }

        $local_ids    = array();
        foreach($objects as $object) {
            $local_ids[] = $object[$local_key];
        }

        if (array_key_exists(0, $rel_objects) && !is_array($rel_objects[0]) && !is_object($rel_objects[0])) {
            $foreign_ids = $rel_objects;
        } else {
            $foreign_ids  = array();
            foreach($rel_objects as $object) {
                $foreign_ids[] = $object[$foreign_key];
            }
        }

        if (substr($type, -6) == 'to_one') {
            $data = array($foreign_field=>$foreign_ids[0]);
            Q::create($local_table)->update($data)->where(array($local_key=>$local_ids))->exec();
            //@todo update in othe cases
            foreach($objects as $object) {
                $object[$local_field] = $foreign_ids[0];
            }
        } elseif($type == 'one_to_many') {
            $data = array($local_field=>$local_ids[0]);
            Q::create($foreign_table)->update($data)->where(array($foreign_key=>$foreign_ids))->exec();
        } elseif($type == 'many_to_many') {
            $data = array();
            foreach($local_ids as $lid) {
                foreach($foreign_ids as $fid) {
                    $r_item = array(
                        $local_field    => $lid,
                        $foreign_field  => $fid,
                    );
                    if (isset($relations[$relation_name]['many_fields'])) {
                        foreach($relations[$relation_name]['many_fields'] as $mf_name) {
                            $r_item[$mf_name] = isset($rel_objects[$fid][$mf_name]) ? $rel_objects[$fid][$mf_name] : null;
                        }
                    }
                    $data[] = $r_item;
                }
            }
            Q::create($many_table)->delete(array($local_field=>$local_ids))->exec();
            if ($data) {
                Q::create($many_table)->replace($data)->exec();
            }
        }
    }

    public function detach(&$objects, $rel_objects, $relation_name = null) {

        $foreign_ids = false;
        if (is_object($rel_objects)) {
            if (!($objects instanceof slModelCollection)) {
                $rel_objects = array($rel_objects);
            }
            $rel_model = get_class($rel_objects[0]);
            if (!$relation_name) $relation_name = strtolower($rel_model);
            
        } else {
            if (!is_array($rel_objects) && !(is_null($rel_objects))) $rel_objects = array($rel_objects);
            $foreign_ids = $rel_objects;
        }

        $relations = $this->_model->getStructure('relations');

        if (!isset($relations[$relation_name])) {
            $relation_name = slInflector::pluralize($relation_name);
        }
        if (!isset($relations[$relation_name])) {
            throw new slDBException('Cannot detect relation');
        }

        // categories

        /**
         * @var $local_key
         * @var $local_field
         * @var $foreign_key
         * @var $foreign_field
         * @var $local_table
         * @var $foreign_table
         * @var $many_table
         * @var $type
         */
        extract(slDBOperator::calculateRelationVariables($this->_model, $relations[$relation_name], $relation_name));

        $ids_map        = array();
        $related_ids    = array();
        $value_is_array = substr($type, -7) == 'to_many' ? true : false;

        $local_ids    = array();
        foreach($objects as $object) {
            $local_ids[] = $object[$local_key];
        }
        $local_ids = array_unique($local_ids);
        if ($foreign_ids ===  false) {
            $foreign_ids  = array();
            foreach($rel_objects as $object) {
                $foreign_ids[] = $object[$foreign_key];
            }
        }

        
        if (substr($type, -6) == 'to_one') {
            $data = array($foreign_field=>null);
            Q::create($local_table)->update($data)->where(array($local_key=>$local_ids))->exec();
            foreach($objects as $object) {
                $object[$local_field] = null;
            }
        } elseif($type == 'one_to_many') {
            $data = array($local_field=>null);
            // check for unlink all related objects assigned to current objects
            //$where = ($foreign_ids === null) ? null : array($foreign_key=>$foreign_ids);
            //rEkViZiT done: perhaps that's why every record got NULL for the relative field in foreign table
            $where = array($local_field => $local_ids);
            Q::create($foreign_table)->update($data)->where($where)->exec();
        } elseif($type == 'many_to_many') {
            $data = array();

            if ($foreign_ids === null) {
                $data = array($local_field=>$local_ids);
            } else {
                foreach($local_ids as $lid) {
                    foreach($foreign_ids as $fid) {
                        $data[] = array(
                            $local_field    => $lid,
                            $foreign_field  => $fid,
                        );
                    }
                }
            }
            Q::create($many_table)->delete($data)->exec();
        }
    }

}
