<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Feb 10, 2011 23:29:23 AM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Meta information. Used for SEO.
 *
 * @version 1.0
 *
 * @method slModelCollection loadRelative($relation = null)
 *
 * @author Pavel Vodnyakov <pavel.vodnyakoff@gmail.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class MetainfoAbility extends slModelAbility{

    /**
     * @var array
     */
    static public $meta_info = array();

    public $_mixed_methods = array(
        'getMetaInfo'       => array(),
        'mergeMetaInfo'     => array(),
    );

    static private $_fields = array('meta_title','meta_description','meta_keywords');

    public function setUp() {
        foreach (self::$_fields as $field) {
            $this->_model->getStructure()->addColumn('_'.$field, array(
                'type'      => 'varchar(255)'
            ));
        }
    }

    /**
     * Return Metainfo for current object
     * @param $objects
     * @param null $params
     */
    public function getMetaInfo(&$objects, $params = null) {
        $this->requireModeSingle($objects);
        $res = array();
        foreach (self::$_fields as $field) {
            $res[$field] = $objects[0]['_'.$field];
        }
    }

    /**
     * Return objects metainfo merged with project metaingo
     * @static
     * @return array
     */
    static public function getMergedMetaInfo() {
        foreach (self::$_fields as $field ) {
            if (!isset(self::$meta_info[$field])) self::$meta_info[$field] = '';
        }
        return self::$meta_info;
    }

    public function preSave(&$changed, &$all) {
        if ((isset($changed['_meta_title']) && empty($changed['_meta_title'])) || (!isset($changed['_meta_title']) && empty($all['_meta_title']))) {
            $changed['_meta_title'] = isset($changed['title']) ? $changed['title'] : isset($all['title']) ? $all['title'] : '';
        }
        if ((isset($changed['_meta_keywords']) && empty($changed['_meta_keywords'])) || (!isset($changed['_meta_keywords']) && empty($all['_meta_keywords']))) {
            $changed['_meta_keywords'] = isset($changed['title']) ? $changed['title'] : isset($all['title']) ? $all['title'] : '';
        }
        if ((isset($changed['_meta_description']) && empty($changed['_meta_description'])) || (!isset($changed['_meta_description']) && empty($all['_meta_description']))) {
            $changed['_meta_description'] = strip_tags(isset($changed['description']) ? $changed['description'] : isset($all['description']) ? $all['description'] : '');
        }
    }

    public function mergeMetaInfo(&$objects, $params = null) {
        $this->requireModeSingle($objects);
        $overwrite = isset($params[0]) ? $params[0] : $this->_model->getStructure('abilities/metainfo/overwrite' , false);
        $glue = isset($params[1]) ? $params[1] : $this->_model->getStructure('abilities/metainfo/glue' , ' ');
        $direction = isset($params[2]) && in_array($params[2],array('before','after')) ? $params[2] : $this->_model->getStructure('abilities/metainfo/direction' , 'after');
        self::mergeMetaInfoWithArray($objects[0], $overwrite, $glue, $direction);
    }

    static public function mergeMetaInfoWithArray($array, $overwrite = false, $glue = ' | ', $direction = 'before') {
        foreach (self::$_fields as $field) {
            if (!isset($array['_'.$field])) continue;
            if (is_bool($overwrite) && $overwrite || !isset(self::$meta_info[$field])) {
                self::$meta_info[$field] = $array['_'.$field];
            } elseif(is_array($overwrite) && in_array($field,$overwrite)) {
                self::$meta_info[$field] = $array['_'.$field];
            } elseif (trim($array['_'.$field]) != '') {
                if ($direction == 'after') {
                    self::$meta_info[$field] .= $glue . $array['_'.$field];
                } elseif ($direction == 'before') {
                    self::$meta_info[$field] = $array['_'.$field] . $glue . self::$meta_info[$field];
                }
            }
        }
    }

}
