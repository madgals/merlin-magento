<?php

namespace Merlin;

class MultiSearch extends MerlinGetFunction {

    protected $qc;
    protected $start;
    protected $num;
    protected $sort;

    function __construct() {
        $this->qc = array();
    }

    function addQc(Qc $s) {
        $this->qc[] = $s;
        return $this;
    }

    function setStart($start) {
        $this->start = $start;
    }

    function setNum($num) {
        $this->num = $num;
    }

    function addSort(Sort $s) {
        if (!isset($this->sort)) {
            $this->sort = array();
        }
        $this->sort[] = $s;
        return $this;
    }


}
