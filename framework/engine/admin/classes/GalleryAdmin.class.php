<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 24.10.12 23:24
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class GalleryAdmin {

    static public function processSave($object) {
        if (($gallery_id = slRouter::getCurrentRoute()->getVar('gallery_id')) &&
            ($gallery_items = slRouter::getCurrentRoute()->getVar('gallery_items'))) {

            $object->updateGalleryItems($gallery_id, $gallery_items);
        }

        if ($id_gallery = slRouter::getCurrentRoute()->getVar('gallery_object_id')) {
            $object->save(true, true);
        }
    }

    static public function save($object) {
        $route = slRouter::getCurrentRoute();
        $data = $route->getVar('gallery');
        if (!($id_gallery = $route->getVar('id_gallery'))) {
            $object->addGallery($data);
        } else {
            $data['id_gallery'] = $id_gallery;
            $object->updateGallery($data);
        }
        return "ok";
    }

    static public function saveFiles($object) {
        $resp = array(
            'status'    => 'no'
        );
        $object->save(true, true);
        return $resp;
    }

    static public function saveVideoFiles($object) {
        $resp = array(
            'status'    => 'no'
        );
        $object->save(true, true);
        return $resp;
    }

    static public function loadInfo($object) {
        $route = slRouter::getCurrentRoute();
        $info = $object->getGalleries();
        $id_gallery = $route->getVar('id_gallery');
        $res = array(
            'status'    => 'no'
        );
        if (!empty($info)) {
            foreach($info as $gallery) {
                if ($gallery['id'] == $id_gallery) {
                    $res['fields'] = $gallery;
                    $res['status'] = 'ok';
                    break;
                }
            }
        }
        return $res;
    }

    static public function updateItemZoomDimensions($object) {
        $response = array('res' => false , 'message' => 'Error');

        $zoom_dimensions = slRouter::getCurrentRoute()->getPOST('dimensions');
        if (!preg_match('#^\d+x\d+$#',$zoom_dimensions)) {
            $response['message'] = 'Wrong dimensions';
            echo json_encode($response);
            die();
        }

        $gallery_item_id = slRouter::getCurrentRoute()->getPOST('id_item', 0);

        $left = intval(slRouter::getCurrentRoute()->getPOST('left'));
        $top  = intval(slRouter::getCurrentRoute()->getPOST('top'));
        $zoom = slRouter::getCurrentRoute()->getPOST('zoom');

        $data = array('crop_left'=>$left, 'crop_top'=>$top, 'crop_zoom'=>$zoom, 'crop_zoom_dimensions'=>$zoom_dimensions);
        if (($item = $object->updateGalleryItemZoom($gallery_item_id, $data)) !== false) {
            echo json_encode(array(
                'res' => true,
                'item' => $item,
                'message' => 'Changes applies successfully'
            ));
            die();
        }

        echo json_encode($response);
        die();
    }

}
