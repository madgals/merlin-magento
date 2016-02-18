<?php

class Blackbird_Merlinsearch_Model_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Abstract {

    const RESET_VALUE = '-';

    protected $_appliedFilter = null;

    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'attribute';
    }

    public function getResetValue() {
        return self::RESET_VALUE;
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  string|bool
     */
//    protected function _getOptionText($optionId)
//    {
//        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
//    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        $filter = $request->getParam($this->getRequestVar());
        //Mage::log('$filter:'.$filter);
        if (!$filter) {
            return $this;
        }
        if ($filter == self::RESET_VALUE) {
            return $this;
        }
        

        //Mage::register('current_category_filter', $this->getCategory(), true);
        $attName = $this->getAttributeModel()->getAttributeCode();
        
        $this->_appliedFilter = $filter;

        $this->getLayer()->getProductCollection()->addAttributeFilter($attName, $filter);

        $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedFilter, $filter)
        );


        return $this;
    }

    /**
     * Check whether specified attribute can be used in LN
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
//    protected function _getIsFilterableAttribute($attribute)
//    {
//        return $attribute->getIsFilterable();
//    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData() {
        if($this->_appliedFilter != null){
            return array();
        }
        return $this->getLayer()->getProductCollection()->getAttributeFacet(
                $this->getAttributeModel()->getAttributeCode()
                );
    }

}
