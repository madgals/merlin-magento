<?php

require_once(Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Merlin' . DIRECTORY_SEPARATOR . 'Merlin.php');

/*
 *  Collection: Processes queries and loads product collections
 *
 *  Uses loadQuery Function to retrieve results from Merlin Search Engine
 *
 */

class Blackbird_Merlinsearch_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{

    protected $_query;
    protected $_vrecId;
    protected $_vrecNum;
    protected $_attributeFiltersMax;
    protected $_attributeFiltersMin;
//    protected $_facetableHistAttributes;
//    protected $_facetableEnumAttributes;
//    protected $_enumFacets;
//    protected $_histFacets;
//    protected $_items;
    protected $_totalCount;
    protected $_isLoaded = false;
    protected $_orderBy;
    protected $_orderDir;

    /**
     *
     * @var Blackbird_Merlinsearch_Model_Merlin_Engine 
     */
    protected $_engine;

    /**
     *
     * @var Blackbird_Merlinsearch_Model_Merlin_Search 
     */
    protected $_search;

    /**
     *
     * @var Blackbird_Merlinsearch_Model_Merlin_Result 
     */
    protected $_result;
    //protected $_enumCounts;
    //protected $_attributeLabels;
    //protected $_categoryNames;

    /**
     *
     * @var Blackbird_Merlinsearch_Model_Facet
     */
    protected $_facet;

    function __construct($resource = null)
    {
        parent::__construct($resource);
        $mapping = Mage::helper('merlinsearch/mapping');

        $this->_attributeEnumFilters = array();
        $this->_attributeFiltersMin = array();
        $this->_attributeFiltersMax = array();
//        $this->_enumFacets = array();
//        $this->_histFacets = array();
//        //$this->_items = array();

        $this->_curPage = Mage::app()->getRequest()->getParam('p');
        $this->_orderBy = Mage::app()->getRequest()->getParam('order');
        $this->_orderDir = Mage::app()->getRequest()->getParam('dir');

        $this->_facet = Mage::getModel('merlinsearch/facet');
    }

    public function getCurPage($displacement = 0)
    {
        return $this->_curPage + $displacement;
    }

    public function load()
    {
        if ($this->_isLoaded) {
            return $this;
        }
        $this->_isLoaded = true;
        if (isset($this->_vrecId)) {
            $this->loadVrec();
        }
        else {
            $this->loadFromQuery();
            $this->addAttributeToSelect('*');
        }
        return parent::load();
    }

    public function loadVrec()
    {
        $engine = $this->getMerlinEngine();
        $v = new \Merlin\Vrec($this->_vrecId, null, $this->_vrecNum);
        $r = $engine->vrec($v);

        if (!isset($r->results)) {
            throw new Exception($r->msg);
        }

        $this->_totalCount = $r->results->numfound;
        foreach ($r->results->hits as $prod) {
            $bprod = $this->translate($prod);
            $this->_items[$bprod->getEntityId()] = $bprod;
        }
    }

    //Based on three character php header append
    private function getMagentoParentId($prod)
    {
        $parent_id = $prod->parent_id;
        if (substr($parent_id, 0, 3) === "pid") {
            $id = substr($parent_id, 3);
            return $id;
        }
        return $prod->id;
    }

    protected function _addPagination()
    {
        $page = $this->getCurPage();
        $limit = $this->getPageSize();
        $this->_search->addPagination($page, $limit);
    }

    protected function _addHistAttributes()
    {
        foreach ($this->_facet->getFacetableHistAttributes() as $att => $val) {
            if (isset($this->_attributeFiltersMax[$att])) {
                if (!isset($this->_attributeFiltersMin[$att]) || !$this->_attributeFiltersMin[$att]) {
                    $this->_attributeFiltersMin[$att] = 0;
                }
                $this->_search->addFilter($att, '>', $this->_attributeFiltersMin[$att]);
                $this->_search->addFilter($att, '<', $this->_attributeFiltersMax[$att]);
            }
            else {
                $this->_search->addHistFacet($att, $val[0], $val[1], $val[2]);
            }
        }
    }

    protected function _addEnumAttributes()
    {
        foreach ($this->_facet->getFacetableEnumAttributes() as $att) {
            if (isset($this->_attributeEnumFilters[$att])) {
                $this->_search->addFilter($att, '=', $this->_attributeEnumFilters[$att]);
            }
            else {
                $this->_search->addEnumFacet($att, 20);
            }
        }
    }

    protected function _addSort()
    {
        $this->_search->setOrder($this->_orderBy, $this->_orderDir);
    }

    protected function _addFilters()
    {
        foreach ($this->_facet->getFacetableEnumAttributes()as $attribute) {
            if ($value = Mage::app()->getRequest()->getParam($attribute)) {
                $this->addAttributeFilter($attribute, $value);
            }
        }
        $categoryVarName = Mage::getModel('merlinsearch/layer_filter_category')->getRequestVar();
        if ($value = Mage::app()->getRequest()->getParam($categoryVarName)) {
            $this->setCategoryFilter($value);
        }

        $categoryVarName = Mage::getModel('merlinsearch/layer_filter_price')->getRequestVar();
        if ($value = Mage::app()->getRequest()->getParam($categoryVarName)) {
            $priceValues = explode('-', $value);
            if (is_array($priceValues) && count($priceValues) == 2) {
                $this->setPriceFilterMin($priceValues[0]);
                $this->setPriceFilterMax($priceValues[1]);
            }
        }
    }

    protected function _initQuerySearch()
    {
        $this->_engine = Mage::getModel('merlinsearch/merlin_engine');
        $this->_search = Mage::getModel('merlinsearch/merlin_search', $this->_query);
        $this->_addFilters();
        $this->_addHistAttributes();
        $this->_addEnumAttributes();
    }

    protected function _addAdditionalFilters()
    {
        //VisibilityFilter
        //$s->addFilter(new \Merlin\Filter("visibility", '=', "catalog, search"));
    }

    protected function _sendRequest()
    {
        $this->_result = $this->_engine->search($this->_search);
        if (!$this->_result->getResults()) {
            Mage::throwException($this->_result->getMsg());
        }
    }

    protected function _initEnumFacets()
    {
        $this->_facet->initEnumFacets($this->_result);
    }

    protected function _initHistFacets()
    {
        $this->_facet->initHistFacets($this->_result);
    }

    public function loadFromQuery()
    {
        $this->_initQuerySearch();
        $this->_sendRequest();
        $this->_initEnumFacets();

        $this->_initQuerySearch();
        $this->_addPagination();
        $this->_addSort();
        $this->_search->setGroup('parent_id');
        $this->_sendRequest();
        $this->_initHistFacets();

        $this->_loadMagentoItems();
    }

    protected function _loadMagentoItems()
    {
        $this->_totalCount = $this->_result->getNumFound();

        $ids = array();
        foreach ($this->_result->getHits() as $prod) {
            $ids[] = $this->getMagentoParentId($prod);
        }
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        if (count($ids)) {
            $collection->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $ids) . ')'));
        }
        $this->addAttributeToSelect('*');
        $this->_select = $collection->getSelect();
    }

