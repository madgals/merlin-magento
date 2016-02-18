<?php

require_once(Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Merlin' . DIRECTORY_SEPARATOR . 'Merlin.php');

class Blackbird_Merlinsearch_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {

    protected $_query;
    protected $_vrecId;
    protected $_vrecNum;
    
    protected $_categoryFacet;
    protected $_categoryFilter;
    
    protected $_priceFacet;
    protected $_priceFilterMin;
    protected $_priceFilterMax;
    
    protected $_facetableAttributes;
    protected $_attributeFilters;
    protected $_attributeFacets;
    
    protected $_items;
    protected $_totalCount;
    protected $_isLoaded = false;
    protected $_orderBy;
    protected $_orderDir;

    function __construct() {
        $this->_categoryFacet = array();
        $this->_priceFacet = array();
        $this->_facetableAttributes = array();
        $this->_attributeFilters = array();
        $this->_attributeFacets = array();
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
        //Mage::log($v->__toString());
        $r = $engine->vrec($v);
        
        if(!isset($r->results)){
            throw new Exception($r->msg);
        }
        
        $this->_totalCount = $r->results->numfound;
        //$col = array();
        foreach ($r->results->hits as $prod) {
            $bprod = $this->translate($prod);
            $this->_items[$bprod->getEntityId()] = $bprod;
        }
    }

    public function loadFromQuery() {
        //Mage::log('load');
        
        $engine = $this->getMerlinEngine();
        $s = (new \Merlin\Search($this->_query));
        $limit = $this->getPageSize();
        if (!$limit) {
            $limit = 12;
        }
        $s->setNum($limit);
        $page = $this->getCurPage();
        //Mage::log('load ' . $page);
        if (isset($page) && $page > 1) {
            $s->setStart(($page - 1) * $limit);
        }
        $s->setGroup(new \Merlin\Group('parent_id'));


        if (isset($this->_categoryFilter)) {
            $s->addFilter(new \Merlin\Filter('category', '=', $this->_categoryFilter));
        } else {
            $s->addFacet(new \Merlin\EnumFacet('category', 5));
        }
        if (isset($this->_priceFilterMax)) {
            $s->addFilter(new \Merlin\Filter('price', '>', $this->_priceFilterMin));
            $s->addFilter(new \Merlin\Filter('price', '<', $this->_priceFilterMax));
        } else {
            $s->addFacet(new \Merlin\HistFacet('price', 0, 500, 100));
        }
        foreach ($this->_facetableAttributes as $att) {
            if (isset($this->_attributeFilters[$att])) {
                $s->addFilter(new \Merlin\Filter($att, '=', $this->_attributeFilters[$att]));
            } else {
                $s->addFacet(new \Merlin\EnumFacet($att, 5));
            }
        }

        if (isset($this->_orderBy)) {
            $s->addSort(new \Merlin\Sort($this->_orderBy, $this->_orderDir));
        }
        $r = $engine->search($s);
        //Mage::log($s->__toString());
        if(!isset($r->results)){
            throw new Exception($r->msg);
        }
        //Mage::log(print_r($r,true));
        if (isset($r->results->facets->enums->category)) {
            foreach ($r->results->facets->enums->category->enums as $enum) {
                $this->_categoryFacet[] = array(
                    'label' => $enum->term,
                    'value' => $enum->term,
                    'count' => $enum->count,
                );
            }
        }
        if (isset($r->results->facets->histograms->price)) {
            foreach ($r->results->facets->histograms->price->histograms as $hist) {
                $this->_priceFacet[] = array(
                    'from' => $hist->from,
                    'to' => $hist->to,
                    'value' => $hist->from . '-' . $hist->to,
                    'count' => $hist->count,
                );
            }
        }
        foreach ($this->_facetableAttributes as $att) {
            if (isset($r->results->facets->enums->$att)) {
                $this->_attributeFacets[$att] = array();
                foreach ($r->results->facets->enums->$att->enums as $enum) {
                    $this->_attributeFacets[$att][] = array(
                        'label' => $enum->term,
                        'value' => $enum->term,
                        'count' => $enum->count,
                    );
                }
            }
        }

        $this->_totalCount = $r->results->numfound;
        //$col = array();
        foreach ($r->results->hits as $prod) {
            $bprod = $this->translate($prod);
            $this->_items[$bprod->getEntityId()] = $bprod;
        }

        //Mage::log('load');
        //Mage::log(print_r($this->_items[439]->getData(''), true));
    }

    private function getMerlinEngine() {
        return new \Merlin\MerlinSearch(
                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance'))
        );
    }

    private static $mappings = array(
        'title' => 'name',
        'id' => 'entity_id',
        'description' => 'short_description',
        'small_image' => 'small_image',
        'thumbnail' => 'thumbnail',
        'price' => 'final_price'
    );

    private function translate($merlinProduct) {
        $prod = new Blackbird_Merlinsearch_Model_Product();
        $prod->setData('entity_type_id', '4');
        $prod->setData('is_salable', 0);
//$prod->setData('type_id', 'configurable');
//        $prod->setData('tax_class_id', 2);
//        $prod->setData('msrp_enabled', 2);
//        $prod->setData('msrp_display_actual_price_type', 4);

        foreach ($merlinProduct as $key => $value) {
            switch ($key) {
//                case 'price':
//                    $prod->setData('price', $value);
//                    $prod->setData('final_price', $value);
//                    $prod->setData('min_price', $value);
//                    $prod->setData('max_price', $value);
//                    break;
                case 'minimal_price':
                    //Mage::log('minimal_price' . $value);
                    $prod->setData('final_price', null);
                    $prod->setData('minimal_price', $value);
                    $prod->setData('min_price', $value);
                    $prod->setData('type_id', 'grouped');
                    unset($merlinProduct->price);
                    break;
                case 'url':
                    $prod->setData('url', $value);
                    break;
                case 'category':
                    break;
                default:
                    if (isset(self::$mappings[$key])) {
                        $prod->setData(self::$mappings[$key], $value);
                    }
            }
        }

        $qty = 10;
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->setData('manage_stock', 1);
        $stockItem->setData('is_in_stock', $qty ? 1 : 0);
        $stockItem->setData('use_config_manage_stock', 0);
        $stockItem->setData('stock_id', 1);
        $stockItem->setData('product_id', $prod->getId());
        $stockItem->setData('qty', $qty);
        $prod->setStockItem($stockItem);

        return $prod;
    }

    public function getCategories() {
        $this->load();
        return $this->_categoryFacet;
    }

    public function getAttributeFacet($attName) {
        $this->load();
        return $this->_attributeFacets[$attName];
    }

    public function getPriceHist() {
        $this->load();
        return $this->_priceFacet;
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
        return null;
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
        $this->_categoryFilter = $_categoryFilter;
    }

    function setPriceFilterMin($_priceFilter) {
        $this->_priceFilterMin = $_priceFilter;
    }

    function setPriceFilterMax($_priceFilter) {
        $this->_priceFilterMax = $_priceFilter;
    }

    function addFacetableAttribute($_facetableAttribute) {
        if ($_facetableAttribute == 'price') {
                return;
            }
        $this->_facetableAttributes[] = $_facetableAttribute;
    }
    
    public function addAttributeFilter($name, $value){
        $this->_attributeFilters[$name] = $value;
    }

    function setVrec($_vrecId, $num = 5) {
        $this->_vrecId = $_vrecId;
        $this->_vrecNum = $num;
    }


}

/*
      (
      [entity_id] => 402
      [entity_type_id] => 4
      [attribute_set_id] => 13
      [type_id] => configurable
      [sku] => msj000c
      [has_options] => 1
      [required_options] => 1
      [created_at] => 2013-03-05 07:25:10
      [updated_at] => 2013-03-20 17:58:34
      [relevance] => 0.0000
      [price] => 190.0000
      [tax_class_id] => 2
      [final_price] => 190.0000
      [minimal_price] => 190.0000
      [min_price] => 190.0000
      [max_price] => 190.0000
      [tier_price] =>
      [cat_index_position] => 30010
      [name] => French Cuff Cotton Twill Oxford
      [small_image] => /m/s/msj000t_2.jpg
      [thumbnail] => /m/s/msj000t_2.jpg
      [url_key] => french-cuff-cotton-twill-oxford
      [image_label] =>
      [small_image_label] =>
      [thumbnail_label] =>
      [msrp_enabled] => 2
      [msrp_display_actual_price_type] => 4
      [short_description] => Made with wrinkle resistant cotton twill, this French-cuffed luxury dress shirt is perfect for Business Class frequent flyers.
      [special_price] =>
      [msrp] =>
      [news_from_date] =>
      [news_to_date] =>
      [special_from_date] =>
      [special_to_date] =>
      [status] => 1
      [do_not_use_category_id] => 1
      [request_path] => french-cuff-cotton-twill-oxford-570.html
      [is_salable] => 1
      [stock_item] => Varien_Object Object
      (
      [_data:protected] => Array
      (
      [is_in_stock] => 1
      )

      [_hasDataChanges:protected] =>
      [_origData:protected] =>
      [_idFieldName:protected] =>
      [_isDeleted:protected] =>
      [_oldFieldsMap:protected] => Array
      (
      )

      [_syncFieldsMap:protected] => Array
      (
      )

      )

      )
     */