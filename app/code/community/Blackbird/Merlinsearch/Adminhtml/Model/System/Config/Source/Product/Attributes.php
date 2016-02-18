<?php

function compare_label($a, $b) {
        return strnatcmp($a["label"], $b['label']);
}

class Blackbird_Merlinsearch_Adminhtml_Model_System_Config_Source_Product_Attributes{
	
	public function toOptionArray(){
	$options = array();
	$entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getEntityTypeId();
	//$attributes = Mage::getModel('eav/entity_attribute')->getCollection()->addFilter('entity_type_id', $entityTypeId)->setOrder('attribute_code', 'ASC');
	$attributes = Mage::getModel('eav/entity_attribute')->getCollection()->addFilter('entity_type_id', $entityTypeId);
	foreach ($attributes as $attribute){
		$item = array();
		$item['value'] = $attribute->getAttributeCode();
		if ($attribute->getFrontendLabel()){
			$item['label'] = $attribute->getFrontendLabel();
		}
		else{
            //$item['label'] = $attribute->getAttributeCode();
            continue;
		}
		$options[] = $item;
	}
    usort($options, compare_label);
	$options[] = array('value' => null, 'label' => 'None');
    return $options;
	}
}		
