<?php

require_once(Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Merlin' . DIRECTORY_SEPARATOR . 'Merlin.php');

class Blackbird_Merlinsearch_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {
	
    protected $_query;
    protected $_vrecId;
    protected $_vrecNum;
    
    protected $_attributeFiltersMax;
    protected $_attributeFiltersMin;
    
    protected $_facetableHistAttributes;
    protected $_facetableEnumAttributes;
    protected $_enumFacets;
    protected $_histFacets;
    
    protected $_items;
    protected $_totalCount;
    protected $_isLoaded = false;
    protected $_orderBy;
    protected $_orderDir;

    function __construct() {
        $mapping = new Blackbird_Merlinsearch_Helper_Mapping();

        $this->_facetableEnumAttributes = $mapping->getEnumFacets();  //array("category");
        $this->_facetableHistAttributes = array("price" => array(0, 500, 100));
        $this->_attributeEnumFilters = array();
	    $this->_attributeFiltersMin = array();
	    $this->_attributeFiltersMax = array();
        $this->_enumFacets = array();
        $this->_histFacets = array();
        $this->_items = array();
        $this->_curPage = 1;
    }

    function setOrder($_orderBy, $_orderDir) {
        if ($_orderBy == 'relevance') {
            return;
        }
        $this->_orderBy = $_orderBy;
        $this->_orderDir = $_orderDir;
    }

    public function getCurPage($displacement = 0) {
        return $this->_curPage + $displacement;
    }
    
    public function load() {
        if ($this->_isLoaded) {
            return;
        } else {
            $this->_isLoaded = true;
        }
        if(isset($this->_vrecId)){
            $this->loadVrec();
        }else{
            $this->loadFromQuery();
        }
    }
    
    public function loadVrec(){
        $engine = $this->getMerlinEngine();
        $v = new \Merlin\Vrec($this->_vrecId, null, $this->_vrecNum);
        $r = $engine->vrec($v);
        
        if(!isset($r->results)){
            throw new Exception($r->msg);
        }
        
        $this->_totalCount = $r->results->numfound;
        foreach ($r->results->hits as $prod) {
            $bprod = $this->translate($prod);
            $this->_items[$bprod->getEntityId()] = $bprod;
        }
    }

    private function _setEnumFacet($r, $att){
        if (isset($r->results->facets->enums->$att)) {
            foreach ($r->results->facets->enums->$att->enums as $enum) {
                $this->_enumFacets[$att][] = array(
                    'label' => $enum->term,
                    'value' => $enum->term,
                    'count' => $enum->count,
                );
            }
        }
    }

    private function _setHistFacet($r, $att){
        if (isset($r->results->facets->histograms->$att)) {
            foreach ($r->results->facets->histograms->$att->histograms as $hist) {
                $this->_histFacet[$att] = array(
                    'from' => $hist->from,
                    'to' => $hist->to,
                    'value' => $hist->from . '-' . $hist->to,
                    'count' => $hist->count,
                );
            }
        }
    }
    
    public function loadFromQuery() {
        
        $engine = $this->getMerlinEngine();
        $s = (new \Merlin\Search($this->_query));
        $limit = $this->getPageSize();
        if (!$limit) {
            $limit = 12;
        }
        $s->setNum($limit);
        $page = $this->getCurPage();
        if (isset($page) && $page > 1) {
            $s->setStart(($page - 1) * $limit);
        }
    
	    $s->setGroup(new \Merlin\Group('parent_id'));


        foreach ($this->_facetableHistAttributes as $att => $val){
            if (isset($this->_attributeFiltersMax[$att])) {
            $s->addFilter(new \Merlin\Filter($att, '>', $this->_attributeFiltersMin[$att]));
            $s->addFilter(new \Merlin\Filter($att, '>', $this->_attributeFiltersMax[$att]));
            } else {
            Mage::log($val);
            $s->addFacet(new \merlin\HistFacet($att, $val[0], $val[1], $val[2]));	
            }
        }

        foreach ($this->_facetableEnumAttributes as $att) {
                if (isset($this->_attributeEnumFilters[$att])) {
                    $s->addFilter(new \Merlin\Filter($att, '=', $this->_attributeFilters[$att]));
                } else {
                    $s->addFacet(new \Merlin\EnumFacet($att, 5));
                }
            }

        if (isset($this->_orderBy)) {
            $s->addSort(new \Merlin\Sort($this->_orderBy, $this->_orderDir));
        }
        $r = $engine->search($s);
        if(!isset($r->results)){
            throw new Exception($r->msg);
        }

	
        foreach ($this->_facetableEnumAttributes as $att) {
        	$this->_setEnumFacet($r, $att);
	}

	foreach ($this->_facetableHistAttributes as $att => $val) {
		$this->_setHistFacet($r, $att);
	}

        
	$this->_totalCount = $r->results->numfound;
        foreach ($r->results->hits as $prod) {
	    $bprod = Mage::getModel('catalog/product')->load($prod->id);
            $this->_items[$bprod->getEntityId()] = $bprod;
        }
	
    }

    private function getMerlinEngine() {
        return new \Merlin\MerlinSearch(
                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance'))
        );
    }

    public function getCategories() {
        $this->load();
        return $this->_enumFacets['category'];
    }

    public function getAttributeFacet($attName) {
        $this->load();
        return $this->_enumFacets[$attName];
    }

    public function getPriceHist() {
        $this->load();
        return $this->_histFacets['price'];
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true) {
        $this->load();
        return $this->_totalCount;
    }

    public function getSize() {
        $this->load();
        return $this->_totalCount;
    }

    public function getProductCountSelect() {
        $this->load();
        return $this->_totalCount;
    }

    public function getSetIds() {
        $this->load();
        $select = $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->distinct(true);
        $select->columns('attribute_set_id');
        return $this->getConnection()->fetchCol($select);
    }

    protected function _buildClearSelect($select = null) {
        return null;
    }

    public function addCountToCategories($categoryCollection) {
        return $categoryCollection;
    }

    function setQuery($_query) {
        $this->_query = $_query;
    }

    function setCategoryFilter($_categoryFilter) {
        $this->_attributeEnumFilters["category"] = $_categoryFilter;
    }

    function setPriceFilterMin($_priceFilter) {
        $this->_attributeFiltersMin['price'] = $_priceFilter;
    }

    function setPriceFilterMax($_priceFilter) {
	    $this->_attributeFiltersMax['price'] = $_priceFilter;
    }

    function addFacetableAttribute($_facetableAttribute) {
        $this->_facetableEnumAttributes[] = $_facetableAttribute;
    }
    
    public function addAttributeFilter($name, $value){
        $this->_attributeEnumFilters[$name] = $value;
    }

    function setVrec($_vrecId, $num = 5) {
        $this->_vrecId = $_vrecId;
        $this->_vrecNum = $num;
    }


}

