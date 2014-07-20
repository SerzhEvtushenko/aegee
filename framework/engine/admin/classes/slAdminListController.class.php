<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 27.05.12 23:57
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
 
class slAdminListController extends ModelAdminController {

    private $_filter_storage    = array();

    public function preAction() {
        parent::preAction();

        if (!isset($_SESSION['admin']['filters'][$this->_module['name']])) {
            $_SESSION['admin']['filters'][$this->_module['name']] = array();
        }

        $this->_filter_storage = &$_SESSION['admin']['filters'][$this->_module['name']];
        $this->view->module_title = $this->_module['title'];

    }

    public function actionDefault() {
        $this->view->setTemplate('list/default.tpl');
        $c = C::create();
        $this->performFilters($c);

        $per_page   = isset($this->_module['per_page']) ? $this->_module['per_page'] : 30;
        $page       = $this->route->getVar('page_number', 1);

        try {
            $ids = slPaginator::getFromQuery(Q::create($this->_model_structure['table'])->where($c),$page, $per_page,'id');
            $c->where(array($this->_model_structure['table'] . '.id'=>$ids));
        } catch (Exception $e) {
            vd($e->getMessage());
            $this->route->redirect(SL::getApplication()->getName().'/' . $this->route->get('module') . '/');
        }

        $objects = call_user_func(array($this->_model_name, 'loadList'), $c);

        $abilities = $this->_model_structure->get('abilities');
        if ($abilities) {
            foreach($abilities as $ability=>$info) {
                if (!in_array($ability, $this->_loadable_abilities)) continue;
                $objects->{'load'.slInflector::camelCase($ability)}();
            }
        }

        $this->view->pager      = slPaginator::getInfo();
        $this->view->link       = '/'.SL::getApplication()->getName().'/'.$this->route->get('module').'/';
        $this->view->objects    = $objects;

    }

    public function actionPluginControl() {
        $class = slInflector::camelCase($this->route->getVar('plugin_name')).'Admin';
        $method = $this->route->getVar('method');
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }

