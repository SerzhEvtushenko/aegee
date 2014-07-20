<?php

/**
 * slModel Project Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 22.08.2013 12:10:15
 */
class Project extends BaseProject {

    static public function getList($current_page, $on_page = 7){
	    $query = Q::create('projects n')
		    ->select('n.id')
		    ->leftJoin('projects_mlt m', 'm.id = n.id')
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

	    return self::loadList(C::create()->where(array('projects.id'=>$ids))->orderBy('_position ASC'));
    }

	static public function loadOneBySlug($slug){
		return self::loadOne(C::create()->where(array('slug'=>$slug)));
	}

	public function getGallery(){
		$galleries = $this->getGalleriesFiles();
		return (count($galleries) > 0) ? end($galleries) : array();
	}
}
