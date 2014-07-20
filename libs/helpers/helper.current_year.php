<?php

class ftlHelperCurrentYear extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
      $rez = date('Y');
      return $rez;
}
}