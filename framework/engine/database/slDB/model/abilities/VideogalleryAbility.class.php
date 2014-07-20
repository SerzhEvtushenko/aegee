<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rekvizit
 * Date: 28/12/2012
 * Time: 01:12
 * To change this template use File | Settings | File Templates.
 */
class VideogalleryAbility extends slModelAbility {
    public $_mixed_methods = array(
        'addGallery' => array(),


        'getVideogalleries' => array(),
        'getVideogalleriesFiles' => array(),
        'deleteVideogalleryItem' => array(),
        'deleteVideogallery' => array(),
        'updateVideogallery' => array(),
        'updateVideogalleryItems' => array(),
    );

    private $_galleries_list_table = null;
    private $_galleries_items_table = null;
    private $_store_path = null;
    private $_upload_path = 'upload/';

    public function setUp() {
        $this->_galleries_list_table = $this->_table . '_videogalleries';
        $this->_galleries_items_table = $this->_table . '_videogalleries_items';


        $galleries_list_table = array(
            'columns' => array(
                'id' => array(
                    'type' => 'int(11) unsigned',
                    'auto_increment' => 'true'
                ),
                'title' => 'varchar(255)',
                'description' => 'text',
                'id_object' => 'int(11) unsigned',
            ),
            'indexes' => array(
                'primary' => array(
                    'columns' => array('id'),
                )
            ),
            'table' => $this->_galleries_list_table
        );

        $galleries_items_table = array(
            'columns' => array(
                'id' => array(
                    'type' => 'int(11) unsigned',
                    'auto_increment' => 'true'
                ),
                'link' => 'varchar(255)',
                'converted_link' => 'varchar(255)',
                'thumbnail_link' => 'varchar(255)',
                '_position' => 'int(11) unsigned',
                'title' => 'varchar(255)',
                'id_gallery' => 'int(11) unsigned',
                'id_object' => 'int(11) unsigned',
            ),
            'indexes' => array(
                'primary' => array(
                    'columns' => array('id'),
                )
            ),
            'table' => $this->_galleries_items_table
        );

        $diffs = slDBOperator::getInstance()->getDifferenceSQL($galleries_list_table, $this->_galleries_list_table);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($diffs['sql'] as $type) {
                Q::execSQL($type);
            }
        }

