<?php

class Blackbird_Merlinsearch_Model_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category {

    const RESET_VALUE = '-';

    protected $_appliedCategory = null;

    public function __construct() {
        parent::__construct();
        $this->_requestVar = 'cat';
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
        $this->_appliedCategory = $filter;

        //Mage::register('current_category_filter', $this->getCategory(), true);



        $this->getLayer()->getProductCollection()->setCategoryFilter($this->_appliedCategory);

        $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory, $filter)
        );


        return $this;
    }

    /**
     * Validate category for be using as filter
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return unknown
     */
    protected function _isValidCategory($category) {
        return $category->getId();
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName() {
        return Mage::helper('catalog')->__('Category');
    }

    /**
     * Get selected category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory() {
        if (!is_null($this->_categoryId)) {
            $category = Mage::getModel('catalog/category')
                    ->load($this->_categoryId);
            if ($category->getId()) {
                return $category;
            }
        }
        return $this->getLayer()->getCurrentCategory();
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData() {
        if($this->_appliedCategory != null) return array();
        return $this->getLayer()->getProductCollection()->getCategories();
    }

}
