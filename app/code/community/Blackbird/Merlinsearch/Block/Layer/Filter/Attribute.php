<?php

class Blackbird_Merlinsearch_Block_Layer_Filter_Attribute extends Mage_CatalogSearch_Block_Layer_Filter_Attribute
{
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'merlinsearch/layer_filter_attribute';
    }
}
