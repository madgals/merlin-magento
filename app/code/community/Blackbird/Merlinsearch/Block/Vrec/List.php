<?php

class Blackbird_Merlinsearch_Block_Vrec_List extends Mage_Catalog_Block_Product_List
{
    protected $_merlinProducts;

    public function getLoadedProductCollection()
    {
        $count = $this->getColumnCount();
        //Mage::log($count);

        if (!isset($this->_merlinProducts)) {
            $this->_merlinProducts = new Blackbird_Merlinsearch_Model_Resource_Product_Collection();
            $this->_merlinProducts->setVrec($this->getProduct()->getId(), $count);
            $this->_merlinProducts->load();
        }
        return $this->_merlinProducts;
    }

    public function getToolbarHtml()
    {
        return '';
    }

    protected function _beforeToHtml()
    {
        return $this;
    }

    public function getMode()
    {
        return 'grid';
    }
}
