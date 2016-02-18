<?php

namespace Merlin;

class Typeahead extends MerlinGetFunction {
    protected $q;
    function __construct($q) {
        $this->q = $q;
    }

}
