<?php

class Blackbird_Merlinsearch_Model_Facet extends Varien_Object
{

    protected $_facetableHistAttributes = array();
    protected $_facetableEnumAttributes = array();
    protected $_enumFacets = array();
    protected $_histFacets = array();
    protected $_attributeLabels = array();
    protected $_categoryNames = array();
    protected $_enumCounts = array();

    /**
     *
     * @var Blackbird_Merlinsearch_Model_Merlin_Result 
     */
    protected $_result;

    public function __construct()
    {
        $mapping = Mage::helper('merlinsearch/mapping');
        $this->_facetableHistAttributes = array("price" => array(0, 1000, 100));
        $this->_facetableEnumAttributes = $mapping->getEnumFacets();
        $this->addFacetableAttribute('category');
        parent::__construct();
    }

    public function getFacetableHistAttributes()
    {
        return $this->_facetableHistAttributes;
    }

    public function getFacetableEnumAttributes()
    {
        return $this->_facetableEnumAttributes;
    }

    public function getEnumFacets()
    {
        return $this->_enumFacets;
    }

    public function getHistFacets()
    {
        return $this->_histFacets;
    }

    public function getAttributeFacet($attributeCode)
    {
        return isset($this->_enumFacets[$attributeCode]) ? $this->_enumFacets[$attributeCode] : array();
    }

    public function getAttributeHist($attributeCode)
    {
        return isset($this->_histFacets[$attributeCode]) ? $this->_histFacets[$attributeCode] : array();
    }

    public function initEnumFacets($result)
    {
        $this->_result = $result;
        foreach ($this->_facetableEnumAttributes as $attributeCode) {
            $this->_setEnumFacet($attributeCode);
        }
    }

    protected function _setEnumFacet($attributeCode)
    {
        foreach ($this->_result->getEnumFacets($attributeCode) as $enum) {
            $this->_addEnumFacet($attributeCode, $enum);
        }
    }

    protected function _addEnumFacet($attributeCode, $enumItem)
    {
        $this->_enumFacets[$attributeCode][] = array(
            'label' => $this->getAttributeLabel($attributeCode, $enumItem->term),
            'value' => $enumItem->term,
            'count' => $this->_getEnumCount($attributeCode, $enumItem->term),
        );
    }

    public function initHistFacets($result)
    {
        $this->_result = $result;
        foreach ($this->_facetableHistAttributes as $attributeCode => $histParams) {
            $this->_setHistFacet($attributeCode);
        }
    }

    protected function _setHistFacet($attributeCode)
    {
        foreach ($this->_result->getHistFacets($attributeCode) as $hist) {
            $this->_addHistFacet($attributeCode, $hist);
        }
    }

    protected function _addHistFacet($attributeCode, $histItem)
    {
        $this->_histFacets[$attributeCode][] = array(
            'from' => $histItem->from,
            'to' => $histItem->to,
            'value' => $histItem->from . '-' . $histItem->to,
            'count' => $histItem->count,
        );
    }

    protected function addFacetableAttribute($_facetableAttribute)
    {
        if (!in_array($_facetableAttribute, $this->_facetableEnumAttributes)) {
            $this->_facetableEnumAttributes[] = $_facetableAttribute;
        }
    }

    public function getAttributeLabel($attributeCode, $value)
    {
        if($attributeCode == 'size') {
            return strtoupper($value);
        }
        return ucfirst($value);
        
        //Code bellow used for load attribute labels by id.
        if (!$this->_attributeLabels) {
            foreach ($this->_facetableEnumAttributes as $code) {
                $attribute = Mage::getResourceModel('catalog/eav_attribute')->
                        loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);
                $labels = $attribute->getFrontend()->getSelectOptions();
                foreach ($labels as $label) {
                    if (isset($label['value']) && $label['value']) {
                        $this->_attributeLabels[$code][$label['value']] = $label['label'];
                    }
                }
            }
        }
        if (isset($this->_attributeLabels[$attributeCode][$value])) {
            return $this->_attributeLabels[$attributeCode][$value];
        }

        if ($attributeCode == 'category') {
            if (!$this->_categoryNames) {
                $collection = Mage::getResourceModel('catalog/category_collection');
                $collection->addAttributeToSelect('name', true);
                foreach ($collection as $category) {
                    $this->_categoryNames[$category->getId()] = $category->getName();
                }
            }
            if (isset($this->_categoryNames[$value])) {
                return $this->_categoryNames[$value];
            }
        }
        return $value;
    }

    protected function _getEnumCount($attributeCode, $value)
    {
        if (!$this->_enumCounts) {
            $this->_setEnumCounts();
        }
        if (isset($this->_enumCounts[$attributeCode][$value])) {
            return $this->_enumCounts[$attributeCode][$value];
        }
        return 0;
    }

    protected function _setEnumCounts()
    {
        $enumCounts = array();
        foreach ($this->_result->getHits() as $productInfo) {
            foreach ($this->_facetableEnumAttributes as $attribute) {
                if (isset($productInfo->parent_id) && isset($productInfo->$attribute)) {
                    $values = $productInfo->$attribute;
                    if (!is_array($values)) {
                        $values = array($values);
                    }
                    foreach ($values as $value) {
                        $enumCounts[$attribute][$value][$productInfo->parent_id] = 1;
                    }
                }
            }
        }
        foreach ($enumCounts as $attributeCode => $values) {
            foreach ($values as $value => $counts) {
                $value = strtolower($value);
                $this->_enumCounts[$attributeCode][$value] = count($counts);
            }
        }
    }

}
