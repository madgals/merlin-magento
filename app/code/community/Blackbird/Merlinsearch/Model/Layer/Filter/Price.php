<?php

class Blackbird_Merlinsearch_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Category {

    const RESET_VALUE = '-';

    protected $_appliedFilter = null;

    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'price';
    }

    public function getResetValue() {
        return self::RESET_VALUE;
    }

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        //Mage::log('Blackbird_Merlinsearch_Model_Layer_Filter_Category apply');

        $filter = $request->getParam($this->getRequestVar());
        //Mage::log('$filter:'.$filter);
        if (!$filter) {
            return $this;
        }
        if ($filter == self::RESET_VALUE) {
            return $this;
        }
        $this->_appliedFilter = $filter;

        //Mage::register('current_category_filter', $this->getCategory(), true);
        $minmax = explode('-', $filter);


        $this->getLayer()->getProductCollection()->setPriceFilterMin($minmax[0]);
        $this->getLayer()->getProductCollection()->setPriceFilterMax($minmax[1]);

        $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedFilter, $filter)
        );


        return $this;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName() {
        return Mage::helper('catalog')->__('Price');
    }

    protected function _getItemsData() {
        if($this->_appliedFilter != null) return array();
        $ranges = $this->getLayer()->getProductCollection()->getPriceHist();
        $store      = Mage::app()->getStore();
        foreach($ranges as $key=>$val){
            $ranges[$key]['label'] = $store->formatPrice($val['from']) . ' - ' . $store->formatPrice($val['to']);
        }
        return $ranges;
    }

}
