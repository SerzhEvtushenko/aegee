<?php

Class ftlHelperEventTags extends ftlBlock {

    public function process($params) {
        $res = '';

        if (isset($params[0])){
            $tags = explode('#', $params[0]);
            foreach($tags as $tag){
                if (!empty($tag)){
	                $tag = trim(str_replace(array(',',' '),'',$tag));
                    $res .= '<a href="'.MLT::getActiveLanguage().'/events/tag/'.$tag.'/" title="'.$tag.'">#'.$tag.'</a>';
                }
            }
        }
        return $res;
    }

}