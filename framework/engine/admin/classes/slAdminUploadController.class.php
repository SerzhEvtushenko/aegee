<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mounter
 * Date: 14.08.12
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */
class slAdminUploadController extends slController{

    public function actionHandle() {
        $upload_handler = new UploadHandler(array(
            'script_url'    => slRouter::getBaseUrl() . 'admin/upload_handler',
            'upload_dir'    => 'works/',
            'upload_url'    => SL::getDirUpload() . 'works/',
            'image_versions'     => array()
        ));

        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $upload_handler->get();
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }
        die();
    }

}