        $diffs = slDBOperator::getInstance()->getDifferenceSQL($galleries_items_table, $this->_galleries_items_table);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($diffs['sql'] as $type) {
                Q::execSQL($type);
            }
        }

    }

    public function bootstrap()
    {
        $this->_galleries_list_table = $this->_table . '_videogalleries';
        $this->_galleries_items_table = $this->_table . '_videogalleries_items';
        $this->_store_path = SL::getDirUpload() . slInflector::pluralize(strtolower($this->_model->getClassName())) . '/galleries/';
        $this->_upload_path .= slInflector::pluralize(strtolower($this->_model->getClassName())) . '/galleries/';
    }


    public function addVideogallery($objects, $params)
    {
        $this->requireModeSingle($objects);
        $gallery_info = array(
            'id_object' => $this->_model->id
        );

        $count = Q::create($this->_galleries_list_table)->where('id_object = ' . $this->_model->id)
            ->select('count(*) cnt')
            ->one()->useValue('cnt')->exec();

        if (isset($this->_params['single']) && $this->_params['single'] && $count > 0) {
            //don't create any gallery if has param "single" and existing galleries
            return $this->_model;
        }

        if (isset($params[0]['title'])) {
            $gallery_info['title'] = $params[0]['title'];
        } else {
            $count += 1;
            $gallery_info['title'] = 'Video Gallery' . $count;
        }
        $gallery_info['description'] = isset($params[0]['description']) ? $params[0]['description'] : '';
        Q::create($this->_galleries_list_table)->insert(array(
            $gallery_info
        ))->exec();
        return $this->_model;
    }

    public function updateVideogallery($objects, $params)
    {
        $this->requireModeSingle($objects);
        $info['title'] = isset($params[0]['title']) ? $params[0]['title'] : '';
        $info['description'] = isset($params[0]['description']) ? $params[0]['description'] : '';
        Q::create($this->_galleries_list_table)->update($info)
            ->where(array('id_object' => $this->_model->id, 'id' => $params[0]['id_gallery']))
            ->exec();
    }

    public function updateVideogalleryItems($objects, $params)
    {

        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        if (isset($params[0])) {
            $id_gallery = $params[0];
        }
        if (isset($params[1])) {
            $positions = $params[1];
        }

        $texts = isset($params[2]) ? $params[2] : array();

        if (!isset($id_gallery) || !isset($positions)) {
            return false;
        }

        Q::execSQL('START TRANSACTION');
        foreach ($positions as $id_gallery_item => $position) {

            Q::create($this->_galleries_items_table)->update(array(
                '_position' => $position,
                'title' => isset($texts[$id_gallery_item]) ? $texts[$id_gallery_item] : ''
            ))->where(array(
                    'id' => $id_gallery_item,
                    'id_gallery' => $id_gallery,
                    'id_object' => $object->id
                ))->exec();
        }
        Q::execSQL('COMMIT');

        return true;
    }

    public function getVideogalleries($objects, $params = null)
    {
        $this->requireModeSingle($objects);
        $q = Q::create($this->_galleries_list_table);
        $q->andWhere(array('id_object' => $this->_model->id));
        if (!empty($params[0])) {
            $q->andWhere($params[0]);
        }
        $return = $q->exec();
        if (isset($this->_params['single']) && $this->_params['single'] && count($return) < 1) {
            //creating new gallery for "single" param if there's no gallery
            $new_gallery_data = array(
                'id_object' => $this->_model->id,
                'title' => 'Video Gallery' //date('Y-m-d H:i:s')
            );
            $id_gallery = Q::create($this->_galleries_list_table)->insert($new_gallery_data)->exec();
            $new_gallery_data['id'] = $id_gallery;
            $new_gallery_data['description'] = null;
            $return = array(0 => $new_gallery_data);
        }
        return $return;
    }

    public function getVideogalleriesFiles($objects, $params = null)
    {
        $q = Q::create($this->_galleries_list_table);
        if (!empty($params[0])) {
            $q->andWhere($params[0]);
        }
        $gals = $q->exec();

        $gals_files = Q::create($this->_galleries_items_table)
            ->where(array('id_object' => $this->_model->id))
            ->foldBy('id_gallery')
            ->orderBy('_position ASC')
            ->exec();
        foreach ($gals_files as $id_gallery => $files_list) {
            foreach ($files_list as $file_key => $file) {
                $file_item_id = $file['id'];
                $file_item_position = $file['_position'];
                $file_item_title = $file['title'];
                $file_item_original_link = $file['link'];
                $file_item_converted_link = $file['converted_link'];

                $file = SL::getDirWeb() . $file['thumbnail_link'];

                $file_name = substr($file, strrpos($file, '/') + 1);
                $file_ext = substr($file_name, ($ep = strrpos($file_name, '.')) + 1);
                $file_name = substr($file_name, 0, $ep);

                $path = $this->_store_path . 'videogallery_' . $id_gallery . '/';

                if (!is_dir($path)) {
                    slLocator::makeWritable($path, true);
                }

                $item = slLocator::getFileInfo($file);
                $item['id'] = $file_item_id;
                $item['title'] = $file_item_title;
                $item['_position'] = $file_item_position;
                $item['original_link'] = $file_item_original_link;
                $item['converted_link'] = $file_item_converted_link;

                if (!empty($this->_params['thumbnails'])) {
                    $this->_params['sizes'] = $this->_params['thumbnails'];
                    $item['thumbnails'] = FilesAbility::makeThumbnails($file, $this->_params, $path, $file_name, $file_ext);
                }
                $gals_files[$id_gallery][$file_key] = $item;
            }
        }
        return $gals_files;
    }

    public function postSave(&$changed, &$all)
    {
        $galleries_db_list = Q::create($this->_galleries_list_table)->exec();
        foreach ($galleries_db_list as $gallery) {
            $field_name = 'videogallery_' . $gallery['id'];
            $upload_path = $this->_upload_path . $field_name . '/';
            $alias = '__' . $field_name;

            if (isset($_FILES[$alias])) {
                if (is_array($_FILES[$alias])) {
                    $files = array();
                    foreach ($_FILES[$alias]['name'] as $key => $name) {
                        $files[] = array(
                            'name' => $name,
                            'type' => $_FILES[$alias]['type'][$key],
                            'tmp_name' => $_FILES[$alias]['tmp_name'][$key],
                            'error' => $_FILES[$alias]['error'][$key],
                            'size' => $_FILES[$alias]['size'][$key],
                        );
                    }
                } else {
                    $files = array($_FILES[$alias]);
                }

                $last_position = Q::create($this->_galleries_items_table)
                    ->where(array(
                        'id_object' => $this->_model->id,
                        'id' => $gallery['id']
                    ))
                    ->select('_position p')
                    ->orderBy('_position DESC')
                    ->one()->useValue('p')
                    ->exec();
                $last_position += 1;
                slLocator::makeWritable($this->_store_path);

                foreach ($files as $file) {
                    $original_name = substr($file['name'], 0, strrpos($file['name'], '.'));
                    $original_ext = substr($file['name'], strlen($original_name) + 1);
//                    $name = str_replace(' ','_',$original_name);
                    $name = md5(time().rand());
                    $ext = $original_ext;
                    while (is_file($this->_store_path . $field_name . '/' . $name . '.' . $ext)) {
                        $name .= '_1';
                    }
//                    vd($this->_store_path.$field_name);
                    slLocator::makeWritable($this->_store_path . $field_name);
                    $store_file_name = $this->_store_path . $field_name . '/' . $name . '.' . $ext;
                    copy($file['tmp_name'], $store_file_name);

                    @unlink($file['tmp_name']);
                    Q::create($this->_galleries_items_table)
                        ->insert(array(
                            //dhzi
                            'link' => $upload_path . $name . '.' . $ext,

                            'converted_link' => $this->getConvertedLink($this->_store_path . $field_name, $store_file_name, $name),
                            'thumbnail_link' => $this->getThumbnailLink($this->_store_path . $field_name, $store_file_name, $name),

                            'id_object' => $this->_model->id,
                            'id_gallery' => $gallery['id'],
                            '_position' => $last_position
                        ))
                        ->exec();
                    $last_position += 1;
                }

            }
        }
    }

    private function getConvertedLink($path, $store_file_name , $name) {
        $converted_filename = $name.'.flv';
        $converted_file_path = $path.'/converted/'.$converted_filename;
        if (is_file($converted_file_path)) {
            return str_replace(SL::getDirWeb(),'',$converted_file_path);
        }

        set_time_limit(0);

        slLocator::makeWritable($path.'/converted/');

        $command = SL::getProjectConfig('ffmpeg_bin') . ' -i ' . $store_file_name;
        $command .= ' -ab 100 -ar 44100 -b:v 700k -r 25 -s '.$this->_params['size'].' -f flv ';
        $command .= $converted_file_path;
        exec($command); SL::log($command,'video');
        return str_replace(SL::getDirWeb(),'',$converted_file_path);
    }

    private function getThumbnailLink($path, $store_file_name , $name) {
        slLocator::makeWritable($path.'/thumbnails/');

        $thumbnail_filename = $name.'.jpg';
        $thumbnail_filepath = $path.'/thumbnails/' . $thumbnail_filename;

        if (is_file($thumbnail_filepath)) {
            return str_replace(SL::getDirWeb(),'',$thumbnail_filepath);
        }

        $command = SL::getProjectConfig('ffmpeg_bin') . ' -i ' . SL::getDirWeb().$this->getConvertedLink($path, $store_file_name, $name);

        $command .= ' -y -r 1 -vframes 1 -ss 1 ' . $thumbnail_filepath;
        exec($command); SL::log($command,'video');

        return str_replace(SL::getDirWeb(),'',$thumbnail_filepath);
    }

    private function _getFolderHash($value)
    {
        if (!intval($value[0])) {
            $int = ord($value[0]) % 10;
        } else {
            $int = $value[0] % 10;
        }
        return $int . '/' . $value;
    }

    public function deleteVideogalleryItem($objects, $params = null)
    {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        if (!isset($params[0])) return false;
        $gallery_item_id = $params[0];

        $gallery_item_entry = Q::create($this->_galleries_items_table)
            ->where(array(
                'id_object' => $object->id,
                'id' => $gallery_item_id
            ))->one()->exec();

        if (!$gallery_item_entry) return false;

        Q::create($this->_galleries_items_table)->delete(array(
            'id_object' => $object->id,
            'id' => $gallery_item_id
        ))->exec();

        if (isset($gallery_item_entry['link']) && !empty($gallery_item_entry['link'])) {
            $file_name = SL::getDirWeb() . $gallery_item_entry['link'];
        }
        if (!empty($this->_params['thumbnails'])) {
            $files_to_delete = array($file_name);
            $file_info = slLocator::getFileInfo($file_name);
//            vd($file_info, $file_name, is_file($file_name));
            foreach ($this->_params['thumbnails'] as $alias => $size) {
                if (isset($file_info['full_name'])) {
                    $files_to_delete[] = dirname($file_name) . '/' . $alias . '/' . $file_info['name'].'.jpg';
                }
            }
            $files_to_delete[] = dirname($file_name).'/thumbnails/'.$file_info['name'].'.jpg';
            $files_to_delete[] = dirname($file_name).'/converted/'.$file_info['name'].'.flv';
//            vd($files_to_delete);
            foreach ($files_to_delete as $file) {
                if (is_file($file)) unlink($file);
            }
            return true;
        } else {
            unlink($file_name);
            return true;
        }
    }

    public function deleteVideogallery($objects, $params = null)
    {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        if (!isset($params[0])) return false;
        $gallery_id = $params[0];

        $gallery_items = Q::create($this->_galleries_items_table)->where(array(
            'id_object' => $object->id,
            'id_gallery' => $gallery_id
        ))->exec();

        foreach ($gallery_items as $item) {
            unlink(SL::getDirWeb() . $item['link']);
        }

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

}
