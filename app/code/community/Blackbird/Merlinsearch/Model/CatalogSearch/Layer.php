<?php

class Blackbird_Merlinsearch_Model_CatalogSearch_Layer extends Mage_CatalogSearch_Model_Layer
{

    const XML_PATH_DISPLAY_LAYER_COUNT = 'catalog/search/use_layered_navigation_count';

    protected $_merlinCollection;
    protected $_filterableAtributes;

    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (!isset($this->_merlinCollection)) {
            $this->_merlinCollection = Mage::getResourceModel('merlinsearch/product_collection');
            $this->_merlinCollection->setQuery(Mage::helper('catalogsearch')->getQuery()->getQueryText());
            $this->_merlinCollection->load();
        }

        return $this->_merlinCollection;
    }


    public function getFilterableAttributes()
    {
        if (!isset($this->_filterableAtributes)) {
            $filterableAttributes = array();
            $facetAttributes = Mage::helper('merlinsearch/mapping')->getEnumFacets();
            $facetAttributes[] = 'price';
            $collection = Mage::getResourceModel('catalog/product_attribute_collection');
            $collection->setItemObjectClass('catalog/resource_eav_attribute')
                    ->addStoreLabel(Mage::app()->getStore()->getId())
                    ->setOrder('position', 'ASC');
            $collection->addFieldToFilter('attribute_code', array('in' => $facetAttributes));
            $this->_filterableAtributes = $collection;
        }

        return $this->_filterableAtributes;
    }

}
