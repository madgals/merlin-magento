<?php

namespace Merlin;

abstract class Facet extends MultiFieldParameter {
    protected $field;
    protected $type;
    protected $key;
    protected $ex;
    
    protected $num;
    protected $range;
            
    function __construct($field, $type, $key=null) {
        $this->field = $field;
        $this->type = $type;
        $this->key = $key;
    }
    
    function addEx($e){
        if(!isset($this->ex)){
            $this->ex = array();
        }
        $this->ex[] = $e;
        return $this;
    }
}
