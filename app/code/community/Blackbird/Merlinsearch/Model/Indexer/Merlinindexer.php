<?php

require_once(Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Merlin' . DIRECTORY_SEPARATOR . 'Merlin.php');

class Blackbird_Merlinsearch_Model_Indexer_Merlinindexer extends Mage_Index_Model_Indexer_Abstract {

    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'merlinsearch_match_result';

    
    
    /**
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
            Mage_Index_Model_Event::TYPE_DELETE
        )
    );

    /*
    * Generate Mapping for Blackbird Reserved Fields
    */
    
    // protected $_mapping = new Blackbird_Merlinsearch_Helper_Mapping();

    /**
     * Retrieve Indexer name
     * @return string
     */
    public function getName() {
        return 'Merlin indexer';
    }

    /**
     * Retrieve Indexer description
     * @return string
     */
    public function getDescription() {
        return 'Syncs the local DB with Blackbirds DB';
    }

    /**
     * Register data required by process in event object
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        $dataObj = $event->getDataObject();
        if ($event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
            $event->addNewData('merlinsearch_update_product_id', $dataObj->getId());
        } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            $event->addNewData('merlinsearch_delete_product_id', $dataObj->getId());
        } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_MASS_ACTION) {
            $event->addNewData('merlinsearch_mass_action_product_ids', $dataObj->getProductIds());
        }
    }

    /**
     * Process event
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        $data = $event->getNewData();
        if (!empty($data['merlinsearch_update_product_id'])) {
            //Mage::log('Update: ' . print_r($data['merlinsearch_update_product_id'], true));
            $this->reindexMerlinData($data['merlinsearch_update_product_id']);
//            $merlin = $this->getMerlinEngine();
//            $product = Mage::getModel('catalog/product')->load($data['merlinsearch_update_product_id']);
//            $c = new \Merlin\Crud();
//            $data=array();
//            if ($product->isConfigurable()) {
//                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
//                foreach ($childProducts as $child) {
//                    $data[] = $this->product2array($child, $product);
//                }
//            } else {
//                $data[] = $this->product2array($product);
//            }
//            $c->addSubject(array('data' =>$data));
//            $r = $merlin->update($c);
//            //Mage::log('  crud: ' . print_r($r, true));
//            $merlin->close();
        } elseif (!empty($data['merlinsearch_delete_product_id'])) {
            $merlin = $this->getMerlinEngine();
            $this->deleteMerlinProduct($data['merlinsearch_delete_product_id'], $merlin);
            $merlin->close();
        } elseif (!empty($data['merlinsearch_mass_action_product_ids'])) {
            //Mage::log('Mass: ' . print_r($data['yourmodule merlinsearch_mass_action_product_ids'], true));
        }
    }

    /**
     * match whether the reindexing should be fired
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event) {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }
        $entity = $event->getEntity();
        $result = true;
        if ($entity != Mage_Catalog_Model_Product::ENTITY) {
            return;
        }
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);
        return $result;
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll() {
        $this->reindexMerlinData();
    }

    private function deleteMerlinProduct($id, $merlinEngine) {
        $c = new \Merlin\Crud();
        $c->addSubject(array('data' => array(array('id' => $id))));
        $r = $merlinEngine->delete($c);
        //Mage::log('  crud: ' . print_r($r, true));
    }

    private function reindexMerlinData($id = null) {
        //Mage::log('Merlinsearch reindexAll');
        //Mage::log(Mage::getBaseDir('lib').DIRECTORY_SEPARATOR.'Merlin'.DIRECTORY_SEPARATOR.'Merlin.php');
        //Mage::log(print_r($products, true));


        $merlin = $this->getMerlinEngine();

        Mage::app()->setCurrentStore('default');
        //Mage::log(Mage::app()->getStore()->getId());
		
    	$mapping = new Blackbird_Merlinsearch_Helper_Mapping();
	//$attributes = $this->_mapping->getProductAttributesList();
	$attributes = $mapping->getProductAttributesList();
        //$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect($attributes);
        $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect("*");
        //$products->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        $products->setStore(Mage::app()->getStore()->getId());
        $products->addStoreFilter(Mage::app()->getStore()->getId());
        $products->addUrlRewrite();
        $products->setPageSize(10);
        if ($id != null) {
            $products->addAttributeToFilter('entity_id', array('in' => array($id))); //REMOVE AS WELL
        }

        $pages = $products->getLastPageNumber();
        $currentPage = 1;
        $productsLoaded = 0;

        do {
            $products->setCurPage($currentPage);
            $products->load();

            $data = array();
            foreach ($products as $prod) {
                //$product = Mage::getModel('catalog/product')->load($prod->getId());

                if ($prod->isConfigurable()) {
                    $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $prod);
                    foreach ($childProducts as $child) {
                        $data[] = $this->product2array($child, $prod);
                        $productsLoaded++;
                    }
                } else if ($prod->isSuper()){continue;}
		else {
                    $data[] = $this->product2array($prod);
                    $productsLoaded++;
                }


//                $arr = $prod->toArray();
//                foreach ($arr as $key => $value) {
//                    Mage::log("$key => $value");
//                }
                //Mage::log('  $data: ' . print_r($data, true));
                //break 2;
            }

            $c = new \Merlin\Crud();
            $c->addSubject(array('data' => $data));
	    //Mage::log($data);
	    //Mage::log($c);
//            if ($id == null) {
                $r = $merlin->upload($c);
		Mage::log($r);
//            } else {
//                $r = $merlin->update($c);
//            }
            //Mage::log('  crud: ' . print_r($r, true));
            //Mage::log('  $data: ' . print_r($data, true));
            //break;

            $currentPage++;
            //clear collection and free memory
            $products->clear();
        } while ($currentPage <= $pages);
        
        if ($id != null && $productsLoaded == 0) {
            $this->deleteMerlinProduct($id, $merlin);
        }
        $merlin->close();
    }

    private function getMerlinEngine() {
        return new \Merlin\MerlinCrud(
                trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/token')), trim(Mage::getStoreConfig('merlinsearch/merlinconfig/user'))
        );
    }

    private function _getParentId($product){
    	if ($product->getTypeId() == "simple"){
	    $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
	    if (!$parentIds){
		$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
	    }
	    if ($parentIds) return $parentIds[0];
	}
	return null;
    }

    private function product2array2($product, $parent = null){
		$params = $this->product2array($product, $parent);
		
		//DO mapping
		#$attributes = $this->_mapping->getProductAttributesDict();		
    		$mapping = new Blackbird_Merlinsearch_Helper_Mapping();
		$attributes = $mapping->getProductAttributesDict();		
		//$attributes = $product->getAttributes();
		foreach ($attributes as $key => $value){
			$d = $product->_getData($value);
			if (preg_match("/s$/", $key)){
				if (!is_array($d)){
                                    $d = array((string)$d);
                                }
			}
			if($mapping->isValidPair($key, $d) && $value != ""){
			    $params[$key] = $d;
			}
		}
		
		$params['id'] = $product->getId();
		$params['title'] = $product->getName();
		return $params;	
	}

    private function product2array($product, $parent = null) {
        //Mage::log('product:' . $product->getId());
	
	$mapping = new Blackbird_Merlinsearch_Helper_Mapping();
        $params = array();
        if ($parent != null) {
	    /*Mage::log("Config Parent " . (string)$parent->isConfigurable());
	    Mage::log("Super Parent " . (string)$parent->isSuper());
            Mage::log("Grouped Parent: " . $parent->isGrouped());
	    Mage::log("Config Prod " . (string)$product->isConfigurable());
	    Mage::log("Super Prod " . (string)$product->isSuper());
            Mage::log("Grouped Prod: " . $product->isGrouped());
	    Mage::log("Type product: " . $product->getTypeId());
	    Mage::log("Type parent: " . $parent->getTypeId());
	    Mage::log($product->getId()); */
	    #$aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $params['parent_id'] = "pid" . $parent->getId();
            $params['id'] = $product->getId();
            $params += $this->attributes2array($product, $mapping);
            $params += $this->productImages2array($product);

            $product = $parent;
        } else {
	    $parent_id = $this->_getParentId($product);
	    if ($parent_id) {
		$params['parent_id'] = "pid" . $parent_id;
	        Mage::log("Parent Id: " . $parent_id . " Product id: " . $product->getId());
	    } else {
	    	$params['parent_id'] = "cid" . $product->getId();
	    }
            $params['id'] = $product->getId();
            $params += $this->productImages2array($product);
        }
        
	$params += $this->attributes2array($product, $mapping);
        
	$params += array(
            'title' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getFinalPrice(),
            'url' => $product->getProductUrl()
        );

        if ($product->isSuper()) {
            $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
	    $prices = array();
            foreach ($aProductIds as $ids) {
                foreach ($ids as $id) {
                    $aProduct = Mage::getModel('catalog/product')->load($id);
                    $prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
                }
            }

            $minPrice = min($prices);
            $params['minimal_price'] = $minPrice;
            $params['price'] = $minPrice;
            //Mage::log($product->getId() . '|' . $product->getFinalPrice() . '|' . $minPrice);
        }


        $ids = $product->getCategoryIds();

        //Mage::log('$params: ' . print_r($params, true));
        foreach ($ids as $catid) {
            $catname = Mage::getModel('catalog/category')->load($catid)->getName();
            //Mage::log('$catname: '.$catname);
            if (!isset($params['category'])) {
                $params['category'] = array();
            }
            $params['category'][] = $catname;
        }

        //Mage::log('  params: ' . print_r($params, true));

        return $params;
    }

    private function productImages2array($product) {
        $params = array();
        $params['images'] = array();
        $params['thumbnails'] = array();
        try {
            if ($product->hasImage()) {
                $params['images'][] = $product->getImageUrl();;
                $params['thumbnails'][] = $product->getSmallImageUrl();
            }
        } catch (Exception $e) {
            Mage::log('  cant find image for product:' . $product->getId());
        }
        return $params;
    }

    private function attributes2array($product, $mapping) {
	$attributeMap = $mapping->getProductAttributesDict();		
        $attributes = $product->getAttributes();
        $params = array();
        foreach ($attributes as $attribute) {
	    if (array_key_exists($attribute->getName(), $attributeMap)){
		$key = $attribute->getName();
		$value = $product->_getData($key);
		if (preg_match("/s$/", $key)){
			if (!is_array($value)){
			    $value = array((string)$value);
			}
		}
		if($mapping->isValidPair($key, $value) && $attributeMap[$key] != ""){
		    $params[$key] = $value;
		}
	    }
            else if ($attribute->getIsVisibleOnFront() && $product->getData($attribute->getAttributeCode())) {
                $value = $attribute->getFrontend()->getValue($product);
                $code = $attribute->getAttributeCode();
                //Mage::log($product->getId() . '  att: ' . $code . '=' . $value);
                switch ($code) {
                    case 'gender':
                        if (!in_array(strtolower($value), array('male', 'female', 'unisex'))) {
                            break;
                        }
                    default:
                        $params[$code] = $value;
                }
            }
        }
        return $params;
    }

}
