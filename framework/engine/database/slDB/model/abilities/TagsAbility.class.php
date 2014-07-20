<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Dec 17, 2009 17:29:56 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability to attach and use tags for object
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class TagsAbility extends slModelAbility {

    private $_tags_scope_table = 'tags';

    public $_mixed_methods = array (
        'addTag'            => array(),
        'removeTag'         => array(),
        'getTags'           => array(),
        'removeAllTags'     => array(),
    );

    public function setUp() {
        $scope = isset($this->_params['scope']) ? $this->_params['scope'] : 'global';
        $this->_tags_scope_table = 'tags_scope_'.$scope;

    	Q::create()->execSQL('SET FOREIGN_KEY_CHECKS=0');
    	$table_sql = slDBOperator::getInstance()->generateTableSQL(array(
			'columns' => array(
				'id' => array(
					'type' => 'int(11) unsigned',
					'auto_increment' => 'true'
				),
				'title'   => array(
					'type' => 'varchar(255)'
				)
			),
			'indexes' => array(
				'primary' => array(
					'columns' => 'id',
					'type'    => 'primary'
				),
				'unique' => array(
					'columns' => 'title',
					'type'    => 'unique'
				)
			),
			'table'   => $this->_tags_scope_table
		));

		$table_sql = str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$table_sql);
		Q::create()->execSQL($table_sql);
		$table_sql = slDBOperator::getInstance()->generateTableSQL(array(
			'columns' => array(
				'id_object'   => array(
					'type' => 'int(11) unsigned',
					'not_null' => 'true'
				),
				'id_tag' => array(
					'type' => 'int(11) unsigned',
                    'not_null' => 'true'
				)
			),
			
			'table' => 'tags_'.$this->_table,
			'indexes' => array(
				'unique'  => array(
					'columns' => array('id_object','id_tag'),
					'type'    => 'unique'
				)
			),
			'constraints' => array(
				0 => array(
					'local_field' => 'id_tag',
					'foreign_field' => 'id',
					'foreign_table' => $this->_tags_scope_table,
					'on_delete' => 'CASCADE'
				),
				1 => array(
					'local_field' => 'id_object',
					'foreign_field' => 'id',
					'foreign_table' => $this->_table,
					'on_delete' => 'CASCADE'
				)
			)
		));
		
		$table_sql = str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$table_sql);
		Q::create()->execSQL($table_sql);
		
		Q::create()->execSQL('SET FOREIGN_KEY_CHECKS=1');
    }

    public function bootstrap() {
        $scope = isset($this->_params['scope']) ? $this->_params['scope'] : 'global';
        $this->_tags_scope_table = 'tags_scope_'.$scope;

        $this->publishAction('addTag');
    	$this->publishAction('removeTag');    	
    	$this->publishAction('getTags');
    	$this->publishAction('removeAllTags');
    }
    
    public function addTag(&$objects,$params) {
    	$this->requireModeSingle($objects);
    	
    	if (!isset($params[1])) {
    		$tags = explode(',',trim($params[0]));
    	} else {
    		$tags = $params;
    	}
    	$ids = Q::create($this->_tags_scope_table)->where(array('title'=>$tags))->indexBy('title')->useValue('id')->exec();
    	$new_tags = array();
    	foreach ($tags as $tag) {
    		if (!isset($ids[$tag])) {
    			$new_tags[] = array('title'=>$tag);
    		}				
    	}
    	if (count($new_tags)) {
    		Q::create($this->_tags_scope_table)->insert($new_tags)->exec();
            $ids = Q::create($this->_tags_scope_table)->where(array('title'=>$tags))->indexBy('title')->useValue('id')->exec();
        }

    	$data = array();
    	foreach ($tags as $tag) {
			$data[] = array('id_object'=>$objects[0]['id'],'id_tag'=>$ids[$tag]);
    	}
    	Q::create('tags_'.$this->_table)->replace($data)->exec();
    }
    
    public function getTags(&$objects) {
    	$ids = array();
    	foreach ($objects as $object) {
    		$ids[] = $object['id'];
    	}
    	
    	$tags = Q::create($this->_tags_scope_table.' t')->select('*, tt.id_object')->leftJoin('tags_'.$this->_table.' tt','tt.id_tag = t.id')->where(array('tt.id_object'=>$ids))->useValue('title')->exec();
        return $tags;
    }
    
    public function removeTag(&$objects,$params) {
    	
    	if (!isset($params[1])) {
    		$tags = explode(',',$params[0]);
    	} else {
    		$tags = $params;
    	}

    	$tag_ids = Q::create($this->_tags_scope_table)->where(array('title'=>$tags))->useValue('id')->exec();
    	$obj_ids = array();
		foreach ($objects as $object) {
				$obj_ids[] =$object['id'];	
			}
		Q::create('tags_'.$this->_table)->delete()->where(array('id'=>$obj_ids,'id_tag'=>$tag_ids))->exec();
		
		foreach ($objects as &$object) {
			if (isset($object['tags'])) {
				foreach ($object['tags'] as $j=>$tag) {
					if (in_array($tag,$tags)) {
						unset ($object['tags'][$j]);
					}
				}
				if (!count($object['tags'])) {
					unset($object['tags']);
				} 
			}		
		}
    }
    
    public function removeAllTags(&$objects) {
    	$ids = array();
    	foreach ($objects as $object) {
    		$ids[] = $object['id'];	
    	}
    	Q::create('tags_'.$this->_table)->where(array('id_object'=>$ids))->delete()->exec();
    	foreach ($objects as &$object) {
    		unset($object['tags']);
    	}
    }

    public function postLoad(&$objects, $params = array()) {
        $this->load($objects, $params);
    }

    public function load(&$objects, $params = array()) {
    	$ids = array();
    	foreach ($objects as $object) {
			$ids[] = $object['id'];
    	}
    	$tags = Q::create($this->_tags_scope_table.' t')->select('*, tt.id_object')->leftJoin('tags_'.$this->_table.' tt','tt.id_tag = t.id')->where(array('tt.id_object'=>$ids))->useValue('title')->indexBy('id')->foldBy('id_object')->exec();
    	if (count($tags)) {
    		$tmp = array();
    		foreach ($objects as $i=>$object) {
    			$tmp[$i] = $object;
    			$tmp[$i]['tags'] = isset($tags[$object['id']]) ? $tags[$object['id']] : array();
    		}
    		$objects = $tmp;
    	}
    }
    
    public function postSave(&$changed, &$data) {
        if (!empty($changed['tags'])) {
            if (is_array($changed['tags'])) {
                $tags = $changed['tags'];
            } else {
                $tags = explode(',', $changed['tags']);
            }
            $this->_model->removeAllTags();
            foreach($tags as $tag) {
                $this->_model->addTag(trim($tag));
            }
        }
    }

    static public function getModelTags($model_name) {
        $table = 'tags_scope_'.$model_name;
        return Q::create($table)->exec();
    }
	
}