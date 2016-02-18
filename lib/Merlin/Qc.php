<?php

namespace Merlin;

class Qc extends MultiFieldParameter {
    protected $q;
    protected $filter;
    
    function __construct($q, Filter $filter=null) {
        $this->q = $q;
        $this->filter = $filter;
        
        $this->fieldJoiner = '//';
    }

}
