<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 14.08.2012 18:48:26
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability to work with Galleries.
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class GalleryAbility extends slModelAbility {

    public $_mixed_methods = array(
        'addGallery'    => array(),


        'getGalleries'  => array(),
        'getGalleriesFiles'  => array(),
        'deleteGalleryItem' => array(),
        'deleteGallery' => array(),
        'updateGallery'  => array(),
        'updateGalleryItems'  => array(),

        'updateGalleryItemZoom' => array()
    );

    private $_galleries_list_table      = null;
    private $_galleries_items_table     = null;
    private $_store_path                = null;
    private $_upload_path               = 'upload/';

    private function getGalleriesListTableStructure() {
        $galleries_list_table = array(
            'columns'   => array(
                'id'            => array(
                    'type'              => 'int(11) unsigned',
                    'auto_increment'    => 'true'
                ),
                'title'         => 'varchar(255)',
                'description'   => 'text',
                'id_object'     => 'int(11) unsigned',
            ),
            'indexes' => array(
                'primary'    => array(
                    'columns'   => array('id'),
                )
            ),
            'table' => $this->_galleries_list_table
        );

        if (isset($this->_params['custom_gallery_columns']) && is_array($this->_params['custom_gallery_columns'])) {
            $galleries_list_table['columns'] = array_merge($galleries_list_table['columns'], $this->_params['custom_gallery_columns']);
        }

        return $galleries_list_table;
    }

    private function getGalleriesItemsTableStructure() {
        $galleries_items_table = array(
            'columns'   => array(
                'id'            => array(
                    'type'              => 'int(11) unsigned',
                    'auto_increment'    => 'true'
                ),
                'link'          => 'varchar(255)',
                '_position'     => 'int(11) unsigned',
                'title'         => 'varchar(255)',
                'id_gallery'    => 'int(11) unsigned',
                'id_object'     => 'int(11) unsigned',
            ),
            'indexes' => array(
                'primary'    => array(
                    'columns'   => array('id'),
                )
            ),
            'table' => $this->_galleries_items_table
        );

        if (isset($this->_params['custom_item_columns']) && is_array($this->_params['custom_item_columns'])) {
            $galleries_items_table['columns'] = array_merge($galleries_items_table['columns'], $this->_params['custom_item_columns']);
        }
        if ($this->hasZoom()) {
            $galleries_items_table['columns'] = array_merge($galleries_items_table['columns'],array(
                'crop_zoom_dimensions' => 'varchar(255)',
                'crop_top'      => array(
                    'type' => 'int(11)',
                    'default' => 0
                ),
                'crop_left'     => array(
                    'type' => 'int(11)',
                    'default' => 0
                ),
                'crop_zoom'     => array(
                    'type' => 'int(11)',
                    'default' => 1
                )
            ));
        }

        return $galleries_items_table;
    }

    public function setUp() {
        $this->_galleries_list_table = $this->_table . '_galleries';
        $this->_galleries_items_table = $this->_table . '_galleries_items';

        $galleries_list_table = $this->getGalleriesListTableStructure();
        $galleries_items_table = $this->getGalleriesItemsTableStructure();

        $diffs = slDBOperator::getInstance()->getDifferenceSQL($galleries_list_table, $this->_galleries_list_table);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            foreach($diffs['sql'] as $type) {
                Q::execSQL($type);
            }
        }

        $diffs = slDBOperator::getInstance()->getDifferenceSQL($galleries_items_table, $this->_galleries_items_table);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            foreach($diffs['sql'] as $type) {
                Q::execSQL($type);
            }
        }
    }

    public function bootstrap() {
        $this->_galleries_list_table = $this->_table . '_galleries';
        $this->_galleries_items_table = $this->_table . '_galleries_items';
        $this->_store_path = SL::getDirUpload() . slInflector::pluralize(strtolower($this->_model->getClassName())) . '/galleries/';
        $this->_upload_path .= slInflector::pluralize(strtolower($this->_model->getClassName())) . '/galleries/';
    }

    private function isSingle() {
        return isset($this->_params['single']) && $this->_params['single'];
    }

    private function mergeGalleryInfo($params) {
        $gallery_info = array(
            'id_object' => $this->_model->id
        );

        $gallery_structure = $this->getGalleriesListTableStructure();
        foreach ($gallery_structure['columns'] as $column => $column_params) {
            if ($column == 'id' || $column == 'id_object') continue;
            if (isset($params[$column])) {
                $gallery_info[$column] = $params[$column];
            } elseif (is_array($column_params) && isset($column_params['default'])) {
                $gallery_info[$column] = $column_params['default'];
            }
        }

        return $gallery_info;
    }

    private function getGalleriesCount(){
        return Q::create($this->_galleries_list_table)->where(array('id_object' => $this->_model->id))
            ->select('count(*) cnt')
            ->one()->useValue('cnt')->exec();
    }

    public function addGallery($objects, $params) {
        $this->requireModeSingle($objects);

        $count = $this->getGalleriesCount();

        if ($this->isSingle() && $count > 0) {//don't create any gallery if has param "single" and existing galleries
            return $this->_model;
        }

        $gallery_info = $this->mergeGalleryInfo(isset($params[0]) ? $params[0] : array());

        if (empty($gallery_info['title']) && isset($this->_params['default_title'])) {
            $gallery_info['title'] = str_replace('%count%', $count + 1, $this->_params['default_title']);
        }

        Q::create($this->_galleries_list_table)->insert($gallery_info)->exec();

        return $this->_model;
    }

    public function updateGallery($objects, $params) {
        $this->requireModeSingle($objects);

        $info = $this->mergeGalleryInfo(isset($params[0]) ? $params[0] : array());

        Q::create($this->_galleries_list_table)->update($info)
            ->where(array('id_object'=>$this->_model->id, 'id' => $params[0]['id_gallery']))
            ->exec();
    }

    public function getGalleries($objects, $params = null) {
        $this->requireModeSingle($objects);

        if ($this->isSingle() && $this->getGalleriesCount() < 1) { //creating gallery when there's no galleries and "single" is true
            $this->addGallery($objects, array());
        }

        $q = Q::create($this->_galleries_list_table);
        $q->andWhere(array('id_object'=>$this->_model->id));
        if (!empty($params[0])) {
            $q->andWhere($params[0]);
        }
        return $q->exec();
    }

    public function deleteGallery($objects, $params = null) {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        if (!isset($params[0])) return false;
        $gallery_id = $params[0];

        slLocator::unlinkRecursive($this->_store_path.'gallery_'.$gallery_id);

        Q::create($this->_galleries_items_table)->where(array(
            'id_object' => $object->id,
            'id_gallery' => $gallery_id
        ))->delete()->exec();

        Q::create($this->_galleries_list_table)->where(array(
            'id' => $gallery_id,
            'id_object' => $object->id
        ))->delete()->exec();

        return true;
    }

    private function formAliasFilesArray($alias) {
        if (!isset($_FILES[$alias])) return false;

        if (is_array($_FILES[$alias])) {
            $files = array();
            foreach($_FILES[$alias]['name'] as $key=>$name) {
                $files[] = array(
                    'name'	=> $name,
                    'type'	=> $_FILES[$alias]['type'][$key],
                    'tmp_name'	=> $_FILES[$alias]['tmp_name'][$key],
                    'error'	=> $_FILES[$alias]['error'][$key],
                    'size'	=> $_FILES[$alias]['size'][$key],
                );
            }
        } else {
            $files = array($_FILES[$alias]);
        }

        return $files;
    }

    private function getLastItemsPosition($id_gallery) {
        $last_position = Q::create($this->_galleries_items_table)
            ->where(array(
                'id_object' => $this->_model->id,
                'id'        => $id_gallery
            ))
            ->select('_position p')
            ->orderBy('_position DESC')
            ->one()->useValue('p')
            ->exec();
        $last_position +=1;
        return $last_position;
    }

    static private function splitFilenameExt($name) {
        $return = array();
        $return['name'] = substr($name, 0, strrpos($name, '.'));
        $return['ext'] = substr($name, strlen($return['name'])+1);
        return $return;
    }

    private function mergeGalleryItemInfo($params) {
        $item_info = array(
            'id_object' => $this->_model->id
        );

        $gallery_structure = $this->getGalleriesItemsTableStructure();

        foreach ($gallery_structure['columns'] as $column => $column_params) {
            if ($column == 'id' || $column == 'id_object') continue;
            if (isset($params[$column])) {
                $item_info[$column] = $params[$column];
            } elseif (is_array($column_params) && isset($column_params['default'])) {
                $item_info[$column] = $column_params['default'];
            }
        }

        return $item_info;
    }

    private function getZoomSizeBackground() {
        return isset($this->zoom_size_params['background_color']) ? $this->zoom_size_params['background_color'] : '';
    }
    private function getZoomSize() {
        return isset($this->zoom_size_params['size']) ? $this->zoom_size_params['size'] : '';
    }
    private function getZoomSizeAlias() {
        return $this->zoom_size_alias;
    }
    private $zoom_size_params = array();
    private $zoom_size_alias = '';
    private $has_zoom = null;
    private function hasZoom() {
        if (!is_null($this->has_zoom)) return $this->has_zoom;
        if (!isset($this->_params['sizes'])) return $this->has_zoom = false;
        foreach ($this->_params['sizes'] as $alias => $size_params) {
            if (is_array($size_params) && isset($size_params['process']) && $size_params['process'] == 'zoom') {
                $this->zoom_size_params = $size_params;
                $this->zoom_size_alias  = $alias;
                return $this->has_zoom = true;
            }
        }
        return $this->has_zoom = false;
    }

    public function postSave(&$changed, &$all) {
        $galleries_db_list = Q::create($this->_galleries_list_table)->exec();

        foreach($galleries_db_list as $gallery) {
            $field_name             = 'gallery_'.$gallery['id'];
            $upload_path            = $this->_upload_path . $field_name . '/';
            $alias                  = '__'.$field_name;

            if (!($files = $this->formAliasFilesArray($alias))) {
                continue;
            }

            $last_position = $this->getLastItemsPosition($gallery['id']);
            slLocator::makeWritable($this->_store_path);

            foreach($files as $file) {
                $name = $ext = ''; //to be extracted by "extract" function below
                extract(self::splitFilenameExt($file['name']));

                while (is_file($this->_store_path . $field_name . '/' .$name.'.'.$ext)) {
                    $name .= '_1';
                }
                slLocator::makeWritable($this->_store_path . $field_name);
                $store_file_name = $this->_store_path . $field_name . '/' .$name.'.'.$ext;

                copy($file['tmp_name'], $store_file_name);
                @unlink($file['tmp_name']);

                $insert = array(
                    'link'       => $upload_path .$name.'.'.$ext,
                    'id_object'  => $this->_model->id,
                    'id_gallery' => $gallery['id'],
                    '_position'  => $last_position
                );

                if ($this->hasZoom()) {
                    $image_size = getimagesize($store_file_name);
                    $insert['crop_zoom_dimensions'] = $image_size[0].'x'.$image_size[1];
                }

                $insert = $this->mergeGalleryItemInfo($insert);
                Q::create($this->_galleries_items_table)->insert($insert)->exec();

                $last_position += 1;
            }
        }
    }

    public function updateGalleryItems($objects, $params) {

        $this->requireModeSingle($objects);
        $object = array_pop($objects);


        if (isset($params[0])) {
            $id_gallery = $params[0];
        }
        if (isset($params[1])) {
            $items_data = $params[1];
        }

        if (!isset($id_gallery) || !isset($items_data)) {
            return false;
        }

        Q::execSQL('START TRANSACTION');
        foreach ($items_data as $id_item => $data) {
            $data = $this->mergeGalleryItemInfo($data);

            Q::create($this->_galleries_items_table)->update($data)
                ->where(array('id' => $id_item, 'id_gallery' => $id_gallery,'id_object'  => $object->id))
                ->exec();
        }
        Q::execSQL('COMMIT');

        return true;
    }

    private function fixCropZoomDimensions(&$item) {
        if (!is_file($item['path'])) return;
        $image_size = getimagesize($item['path']);
        Q::create($this->_galleries_items_table)
            ->update(array('crop_zoom_dimensions'=>$image_size[0].'x'.$image_size[1]))
            ->where(array('id'=>$item['id']))->one()->exec();
        $item['crop_zoom_dimensions'] = $image_size[0].'x'.$image_size[1];
    }

    public function createSizes($id_gallery, $item, $force_recreate = false) {
        $path = $this->_store_path . 'gallery_'.$id_gallery .'/';
        if (!isset($item['path'])) return false;
	    $image_from = $original_image_path = $item['path'];

        if (!isset($this->_params['sizes']) || !is_array($this->_params['sizes'])) return array();
        $default_process = 'fitIn';
        $si = new slIMagickImageEngine();

        if ($this->hasZoom()) {
            $zoom_size_alias = $this->getZoomSizeAlias();
            $zoom_size       = $this->getZoomSize();
            $zoom_size_background = $this->getZoomSizeBackground();
            $path_to = $path . $zoom_size_alias .'/';
            $image_to = $path_to . $item['full_name'];
            if (!is_file($image_to) || $force_recreate) {
                slLocator::makeWritable($path_to);
                if (!preg_match( '#^\d+x\d+$#',$item['crop_zoom_dimensions'])) {
                    $this->fixCropZoomDimensions($item);
                }
                $si->viaSuperZoomCrop($original_image_path, $image_to, $item['crop_zoom_dimensions'], $item['crop_left'], $item['crop_top'], $zoom_size, 1, $item['crop_zoom'], $zoom_size_background);
            }
            $image_from = $image_to;
        }

        $return = array();
        foreach ($this->_params['sizes'] as $alias => $size_params) {
            $path_to = $path . $alias .'/';
            $image_to = $path_to . $item['full_name'];

            if (!($this->hasZoom() && ($alias == $this->getZoomSizeAlias()))) {
                if (is_array($size_params)) {
                    $size = $size_params['size'];
                    $method = $size_params['process'];
                } else {
                    $size = $size_params;
                    $method = $default_process;
                }

                if (!is_file($image_to) || $force_recreate) {
                    slLocator::makeWritable($path_to);
                    $si->{$method}($image_from, $image_to, $size);
                }
            }

            $return[$alias] = slLocator::getFileInfo($image_to);
        }
        return $return;
    }

    public function getGalleriesFiles($objects, $params = null) {
        $galleries_items = Q::create($this->_galleries_items_table)
            ->where(array('id_object'=>$this->_model->id))
            ->foldBy('id_gallery')->orderBy('_position ASC')
            ->exec();

        foreach($galleries_items as $id_gallery=>$items_list) {
            foreach($items_list as $item_key=>$item) {

                $file_path = SL::getDirWeb() . $item['link'];

                $item = array_merge($item, slLocator::getFileInfo($file_path));

                if (!empty($this->_params['sizes'])) {
                    $item['sizes'] = $this->createSizes($id_gallery, $item);
                }
                $galleries_items[$id_gallery][$item_key] = $item;
            }
        }

        return $galleries_items;
    }

    public function updateGalleryItemZoom($object, $params) {
        if (!$this->hasZoom()) return false;

        $this->requireModeSingle($object);
        $object = array_pop($object);

        $gallery_item_id = isset($params[0]) ? $params[0] : 0;
        if (empty($gallery_item_id)) {
            return false;
        }

        if (!isset($params[1])) {
            return false;
        }

        $c = array(
            'id' => $gallery_item_id,
            'id_object' => $object->id
        );

        Q::create($this->_galleries_items_table)->update($params[1])->where($c)->exec();
        $item = Q::create($this->_galleries_items_table)->where($c)->one()->exec();
        $item = array_merge($item, slLocator::getFileInfo(SL::getDirWeb().$item['link']));
        $item['sizes'] = $this->createSizes($item['id_gallery'], $item, true);

        return $item;
    }

    /*to be refactored*/
    public function deleteGalleryItem($objects, $params = null) {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        if (!isset($params[0])) return false;
        $gallery_item_id = $params[0];

        $gallery_item_entry = Q::create($this->_galleries_items_table)
            ->where(array(
            'id_object' => $object->id,
            'id'        => $gallery_item_id
        ))->one()->exec();

        if (!$gallery_item_entry) return false;

        Q::create($this->_galleries_items_table)->delete(array(
            'id_object' => $object->id,
            'id'        => $gallery_item_id
        ))->exec();

        if (isset($gallery_item_entry['link']) && !empty($gallery_item_entry['link'])){
            $file_name = SL::getDirWeb() . $gallery_item_entry['link'];
        }
        if (!empty($this->_params['sizes'])) {
            $files_to_delete = array($file_name);
            $file_info = slLocator::getFileInfo($file_name);
            foreach ($this->_params['sizes'] as $alias => $size) {
                if (isset($file_info['full_name'])) {
                    $files_to_delete[] = dirname($file_name).'/'.$alias.'/'.$file_info['full_name'];
                }
            }
            foreach($files_to_delete as $file) {
                unlink($file);
            }
            return true;
        } else {
            unlink($file_name);
            return true;
        }
    }

}
