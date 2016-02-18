<?php

class Blackbird_Merlinsearch_Block_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    public function __construct()
    {
        //Mage::log('Mage_Catalog_Block_Layer_Filter_Category __construct');
        parent::__construct();
        $this->_filterModelName = 'merlinsearch/layer_filter_category';
    }
}
