<?php

/**
 * slModel News Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 22.08.2013 12:07:46
 */
class News extends BaseNews {

	const CATEGORY_AEGEE    = 0;
	const CATEGORY_PARTNERS = 1;


    static public function loadOneBySlug($slug){
        return self::loadOne(C::create()->where(array('slug'=>$slug)));
    }

    static public function getList($current_page, $tag=false, $category=self::CATEGORY_AEGEE, $on_page = 7){
        $query = Q::create('news n')
            ->select('n.id')
            ->leftJoin('news_mlt m', 'm.id = n.id')
            ->where('m.is_active = 1')
            ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'');

	    if ('all' == $category) {

	    }else if ('partners' == $category) {
		    $query->andWhere('id_category = ' . self::CATEGORY_PARTNERS);
	    } else {
		    $query->andWhere('id_category = ' . self::CATEGORY_AEGEE);
	    }

        if($tag){
            $query->andWhere('tags like \'%'.$tag.'%\'');
        }


        $query->useValue('id')
            ->orderBy('id DESC');

        $ids = array();
        try {
            $ids = slPaginator::getFromQuery($query, $current_page, $on_page, 'id');
        }catch(Exception $e) {
            if ($current_page>1){
                throw new slRouteNotFoundException('');
            }
        }
        $c = C::create()->where(array('news.id'=>$ids))->orderBy('id DESC');

        $items = self::loadList($c);

        return $items;
    }

    public function save($with_validation = true, $force_save = false) {
        $this->slug = slInflector::slugify($this->slug);
	    $this->tags = str_replace(' ', '_', $this->tags);
        return parent::save($with_validation, $force_save);
    }

    public function getAnotherNews(){
	    $ids = Q::create('news e')
            ->select('e.id')
            ->leftJoin('news_mlt m', 'e.id =m.id')
            ->where('e.id <>' . $this->id)
            ->andWhere('m.is_active = 1')
            ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
            ->limit(2)
            ->useValue('id')
            ->exec();

        return self::loadList(C::create()->where(array('news.id'=>$ids)));
    }

    public function getGallery(){
        $galleries = $this->getGalleriesFiles();
        return (count($galleries) > 0) ? end($galleries) : array();
    }

	public function getMainImageLink(){
		return isset($this->main_image['sizes']['big']['link']) ? $this->main_image['sizes']['big']['link'] : 'images/default_news_image.png';
	}
}