        $response = call_user_func(array($class, $method), $object);
        echo json_encode($response);
        die();
    }

    public function actionInfo() {
        $this->view->setTemplate('list/info.tpl');
        /**
         * @var slModel $object
         */
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }

        if ($data = $this->route->getVar('data')) {


            $this->processData($data);
            if (!$object) $object = new $this->_model_name;
            $object->mergeData($data);

            $is_adding = $object->isNew();

            if ($object->save()) {
                $process = ($is_adding) ? 'add' : 'edit';
                AdminController::adminLog($object, $process);

                $this->view->setMessage('Object has updated');
                if ($control = $this->route->getVar('control')) {
                    if (!empty($control['send_email'])) {
                        $subject = !empty($control['email_subject']) ? $control['email_subject'] : SL::getConfig('settings', 'email_subject');
                        slMailer::sendMail($object->email, $subject, nl2br($control['email_text']));
                        $this->view->setMessage('Email has sent to '.$object->email);
                    }
                }
                $this->afterSave($object);

                if ($this->route->getVar('close')) {
                    $this->redirectModuleHome();
                }
                $this->route->redirect('admin/'.$this->_module['name'].'/edit/'.$object->id.'/');
            } else {
                $this->view->errors = $object->getErrors();
            }
        }

        GalleryAdmin::processSave($object);

        if (class_exists('MailLog') && $object && $object->id) {
            $this->view->mail_logs = MailLog::loadList(array('admin_module'=>$this->_module['name'], 'id_object'=>$object->id));
        }

        $this->performObject($object);

        $this->view->data = $object;

        $this->view->info_action = (is_null($object) || $object->isNew()) ? 'add' : 'edit';

        if ($this->route->getVar('params') == 'fill_language') {
            $object->fillWithLanguage();
            $this->view->setMessage('Object filled with language: '.MLT::getActiveLanguage());
            $this->redirectModuleHome();
        }
    }

    public function actionDeleteGalleryItem() {
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }
        $gallery_item_id = $this->route->getVar('gallery_item_id');
        AdminController::adminLog($object, 'delete_gallery_item');
        $result = $object->deleteGalleryItem($gallery_item_id);
        if (!$this->route->isXHR()) {
            $this->route->redirectReferer();
            die();
        }
        echo json_encode(array('res'=>$result)); die();
    }

    public function actionDeleteVideogalleryItem() {
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }
        $gallery_item_id = $this->route->getVar('gallery_item_id');
        AdminController::adminLog($object, 'delete_video_gallery_item');
        $result = $object->deleteVideogalleryItem($gallery_item_id);
        if (!$this->route->isXHR()) {
            $this->route->redirectReferer();
            die();
        }
        echo json_encode(array('res'=>$result)); die();
    }

    public function actionDeleteImage() {
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }
        AdminController::adminLog($object, 'delete_image');
        $alias = $this->route->getGET('alias');
        $filename = $this->route->getGET('filename');
        $result = $object->removeFile($alias, $filename);
        if (!$this->route->isXHR()) {
            $this->route->redirectReferer();
            die();
        }
        echo json_encode(array('res'=>$result)); die();
    }

    protected function afterSave($object) {

    }

    public function actionDelete() {
        if ($id_objects = $this->route->getVar('ids')) {

            /**
             * @var slModelCollection $objects
             */
            $objects = call_user_func(array($this->_model_name, 'loadList'), array('id'=>$id_objects));
            AdminController::adminLog($objects, 'delete');
            $objects->delete();


            die();
        } else {
            die('no_id');
        }
    }

    protected function performFilters(C $c) {
        if (!isset($this->_module['sort'])) {
            if (isset($this->_module['fields']['_position'])) {
                $this->_module['sort'] = '_position';
            } elseif (isset($this->_module['fields']['title'])) {
                $this->_module['sort'] = 'title';
            } else {
                $this->_module['sort'] = 'id';
            }
        }
        $c->orderBy($this->_module['sort']);
        $this->view->sort = $this->_module['sort'];
        if (isset($this->_module['filter'])) {

            $filter = $this->route->getVar('filter', array());
            if (!empty($filter)) {

                foreach($filter as $key=>$value) {

                    if ((($value == "---") || ($value == "")) && isset($this->_filter_storage[$key])) {
                        unset($this->_filter_storage[$key]);
                    } else {
                        $this->_filter_storage[$key] = $value;
                    }
                }
                $this->route->redirectSelf();
            }

            foreach($this->_module['filter'] as $key=>$info) {
                if (!is_array($info)) {
                    $info = array('field' => $key);
                }

                if (isset($this->_filter_storage[$info['field']]) && !empty($this->_filter_storage[$info['field']])) {
                    $info['value'] = $this->_filter_storage[$info['field']];
                }

                if (!isset($info['title']) && isset($this->_fields[$key])) {
                    $info['title'] = $this->_fields[$key]['title'];
                }
                if (!isset($info['type'])) {
                    if (isset($this->_fields[$key])) {
                        $info['type'] = $this->_fields[$key]['type'];
                    } elseif (isset($info['field_values'])) {
                        $info['type'] = 'select';
                    }
                }

                if (isset($info['pre_select']) && (!array_key_exists('value', $info))) {
                    if ($info['pre_select'] == 'first') {
                        $pre_select_value = $this->_filters_values[$key][0];
                    } else {
                        $pre_select_value = $info['pre_select'];
                    }
                    if (!isset($this->_filter_storage[$info['field']])) {
                        $this->_filter_storage[$info['field']] = $pre_select_value;
                    }
                    $info['value'] = $pre_select_value;
                }

                if (($info['type'] == 'select') && (!isset($info['field_values']))) {
                    $info['field_values'] = $this->_fields[$key]['field_values'];
                    foreach($info['field_values'] as $fvk=>$fvv) {
                        if ($fvv['id'] == "0") {
                            $info['field_values'][$fvk]['id'] = "<1";
                        }
                    }
                }

                if (!array_key_exists('value', $info)) {
                    if ($info['type'] == "text") {
                        $info['value'] = '';
                    } else {
                        $info['value'] = '0';
                    }
                }

                $this->_module['filter'][$key] = $info;
            }

            if (!empty($this->_filter_storage)) {
                $this->preProcessFilterCriteria($this->_filter_storage, $c);
            }

        }

        $this->view->module_structure = $this->_module;
    }

    protected function preProcessFilterCriteria($filter, C $c) {
        foreach($filter as $key=>$value) {
            if ($value == $this->_default_value) {
                unset($filter[$key]);
            } elseif (is_array($value) && in_array($value[0], array(">", "<", "!"))) {
                $c->andWhere($key . $value);
            } else {
                if (isset($this->_module['filter'][$key]) && ($this->_module['filter'][$key]['type'] == "text")) {
                    if (!empty($this->_module['filter'][$key]['fields'])) {
                        $key_fields = explode('|', $this->_module['filter'][$key]['fields']);
                        $sub_c = new C();
                        foreach($key_fields as $key_field) {
                            $sub_c->orWhereLike($key_field, $value);
                        }
                        $c->andWhere($sub_c);
                    } elseif (!empty($value)) {
                        $c->andWhereLike($key, $value);
                    }
                } else {
                    $c->andWhere(array($key=>$value));
                }
            }
        }

        return $filter;
    }

    protected function performFields() {
        $fields = $this->_module['fields'];
        $fields_to_hide = array();
        if (isset($this->_module['actions'][$this->_action]['hide'])) {
            $fields_to_hide = $this->_module['actions'][$this->_action]['hide'];
        }
        $fields_to_show = isset($this->_module['actions'][$this->_action]['show']) ? $this->_module['actions'][$this->_action]['show'] : array();
        foreach($fields as $key=>$item) {
            $field_key = is_numeric($key) ? $item : $key;

            // skip fields that we don't use
            if ((!empty($fields_to_show) && (!in_array($field_key, $fields_to_show))) || in_array($field_key, $fields_to_hide)) continue;

            $this->_fields[$field_key] = self::performFieldStructure($fields, $key, $item);
        }
        $this->view->fields = $this->_fields;
    }

    protected function performObject($object) {
        if (!empty($this->_module['tabs'])) {
            foreach($this->_module['tabs'] as $tab_name=>$info) {
                if (($tab_name == 'gallery') && (is_object($object))) {
                    $object['galleries_files']  = $object->getGalleriesFiles();
                    $object['galleries']        = $object->getGalleries();
                }
                if (($tab_name == 'videogallery') && (is_object($object))) {
                    $object['videogalleries_files']  = $object->getVideogalleriesFiles();
                    $object['videogalleries']        = $object->getVideogalleries();
                }
            }
        }
    }

    protected function processData(&$data) {
        foreach($this->_fields as $field_name=>$field_info) {
            if (!isset($field_info['type'])) {
                $field_info['type'] = 'custom';
            }        
            if ($field_info['type'] == 'checkbox') {
                $data[$field_name] = isset($data[$field_name]) ? 1 : 0;
            }
        }
    }

    protected function performModuleStructure($structure) {
        if (!isset($structure['actions']['edit']['view'])) {
            $structure['actions']['edit']['view'] = array();
        }
        return $structure;
    }

    public function actionMoveUp(){
        /**
         * @var slModel $object
         */
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }

        if ($object) {
            $object->moveBack();
            AdminController::adminLog($object, 'move_up');
        }

        $this->route->redirect('admin/'.$this->_module['name'].'/');
    }

    public function actionMoveDown(){
        /**
         * @var slModel $object
         */
        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }

        if ($object) {
            $object->moveForward();
            AdminController::adminLog($object, 'move_down');
        }

        $this->route->redirect('admin/'.$this->_module['name'].'/');
    }

    public function actionSaveProgramMainImageDimensions() {
        /**
         * @var Program $object
         */
        $response = array('res'=>false,'message'=>'Unknown error');

        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }
        AdminController::adminLog($object, 'save_program_main_image_dimensions');

        $zoom_dimensions = $this->route->getPOST('dimensions');
        if (!preg_match('#^\d+x\d+$#',$zoom_dimensions)) {
            $response['res'] = false;
            $response['message'] = 'Wrong dimensions';
            echo json_encode($response);
            die();
        }

        $left = intval($this->route->getPOST('left'));
        $top  = intval($this->route->getPOST('top'));
        $zoom = $this->route->getPOST('zoom');

        if (!isset($object->main_image['link'])) {
            $response['res'] = false;
            $response['message'] = 'No image';
            echo json_encode($response);
            die();
        }

        $object->main_image_zoom_dimensions = $zoom_dimensions;
        $object->main_image_left = $left;
        $object->main_image_top  = $top;
        $object->main_image_zoom = $zoom;
        $object->save();

        if ($object->regenerateMainImageBigThumbnail() &&
            $object->regenerateMainImageSmallThumbnail()) {
            $response = array('res'=>true,'message'=>'Changes applied successfully');
            echo json_encode($response);
            die();
        } else {
            $response['res'] = false;
            $response['message'] = 'Generating image failed';
            echo json_encode($response);
            die();
        }
    }

    public function actionSaveGalleryPhotoDimensions() {
        $response = array('res'=>true,'message'=>'Changes applied successfully');

        $object = null;
        if ($id_object = $this->route->getVar('id')) {
            if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $id_object))) {
                throw new slRouteNotFoundException('Object with id ['.$id_object.'] is not found.');
            }
        }
        AdminController::adminLog($object, 'save_gallery_photo_dimensions');

        $zoom_dimensions = $this->route->getPOST('dimensions');
        if (!preg_match('#^\d+x\d+$#',$zoom_dimensions)) {
            $response['res'] = false;
            $response['message'] = 'Wrong dimensions';
            echo json_encode($response);
            die();
        }

        $gallery_item_id = $this->route->getPOST('id_item', 0);
        $table           = $this->route->getVar('table');
        $table .= '_galleries_items';
        $gallery_item = Q::create($table)
            ->where(array('id'=>$gallery_item_id))->one()->exec();

        if (!$gallery_item) {
            $response['res'] = false;
            $response['message'] = 'Gallery item missing in the database';
            echo json_encode($response);
            die();
        }

        $left = intval($this->route->getPOST('left'));
        $top  = intval($this->route->getPOST('top'));
        $zoom = $this->route->getPOST('zoom');

        $update_array = array(
            'crop_zoom_dimensions' => $zoom_dimensions,
            'crop_zoom' => $zoom,
            'crop_left' => $left,
            'crop_top'  => $top
        );
        Q::create('programs_galleries_items')->update($update_array)
            ->where(array('id'=>$gallery_item_id))->exec();

        $gallery_item = array_merge($gallery_item, $update_array);

        $file_path = SL::getDirWeb().$gallery_item['link'];
        $gallery_item_file_array = slLocator::getFileInfo($file_path);
        $gallery_item = array_merge($gallery_item, $gallery_item_file_array);

        Program::processGalleryItemFiles($gallery_item, $file_path, true);
        echo json_encode(array(
            'res'  => true,
            'left' => $left,
            'top'  => $top,
            'zoom' => $zoom,
            'message' => 'Changes applied successfully',
            'zoom_dimensions' => $zoom_dimensions,
            'thumbnail_src' => str_replace($gallery_item['full_name'], 'small/'.$gallery_item['full_name'], $gallery_item['link'])
        ));
        die();
    }

	public function actionExport() {
		$c = C::create();
		$this->performFilters($c);

		$objects = call_user_func(array($this->_model_name, 'loadList'), $c);
		require_once dirname(__FILE__).'/../../../../framework/libs/' . 'PHPExcel/PHPExcel.php';

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);

		$objPHPExcel->getActiveSheet()->getDefaultStyle()
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


		$ch = 'A';

		foreach($this->_module['fields'] as $key=>$value) {
			if ('rights' == $key || 'avatar' == $key) continue;
			$title = is_array($value) ? $value['title'] : $value;
			$objPHPExcel->getActiveSheet()->setCellValue($ch.'1', $title);
			$objPHPExcel->getActiveSheet()->getStyle($ch.'1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($ch)->setWidth(30);

			$ch++;
		}

		$i = 2;
		foreach($objects as $object) {
			$ch = 'A';
			foreach($this->_module['fields'] as $key=>$value) {
				if ('rights' == $key || 'avatar' == $key) continue;
				$val = isset($value['field_values']) && isset($value['field_values'][$object[$key]]) ? $value['field_values'][$object[$key]] : $object[$key];
				if (is_array($val) && isset($val['title'])) {
					$val = $val['title'];
				}



				$objPHPExcel->getActiveSheet()
					->getCell($ch . $i)
					->setValueExplicit($val, PHPExcel_Cell_DataType::TYPE_STRING);
				$ch++;
			}
			$i++;
		}

		$objPHPExcel->getActiveSheet()->setTitle(isset($this->_module['title']) ? $this->_module['title'] : $this->_model_name);

		$project_name = 'AEGEE';

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $project_name . '_' . ($this->_module['title']) . '_' . date('Y-m-d H:i:s') . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		/**/
		$objWriter->save('php://output');
		die();
	}


}
