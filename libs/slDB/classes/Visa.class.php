<?php

/**
 * slModel Visa Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 14.08.2013 01:04:05
 */
class Visa extends BaseVisa {

    static public function getList(){
        return self::loadList(C::create()->orderBy('_position ASC'));
    }

    static public function getCurrentItem($slug, $visa_list){
        if ($slug) {
            $visa_list = $visa_list->getIndexedBy('slug');

            if (isset($visa_list[$slug])) {
                $current_visa_item = $visa_list[$slug];
            }else{
                throw new slRouteNotFoundException('');
            }
        }else{
            $current_visa_item = isset($visa_list[0]) ? $visa_list[0] : array();
        }

        return $current_visa_item;
    }
}
