<?php

class Blackbird_Merlinsearch_Block_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'merlinsearch/layer_filter_price';
    }
}
