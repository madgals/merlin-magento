<?php

class Blackbird_Merlinsearch_Model_CatalogSearch_Layer extends Mage_CatalogSearch_Model_Layer 
{

    const XML_PATH_DISPLAY_LAYER_COUNT = 'catalog/search/use_layered_navigation_count';

    protected $_merlinProducts;
    protected $_filterableAtributes;

    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        //Mage::log('Blackbird_Merlinsearch_Model_CatalogSearch_Layer getProductCollection() '.spl_object_hash($this));
        //Mage::log('!isset($this->_merlinProducts)'. !isset($this->_merlinProducts));
        if (!isset($this->_merlinProducts)) {
            //Mage::log('Blackbird_Merlinsearch_Model_CatalogSearch_Layer !isset($this->_merlinProducts');

            $this->_merlinProducts = new Blackbird_Merlinsearch_Model_Resource_Product_Collection();
            //Mage::log('!isset($this->_merlinProducts)'. !isset($this->_merlinProducts));
            $this->_merlinProducts->setQuery(Mage::helper('catalogsearch')->getQuery()->getQueryText());
            //$this->_merlinProducts->load();

            foreach ($this->getFilterableAttributes() as $attribute) {
                //Mage::log('addFacetableAttribute '. $attribute->getAttributeCode());
                $this->_merlinProducts->addFacetableAttribute($attribute->getAttributeCode());
            }
        }

        return $this->_merlinProducts;

//        $this->getMerlinCollection(Mage::helper('catalogsearch')->getQuery()->getQueryText());
//
//        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
//            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
//        } else {
//            $collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
//            $this->prepareProductCollection($collection);
//            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
//        }
//        Mage::log('getProductCollection');
//        foreach ($collection as $prod) {
//            Mage::log(print_r($prod->getData(), true));
//        }
//        return $collection;
    }

    /**
     * Prepare product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
//    public function prepareProductCollection($collection)
//    {
//        $collection
//                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
//                ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
//                ->setStore(Mage::app()->getStore())
//                ->addMinimalPrice()
//                ->addFinalPrice()
//                ->addTaxPercents()
//                ->addStoreFilter()
//                ->addUrlRewrite();
//
//        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
//        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
//
//        return $this;
//    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'Q_' . Mage::helper('catalogsearch')->getQuery()->getId() . '_' . parent::getStateKey();
        }
        return $this->_stateKey;
    }

    /**
     * Get default tags for current layer state
     *
     * @param   array $additionalTags
     * @return  array
     */
    public function getStateTags(array $additionalTags = array())
    {
        return $additionalTags;
    }

    /**
     * Prepare attribute for use in layered navigation
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @return  Mage_Eav_Model_Entity_Attribute
     */
    protected function _prepareAttribute($attribute)
    {
        return $attribute;
    }

    public function getFilterableAttributes()
    {
        //Mage::log('getFilterableAttributes');
        if(!isset($this->_filterableAtributes)){
            $collection = Mage::getResourceModel('catalog/product_attribute_collection');
            $collection ->setItemObjectClass('catalog/resource_eav_attribute')
                //->setAttributeSetFilter($setIds)
                ->addStoreLabel(Mage::app()->getStore()->getId())
                ->setOrder('position', 'ASC');
            $collection = $this->_prepareAttributeCollection($collection);
            $collection->load();
            $this->_filterableAtributes = $collection;
            //Mage::log($collection->getSize());
        }

        return $this->_filterableAtributes;
    }
}
