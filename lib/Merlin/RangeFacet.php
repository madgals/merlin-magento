<?php
namespace Merlin;

class RangeFacet extends Facet{
    
    function __construct($field, $key=null) {
        $type = 'range';
        parent::__construct($field, $type, $key);
    }

}
