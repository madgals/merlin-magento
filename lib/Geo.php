<?php

namespace Merlin;

class Geo extends MultiFieldParameter {
    protected $field;
    protected $pt;
    protected $d;
    
    function __construct($field, $latitude, $longitude, $d) {
        $this->field = $field;
        $this->pt = "($latitude,$longitude)";
        $this->d = $d;
    }

}
