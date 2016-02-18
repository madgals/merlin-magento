<?php

namespace Merlin;

class Group extends MultiFieldParameter {
    protected $field;
    protected $sort;
    protected $num;
    
    function __construct($field, $num=null, Sort $sort=null) {
        $this->field = $field;
        $this->num = $num;
        $this->sort = $sort;
    }

}
