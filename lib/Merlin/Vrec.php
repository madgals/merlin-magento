<?php

namespace Merlin;

class Vrec extends MerlinGetFunction {

    protected $id;
    protected $fields;
    protected $num;
    protected $filter;

    function __construct($id, $fields = null, $num = null, Filter $filter = null) {
        $this->id = $id;
        $this->fields = $fields;
        $this->num = $num;
        $this->filter = $filter;
    }

}
