<?php

class Blackbird_Merlinsearch_Block_CatalogSearch_Layer extends Mage_CatalogSearch_Block_Layer
{
    /**
     * Get attribute filter block name
     *
     * @deprecated after 1.4.1.0
     *
     * @return string
     */
    protected function _getAttributeFilterBlockName()
    {
        return 'catalogsearch/layer_filter_attribute';
    }

    protected function _initBlocks()
    {
        parent::_initBlocks();
        $this->_categoryBlockName = 'merlinsearch/layer_filter_category';
        $this->_priceFilterBlockName = 'merlinsearch/layer_filter_price';
        $this->_attributeFilterBlockName = 'merlinsearch/layer_filter_attribute';
    }

    public function canShowBlock()
    {
        return true;
    }
}
