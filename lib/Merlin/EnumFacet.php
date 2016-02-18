<?php

namespace Merlin;

class EnumFacet extends Facet{
    
    function __construct($field, $num = null, $key=null) {
        $type = 'enum';
        parent::__construct($field, $type, $key);
        $this->num = $num;
    }

}
