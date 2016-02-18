<?php
namespace Merlin;

class HistFacet extends Facet{
    
    function __construct($field, $start, $end, $gap, $key=null) {
        $type = 'hist';
        parent::__construct($field, $type, $key);
        $this->range = "[{$start}:{$end}:{$gap}]";
    }

}
