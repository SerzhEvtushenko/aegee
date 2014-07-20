<?php

/**
 * slModel Feedback Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 12.08.2013 00:27:26
 */
class Feedback extends BaseFeedback {

    static public function saveData($data){
        $result['status'] = false;
        foreach($data as $key=>$item){
            $data[$key] = trim(strip_tags($item));
        }

        $feedback = new Feedback();
        $feedback->mergeData($data);

        if ($feedback->save()) {
            $result['status'] = true;
        }else{
            $result['errors'] = $feedback->getErrors();
        }

        return $result;
    }
}
