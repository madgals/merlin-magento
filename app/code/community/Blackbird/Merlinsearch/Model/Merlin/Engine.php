<?php

class Blackbird_Merlinsearch_Model_Merlin_Engine extends Varien_Object
{

    protected $_engine;

    public function getEngine()
    {
        if (!$this->_engine) {
            $company = trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company'));
            $environment = trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment'));
            $instance = trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance'));
            $this->_engine = new \Merlin\MerlinSearch($company, $environment, $instance);
        }
        return $this->_engine;
    }

    public function search($search)
    {
        $merlinResult = $this->getEngine()->search($search->getSearch());
        $result = Mage::getModel('merlinsearch/merlin_result', $merlinResult);
        return $result;
    }

}
