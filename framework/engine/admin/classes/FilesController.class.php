<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 05.06.12 12:55
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class FilesController extends slController {

    public function actionUpload() {
        $path = SL::getDirUpload() . 'redactor/';
        slLocator::makeWritable($path);

        $_FILES['file']['type'] = strtolower($_FILES['file']['type']);

        if ($_FILES['file']['type'] == 'image/png'
            || $_FILES['file']['type'] == 'image/jpg'
            || $_FILES['file']['type'] == 'image/gif'
            || $_FILES['file']['type'] == 'image/jpeg'
            || $_FILES['file']['type'] == 'image/pjpeg')
        {
            // setting file's mysterious name
            $name = substr($_FILES['file']['name'], 0, strrpos($_FILES['file']['name'], '.'));
            $ext = substr($_FILES['file']['name'], strlen($name)+1);

            while (is_file($path.$name.'.'.$ext)) {
                $name .= '_1';
            }

            $file = $path . $name . '.' .$ext;

            // copying
            copy($_FILES['file']['tmp_name'], $file);

            // displaying file
            echo '<img src="/upload/redactor/'.$name . '.' .$ext.'" />';
	        die('');
        }
    }

    public function actionList() {
        $files = slLocator::getInstance()->in($path = SL::getDirUpload() . 'redactor/')->find('{,.}*', slLocator::TYPE_FILE, slLocator::HYDRATE_NAMES);
        foreach($files as $key=>$value) {
            $files[$key] = array(
                'thumb' =>  '/upload/redactor/' . $value,
                'image' =>  '/upload/redactor/' . $value,
            );
        }
        echo json_encode($files);
        die();
    }


}
