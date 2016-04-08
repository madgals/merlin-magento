<?php

class Blackbird_Merlinsearch_Model_Merlin_Result extends Varien_Object
{

    protected $_result;

    public function __construct($result)
    {
        $this->_result = $result;
        parent::__construct();
    }

    public function getResult()
    {
        return $this->_result;
    }

    public function getResults()
    {
        if (isset($this->getResult()->results)) {
            return $this->getResult()->results;
        }
        return null;
    }

    public function getMsg()
    {
        if (isset($this->getResult()->msg)) {
            return $this->getResult()->msg;
        }
        return null;
    }

    public function getNumFound()
    {
        if (isset($this->getResult()->results->numfound)) {
            return $this->getResult()->results->numfound;
        }
        return 0;
    }

    public function getHits()
    {
        if (isset($this->getResult()->results->hits)) {
            return $this->getResult()->results->hits;
        }
        return array();
    }

    public function getEnumFacets($attributeCode)
    {
        if (isset($this->getResult()->results->facets->enums->$attributeCode->enums)) {
            return $this->getResult()->results->facets->enums->$attributeCode->enums;
        }
        return array();
    }
    
    public function getHistFacets($attributeCode)
    {
        if (isset($this->getResult()->results->facets->histograms->$attributeCode->histograms)) {
            return $this->getResult()->results->facets->histograms->$attributeCode->histograms;
        }
        return array();
    }

}
