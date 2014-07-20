<?php

/**
 * slModel Event Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 14.08.2013 01:12:36
 */
class Event extends BaseEvent {

	const CATEGORY_LOCAL  = 1;
	const CATEGORY_EUROPE = 2;

    static public function loadOneBySlug($slug){
        return self::loadOne(C::create()->where(array('slug'=>$slug)));
    }

	static public function getEventsToMainPage($count){
		$ids = Q::create('events e')
			->select('e.id')
			->leftJoin('events_mlt m', 'e.id =m.id')
			->where('m.is_active = 1')
			->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
			->useValue('id')
			->orderBy('show_on_main DESC, start_date DESC')
			->limit($count)
			->exec();

		$events = self::loadList(C::create()->where(array('events.id'=>$ids))->orderBy('show_on_main DESC, start_date DESC'));

		return $events;
	}

    static public function getList($current_page, $tag=false, $filter = 'all', $on_page = 7){
        $query = Q::create('events e')
                    ->select('e.id')
                    ->leftJoin('events_mlt m', 'e.id =m.id')
                    ->where('m.is_active = 1')
                    ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'');

	    if ('european' == $filter) {
		    $query->andWhere('e.id_category = ' .self::CATEGORY_EUROPE);
	    } elseif ('local' == $filter) {
		    $query->andWhere('e.id_category = ' .self::CATEGORY_LOCAL);
	    }

        if($tag){
            $query->andWhere('tags like \'%'.$tag.'%\'');
        }

        $query->useValue('id')
            ->orderBy('start_date DESC');

        $ids = array();
        try {
            $ids = slPaginator::getFromQuery($query, $current_page, $on_page, 'id');
        }catch(Exception $e) {
            if ($current_page>1){
                throw new slRouteNotFoundException('');
            }
        }

        $c = C::create()->where(array('events.id'=>$ids))->orderBy('start_date DESC');


        $items = self::loadList($c);


        return $items;
    }

    public function getCategories(){
        return EventCategory::loadList();
    }

    public function save($with_validation = true, $force_save = false) {
        $this->slug = slInflector::slugify($this->slug);
        return parent::save($with_validation, $force_save);
    }

    public function getMainImageLink(){
        return isset($this->main_image['sizes']['small']['link']) ? $this->main_image['sizes']['small']['link'] : 'images/default_news_image.png';
    }

    public function getShareText(){
        return  str_replace(array("\r","\n"),"", $this->short_description);
    }

    public function getAnotherEvent($limit=2){
        $ids = Q::create('events e')
            ->select('e.id')
            ->leftJoin('events_mlt m', 'e.id =m.id')
            ->where('e.id <>' . $this->id)
            ->where('m.is_active = 1')
            ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
            ->orderBy('RAND()')
            ->limit($limit)
            ->useValue('id')
            ->exec();

        return self::loadList(C::create()->where(array('events.id'=>$ids)));
    }

    public function getGallery(){
        $galleries = $this->getGalleriesFiles();
        return (count($galleries) > 0) ? end($galleries) : array();
    }


}
