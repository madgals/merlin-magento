<?php

namespace Merlin;

class Sort {
    private $field;
    private $order;
    
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';
    
    function __construct($field, $order) {
        $this->field = $field;
        $this->order = $order;
    }
    
    public function __toString()
    {
        return $this->field.':'.$this->order;
    }
}
