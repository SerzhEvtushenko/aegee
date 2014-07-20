<?php
/**
 * @package SolveProject
 * @subpackage Image
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: Dec 13, 2009 2:42:31 PM
 */

/**
 * ImageEngine for work throw ImageMagick processor
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slIMagickImageEngine extends slImageEngine {

    private $_imagick_executable    = 'convert';

    public function __construct() {
        $this->_imagick_executable = SL::getProjectConfig('imagick_path', '') . $this->_imagick_executable;
    }

    public function crop($from, $to, $dimension, $gravity = 'center') {
        exec($this->_imagick_executable.' "'.$from.'" -resize '.$dimension.'^ -gravity '.$gravity.' -crop '.$dimension.'+0+0 +repage "'.$to.'"');
    }

    public function fitIn($from, $to, $dimensions, $gravity = 'center') {
        $size = explode('x', $dimensions);
        $command = $this->_imagick_executable.' "'.$from.'"';

        $size_opt   = '';
        if (isset($size[0]) && isset($size[1])) {
            $resize_opt = $dimensions;
            $size_opt = ' -size '.$dimensions;
        } elseif ($size[0]) {
            $resize_opt = $size[0]. 'x';
        } else {
            $resize_opt = 'x' . $size[1];
        }
        $command.= ' -resize '.$resize_opt.$size_opt.' -gravity center "'.$to.'"';
        $res = array();
        $code = null;
        exec($command, $res, $code);
    }

    public function fitOut($from, $to, $dimensions, $gravity = 'north') {
        $size = explode('x', $dimensions);
        $command = $this->_imagick_executable.' "'.$from.'"';
        $command.= ' -resize "x'.($size[1]*2).'" -resize "'.($size[0]*2).'x<" -resize 50% -gravity '.$gravity.' -crop '.$dimensions.'+0+0 +repage +set option:filter:sharp:0.5 "'.$to.'"';
        exec($command);
    }

    public function fitInFull($from, $to, $dimensions, $gravity = 'center') {
        $size = explode('x', $dimensions);
        $command = $this->_imagick_executable.' "'.$from.'"';
        $size_opt   = '';
        if ($size[0] && $size[1]) {
            $resize_opt = $dimensions;
            $size_opt = ' -size '.$dimensions;
        } elseif ($size[0]) {
            $resize_opt = $size[0]. 'x';
        } else {
            $resize_opt = 'x' . $size[1];
        }
        $command .= ' -resize '.$dimensions.' -size '.$dimensions.' xc:white +swap -gravity center -composite "'.$to.'"';
        $res = array();
        $code = null;
        exec($command, $res, $code);
    }

    public function viaSuperZoomCrop($from, $to, $zoom_dimensions, $offset_left, $offset_top, $crop_dimensions, $compensation, $zoom, $background = 'white') {
        $zoom_size = explode('x', $zoom_dimensions);
        $crop_size = explode('x', $crop_dimensions);

        $command = $this->_imagick_executable.' "'.$from.'"';
        $command .= ' -resize "'.$zoom_dimensions.'" ';

        $crop_offset_left = 0;
        $crop_offset_top = 0;
        $compose_offset_left = 0;
        $compose_offset_top = 0;

        if ($offset_left > 0) {
            $compose_offset_left = $offset_left;
        } else {
            $crop_offset_left = abs($offset_left);
        }

        if ($offset_top > 0) {
            $compose_offset_top = $offset_top;
        } else {
            $crop_offset_top = abs($offset_top);
        }

        $command .= ' -crop "'.$crop_dimensions.'"+'.$crop_offset_left.'+'.$crop_offset_top.' ';

        if ($compose_offset_left > 0 || $compose_offset_top > 0 || $zoom_size[0] < $crop_size[0] || $zoom_size[1] < $crop_size[1]) {
            $command .= ' -size "'.$crop_dimensions.'" xc:"'.$background.'" +swap -geometry "+'.$compose_offset_left.'+'.$compose_offset_top.'" -composite ';
        }

        $command .= ' '.$to;

        exec($command);
    }

    public function fitOutAlternative($from, $to, $dimensions, $gravity = 'north') {
        $size = explode('x', $dimensions);
        $command = $this->_imagick_executable.' "'.$from.'"';
        $command.= ' -resize "'.$dimensions.'^" -gravity '.$gravity.' +repage -crop "'.$dimensions.'+0+0" "'.$to.'"';
        SL::log($command,'ffo');
        exec($command);
    }

}

?>