<?php

class Blackbird_Merlinsearch_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{

    protected function _getItemsData()
    {
        if ($this->_appliedFilter != null) {
            return array();
        }
        $ranges = $this->getLayer()->getProductCollection()->getPriceHist();
        $store = Mage::app()->getStore();

        $priceRanges = array();
        foreach ($ranges as $val) {
            if ($val['count']) {
                $priceRanges[] = array(
                    'label' => $store->formatPrice($val['from']) . ' - ' . $store->formatPrice($val['to']),
                    'value' => $val['value'],
                    'count' => $val['count'],
                );
            }
        }
        return $priceRanges;
    }

    public function getResetValue()
    {
        return null;
    }
    
    /**
     * Apply price filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        
        if($label = $this->getLayer()->getProductCollection()->getFilterLabel($this->getRequestVar(), $filter)) {
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($label, $filter)
            );
        }

        return $this;
    }

}
