<?php

/**
 * slModel AEGEEToday Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 26.11.2014 20:49:42
 */
class AEGEEToday extends BaseAEGEEToday {
    static public function loadOneBySlug($slug){
        return self::loadOne(C::create()->where(array('slug'=>$slug)));
    }

    static public function getList($current_page, $on_page = 7){
        $query = Q::create('aegee_todays n')
                  ->select('n.id')
                  ->leftJoin('aegee_todays_mlt m', 'm.id = n.id')
                  ->where('m.is_active = 1')
                  ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
                  ->useValue('id')
                  ->orderBy('id DESC');

        $ids = array();
        try {
            $ids = slPaginator::getFromQuery($query, $current_page, $on_page, 'id');
        }catch(Exception $e) {
            if ($current_page>1){
                throw new slRouteNotFoundException('');
            }
        }
        $c = C::create()->where(array('aegee_todays.id'=>$ids))->orderBy('id DESC');

        $items = self::loadList($c);

        return $items;
    }

    public function save($with_validation = true, $force_save = false) {
        $this->slug = slInflector::slugify($this->slug);
        $this->tags = str_replace(' ', '_', $this->tags);
        return parent::save($with_validation, $force_save);
    }

    public function getAnotherObjects(){
        $ids = Q::create('aegee_todays e')
                ->select('e.id')
                ->leftJoin('aegee_todays_mlt m', 'e.id =m.id')
                ->where('e.id <>' . $this->id)
                ->andWhere('m.is_active = 1')
                ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
                ->limit(2)
                ->useValue('id')
                ->exec();

        return self::loadList(C::create()->where(array('aegee_todays.id'=>$ids)));
    }

    public function getGallery(){
        $galleries = $this->getGalleriesFiles();
        return (count($galleries) > 0) ? end($galleries) : array();
    }

    public function getMainImageLink(){
        return isset($this->main_image['sizes']['big']['link']) ? $this->main_image['sizes']['big']['link'] : 'images/default_news_image.png';
    }
}
