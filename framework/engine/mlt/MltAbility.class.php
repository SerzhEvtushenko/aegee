<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 03.07.12 11:57
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class MltAbility extends slModelAbility {

    public $_mixed_methods = array (
        'getTranslated'         => array(),
        'fillWithLanguage'      => array(),
    );

    private $_mlt_table     = null;

    public function setUp() {

        $this->_mlt_table = $this->_table . '_mlt';

        Q::create()->execSQL('SET FOREIGN_KEY_CHECKS=0');
        $table_structure = array(
            'columns' => array(
                'id' => array(
                    'type' => 'int(11) unsigned'
                ),
                'lang' => array(
                    'type' => 'varchar(3)'
                ),
            ),
            'indexes' => array(
                'lang'.$this->_mlt_table    => array(
                    'columns'   => array('lang', 'id'),
                    'type'      => 'unique'
                )
            ),
            'table' => $this->_mlt_table
        );

        $mlt_columns = array();
        if (!empty($this->_params['columns'])) {
            foreach($this->_params['columns'] as $column_name) {
                $table_structure['columns'][$column_name] = $this->_model->getStructure('columns/'.$column_name);
                $mlt_columns[] = $column_name;
            }
        }

        $diffs = slDBOperator::getInstance()->getDifferenceSQL($table_structure, $this->_mlt_table);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            foreach($diffs['sql'] as $type) {
                Q::execSQL($type);
            }
        }

        if (($options = slRouter::getCurrentRoute()->getVar(':options')) && $options['clean']) {
            $original_table_structure = $this->_model->getStructure()->get();
            foreach ($original_table_structure['columns'] as $column => $params) {
                if (in_array($column, $mlt_columns)) {
                    unset($original_table_structure['columns'][$column]);
                }
            }

            $diffs = slDBOperator::getInstance()->getDifferenceSQL($original_table_structure);
            if ($diffs['result'] === true) {
                Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
                foreach ($diffs['sql'] as $type) {
                    Q::execSQL($type);
                }
            }
        }

        if ($options = slRouter::getCurrentRoute()->getVar(':options')) {
            if (isset($options['fill']) && count($mlt_columns)) {
                $data = Q::create($this->_table)->select('id, '.implode(',', $mlt_columns))->exec();
                $insert = array();
                foreach($data as $one) {
                    $insert_item = $one;
                    foreach(MLT::getLanguagesAliases() as $lang) {
                        $insert_item['lang'] = $lang;
                        $insert[] = $insert_item;
                    }
                }
                Q::create($this->_mlt_table)->insert($insert)->exec();
            }
        }

    }

    public function bootstrap() {
        $this->_mlt_table = $this->_table . '_mlt';
    }

    public function unlink(&$objects) {
        $ids = array();
        foreach($objects as $object) {
            $ids[] = $object['id'];
        }
        Q::create($this->_mlt_table)->delete(array('id'=>$ids))->exec();
    }

    static private function getTranslatableFields($structure) {
        $res = array();
        foreach($structure as $column=>$info) {
            if (self::isTrasnlatableField($info)) {
                $res[$column] = $info;
            }
        }
        return $res;
    }

    static private function isTrasnlatableField($column) {
        if (!isset($column['type'])) return true;

        if ((strpos($column['type'], 'varchar')     === 0)
            || (strpos($column['type'], 'text')     !== false)
            || (strpos($column['type'], 'char')     === 0)
            || (strpos($column['type'], 'blob')     === 0)) {
            return true;
        }

        return false;
    }

    public function switchLanguage($lang) {
    }

    public function getTranslated($objects, $params) {

        $ids_to_load = array();
        $result = array();
        $params = $params[0];
        if (!is_array($params)) $params = array($params);
        if (!MLT::isLanguageAvailable($params)) {
            throw new slDBException('Trying to load unavailable language: '.$params);
        }

        foreach($objects as $object) {
            $ids_to_load[] = $object->id;
        }

        $objects_db_data = Q::create($this->_mlt_table)
            ->andWhere(array('id'=>$ids_to_load))
            ->andWhere(array('lang'=>$params))
            ->foldBy('id')
            ->indexBy('lang')
            ->exec();

        foreach($objects as $object) {
            foreach($object as $field=>$value) {
                foreach($params as $lang) {
                    $result[$object->id][$lang][$field] = isset($objects_db_data[$object->id][$lang][$field]) ? $objects_db_data[$object->id][$lang][$field] : null;
                }
            }
        }

        return count($ids_to_load) == 1 ? $result[$ids_to_load[0]] : $result;
    }

    /**
     * @param Q $query_part
     * @return Q
     */
    public function updateLoadQueryPart($query_part) {
        if (MLT::getModelsAutoTranslate()) {
            if (!$query_part) $query_part = Q::create();

            $fields = $this->_params['columns'];
            foreach($fields as &$field) { $field = $this->_mlt_table . '.' . $field; } unset($field);

            $query_part->leftJoin($this->_mlt_table,
                '('. $this->_table . '.id = '. $this->_mlt_table . '.id)'
                .'AND (' . $this->_mlt_table . '.lang = "'.MLT::getActiveLanguage() . '")'
            )
                ->select($this->_table . '.*, '.implode(',', $fields)
                .', '.$this->_mlt_table.'.lang __mlt_lang')
            ;

        }

        return $query_part;
    }

    public function fillWithLanguage($objects, $params) {
        $lang = MLT::getActiveLanguage();
        $langs = MLT::getLanguagesAliases();
        foreach($objects as $object) {
            foreach($langs as $l) {
                if ($l == $lang) continue;
                MLT::setActiveLanguage($l);
                $object->__mlt_lang = false;
                /**
                 * @var $object slModel
                 */
                $object->save(true, true);
            }
        }
        MLT::setActiveLanguage($lang);

    }

    public function setTranslatedData($data, $lang = false) {
        $q = Q::create($this->_mlt_table);
        $criteria = array(
            $this->_pk   => $this->_model[$this->_pk],
            'lang'       => MLT::getActiveLanguage()
        );

        if ($this->_model->__mlt_lang) {
            $q->update($data)->where($criteria);
        } else {
            $criteria += $data;
            $q->insert($criteria);
        }
        $q->exec();
    }

    static public function getInitialStructure(slModelStructure $structure) {
        $res = array();
        $fields = array_keys(self::getTranslatableFields($structure->get('columns')));
        $res['columns'] = $fields;
        return $res ? $res : true;
    }

}
