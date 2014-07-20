<?php

/**
 * slModel Faq Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 12.08.2013 00:22:38
 */
class Faq extends BaseFaq {
    public function save($with_validation = true, $force_save = false) {
        $this->slug = slInflector::slugify($this->slug);
        return parent::save($with_validation, $force_save);
    }
}
