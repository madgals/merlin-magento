<?php

require_once(Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Merlin' . DIRECTORY_SEPARATOR . 'Merlin.php');

/**
 * MerlinIndexer: handles syncing data with Blackbird Merlin search engine
 * Uses Merlin PHP Library to handle updates, deletes, and add
 *
 * Mapping class is used to determine which attributes to upload and which get mapped to reserved fields
 *
 */
class Blackbird_Merlinsearch_Model_Indexer_Merlinindexer extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'merlinsearch_match_result';
    const BATCH_SIZE = 500;

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
    public function getName()
    {
        return 'Merlin indexer';
    }

    /**
     * Retrieve Indexer description
     * @return string
     */
    public function getDescription()
    {
        return 'Syncs the local DB with Blackbirds DB';
    }

    /**
     * Register data required by process in event object
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
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
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
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
    public function matchEvent(Mage_Index_Model_Event $event)
    {
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
    public function reindexAll()
    {
        $this->reindexMerlinData();
    }

    private function deleteMerlinProduct($id, $merlinEngine)
    {
        $c = new \Merlin\Crud();
        $c->addSubject(array('data' => array(array('id' => $id))));
        $r = $merlinEngine->delete($c);
        //Mage::log('  crud: ' . print_r($r, true));
    }


    private function reindexMerlinData($id = null)
    {
        set_time_limit(300);
        Mage::log('Merlinsearch reindexAll');
        //Mage::log(Mage::getBaseDir('lib').DIRECTORY_SEPARATOR.'Merlin'.DIRECTORY_SEPARATOR.'Merlin.php');
        //Mage::log(print_r($products, true));

        $merlin = $this->getMerlinEngine();

        Mage::app()->setCurrentStore('default');

        Mage::log(Mage::app()->getStore()->getId());

    	$mapping = Mage::helper('merlinsearch/mapping');
        $attributes = $mapping->getProductAttributesList();
        $attributes[] = "visibility";
        $temp_attributes = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        $currentPage = 1;
        $productsLoaded = 0;
        $batchLoaded = 0;
        $data = array();
        
        $limit = 100;
        $offset = 0;
        do {
            $products = $this->_getProductCollection($id);
            $products->getSelect()->limit($limit, $offset);
                       
            $offset += $limit;

            foreach ($products as $prod) {
                
                if ($prod->isSalable()) {
                    if ($prod->isConfigurable()) {
                        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $prod);
 
                        //$childProducts = $prod->getTypeInstance()->getUsedProducts(null, $prod);
                        foreach ($childProducts as $child) {
                            $data[] = $this->product2array($child, $mapping, $temp_attributes, $prod);
                            $productsLoaded++;
                            $batchLoaded++;
                        }
                    } else {
                        $data[] = $this->product2array($prod, $mapping, $temp_attributes);
                        $productsLoaded++;
                        $batchLoaded++;
                    }
                }
            }
            
            if ($batchLoaded >= self::BATCH_SIZE || !count($products)) {
                $c = new \Merlin\Crud();
                $c->addSubject(array('data' => $data));
                $r = $merlin->upload($c);
                $batchLoaded = 0;
                $data = array();
                Mage::log($r);
            }
            Mage::log("Current Page: " . $currentPage);
            // Prevents Timeouts on large catalogs by reseting PHP time state
            set_time_limit(300);
            
            $currentPage++;
        } while (count($products));

        Mage::log("Reindex Finished!!");

        if ($id != null && $productsLoaded == 0) {
            $this->deleteMerlinProduct($id, $merlin);
        }
        $merlin->close();
    }
    
    protected function _getProductCollection($id = null)
    {        
        $products = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect("*");
        //??
        $products->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);  
        $products->setStore(Mage::app()->getStore()->getId());
        $products->addStoreFilter(Mage::app()->getStore()->getId());
        $products->addUrlRewrite();
        if ($id != null) {
            $products->addAttributeToFilter('entity_id', array('in' => array($id))); //REMOVE AS WELL
        }
        return $products;
    }
    

    private function getMerlinEngine()
    {
        return new \Merlin\MerlinCrud(
            trim(Mage::getStoreConfig('merlinsearch/merlinconfig/company')),
            trim(Mage::getStoreConfig('merlinsearch/merlinconfig/environment')),
            trim(Mage::getStoreConfig('merlinsearch/merlinconfig/instance')),
            trim(Mage::getStoreConfig('merlinsearch/merlinconfig/token')),
            trim(Mage::getStoreConfig('merlinsearch/merlinconfig/user'))
        );
    }

    private function _getParentId($product)
    {
    	if ($product->getTypeId() == "simple") {
	        $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
            if (!$parentIds) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            }
            if ($parentIds) {
                return $parentIds[0];
            }
	    }
        return null;
    }

    private function product2array($product, $mapping, $attributes, $parent = null)
    {
        $params = array();
        if ($parent != null) {
            $params['parent_id'] = "pid" . $parent->getId();
            $params['id'] = $product->getId();
            $params += $this->attributes2array($product, $mapping, $attributes);
            $params += $this->productImages2array($product, $mapping);
            $product = $parent;
        } else {
	        $parent_id = $this->_getParentId($product);
	        if ($parent_id) {
		        $params['parent_id'] = "pid" . $parent_id;
	        } else {
	    	    $params['parent_id'] = "cid" . $product->getId();
	        }
            $params['id'] = $product->getId();
	        $params += $this->attributes2array($product, $mapping, $attributes);
            $params += $this->productImages2array($product, $mapping);
        }

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
        }


        $ids = $product->getCategoryIds();
        
        foreach ($ids as $catid) {
            $catname = Mage::getModel('catalog/category')->load($catid)->getName();
            if (!isset($params['category'])) {
                $params['category'] = array();
            }
            $params['category'][] = $catname;
        }

        //ATTRIBUTE MAPPING TRUMPS ALL OTHER
	    $params += $this->attributes2array($product, $mapping, $attributes);

        return $params;
    }

    private function productImages2array($product, $mapping)
    {
        $params = array();
        try {
            if ($product->hasImage()) {
                $images = array($product->getImageUrl());
                if ($mapping->isValidPair("images", $images)) {
                    $params['images'] = array_values($images);
                }

                $thumbnails = array($product->getSmallImageUrl());
                if ($mapping->isValidPair("thumbnails", $thumbnails)) {
                    $params['thumbnails'] = array_values($thumbnails);
                }
            }
        } catch (Exception $e) {
            Mage::log('  cant find image for product:' . $product->getId());
        }
        return $params;
    }

    private function attributes2array($product, $mapping, $attributes)
    {
	    $attributeMap = $mapping->getProductAttributesDict();
        //$attributes = $product->getAttributes();
        //$attributes = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
        $params = array();
        foreach ($attributes as $attribute) {
            $key = $attribute->getAttributeCode();
            $value = $product->getResource()->getAttribute($key)->getFrontend()->getValue($product);
            if (array_key_exists($attribute->getName(), $attributeMap)) {
                $field = $attributeMap[$key];
                if (preg_match("/s$/", $field) && isset($value)) {
                    if (!is_array($value) && $value) {
                        $value = array((string)$value);
                    }
                }
                if($mapping->isValidPair($field, $value) && isset($value)) {
                    $params[$field] = $value;
                } else if ($field == "images" || $field == "thumbnails") {
                    $new_val = $product->_getData($key);
                    if (isset($new_val)) {
                        $params[$field] = array(Mage::getModel('catalog/product_media_config')->getMediaUrl($product->_getData($key)));
                    }
                }
            }
            else {// if ($attribute->getIsVisibleOnFront() && $product->getData($attribute->getAttributeCode())) {
                if (in_array($key, $mapping->getReservedFields())) {
                    if ($mapping->isValidPair($key, $value)) {
                        $params[$key] = $value;
                    }
                } else {
                    $params[$key] = $value;
                }
            }
        }
        return $params;
    }
}