//    protected function getMerlinEngine()
//    {
//        return new \Merlin\MerlinSearch(
//                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company')), 
//                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment')), 
//                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance'))
//        );
//    }

    public function getFilterLabel($attributeCode, $filter)
    {
        if(!preg_match('/^[a-z0-9\&\.\,-_\s]$/', $filter))
        return $this->_facet->getAttributeLabel($attributeCode, $filter);
    }
    
    public function getCategories()
    {
        return $this->_facet->getAttributeFacet('category');
    }

    public function getAttributeFacet($attributeCode)
    {
        return $this->_facet->getAttributeFacet($attributeCode);
    }

    public function getPriceHist()
    {
        return $this->_facet->getAttributeHist('price');
    }

    public function getSize()
    {
        return $this->_totalCount;
    }

    public function getProductCountSelect()
    {
        return $this->_totalCount;
    }

    public function addCountToCategories($categoryCollection)
    {
        return $categoryCollection;
    }

    function setQuery($_query)
    {
        $this->_query = $_query;
    }

    function setCategoryFilter($_categoryFilter)
    {
        $this->_attributeEnumFilters["category"] = $_categoryFilter;
    }

    function setPriceFilterMin($_priceFilter)
    {
        $this->_attributeFiltersMin['price'] = $_priceFilter;
    }

    function setPriceFilterMax($_priceFilter)
    {
        $this->_attributeFiltersMax['price'] = $_priceFilter;
    }

    public function addAttributeFilter($name, $value)
    {
        $this->_attributeEnumFilters[$name] = $value;
    }

    function setVrec($_vrecId, $num = 5)
    {
        $this->_vrecId = $_vrecId;
        $this->_vrecNum = $num;
    }

}
