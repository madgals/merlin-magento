<?php

namespace Merlin;

class Search extends MerlinGetFunction {

    protected $q;
    protected $filter;
    protected $facet;
    protected $start;
    protected $num;
    protected $sort;
    protected $fields;
    protected $group;
    protected $geo;
    protected $correct;

    protected $tqid;
    protected $oqid;

    function __construct($q) {
        $this->q = $q;
    }

    function addField($f) {
        if (!isset($this->fields)) {
            $this->fields = array();
        }
        $this->fields[] = $f;
        return $this;
    }

    function addFilter(Filter $f) {
        if (!isset($this->filter)) {
            $this->filter = array();
        }
        $this->filter[] = $f;
        return $this;
    }

    function addFacet(Facet $f) {
        if (!isset($this->facet)) {
            $this->facet = array();
        }
        $this->facet[] = $f;
        return $this;
    }

    function addSort(Sort $s) {
        if (!isset($this->sort)) {
            $this->sort = array();
        }
        $this->sort[] = $s;
        return $this;
    }

    function setStart($start) {
        $this->start = $start;
        return $this;
    }

    function setNum($num) {
        $this->num = $num;
        return $this;
    }

    function setCorrect($correct) {
        Validation::checkBoolean($correct, 'correct');
        $this->correct = $correct;
        return $this;
    }

    function setGroup(Group $group) {
        $this->group = $group;
        return $this;
    }

    function setGeo(Geo $geo) {
        $this->geo = $geo;
        return $this;
    }
    
    function setTqid($tqid) {
        $this->tqid = $tqid;
        return $this;
    }



}
