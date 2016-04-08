<?php

class Blackbird_Merlinsearch_Model_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{

    public function getResetValue()
    {
        return null;
    }

    /**
     * Apply category filter to layer
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

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->_appliedCategory != null) {
            return array();
        }
        return $this->getLayer()->getProductCollection()->getCategories();
    }

}
