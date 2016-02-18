<?php

class Blackbird_Merlinsearch_Helper_Mapping{

    protected $_reserved_fields = array("id", "title", "description", "price",
			"images", "thumbnails", "sizes", "colors", 
			"tags", "timestamp", "availability", "offer",
			"age", "brand", "geo", "gender");
    
    protected $_reserved_types = array("id"=>["string", "int"], 
                                    "title"=>["string"],
                                    "description"=>["string"],
                                    "price"=>["float"],
                                    "url"=>["string"],
                                    "images"=>["multi-string"],
                                    "thumbnails"=>["multi-string"],
                                    "sizes"=>["multi-string"],
                                    "colors"=>["multi-string"],
                                    "tags"=>["multi-string"],
                                    "timestamp"=>["string"],
                                    "availability"=>["string"],
                                    "offer"=>["string"],
                                    "gender"=>["string"],
                                    "age"=>["string"],
                                    "brand"=>["string"],
                                    "geo"=>["string"]);
    
    protected $_reserved_enum = array("colors"=>["Black",
                                                "Blue",
                                                "Brown",
                                                "Cream",
                                                "Gold",
                                                "Gray",
                                                "Green",
                                                "Lime",
                                                "Magenta",
                                                "Navy",
                                                "Orange",
                                                "Pink",
                                                "Purple",
                                                "Red",
                                                "Salmon",
                                                "Silver",
                                                "Teal",
                                                "White",
                                                "Yellow",
                                                "Animal Print",
                                                "Baby Blue",
                                                "Beige",
                                                "Burgundy",
                                                "Charcoal",
                                                "Dark Blue",
                                                "Dark Brown",
                                                "Dark Cream",
                                                "Dark Gold",
                                                "Dark Gray",
                                                "Dark Green",
                                                "Dark Lime",
                                                "Dark Magenta",
                                                "Dark Navy",
                                                "Dark Orange",
                                                "Dark Pink",
                                                "Dark Purple",
                                                "Dark Red",
                                                "Dark Salmon",
                                                "Dark Silver",
                                                "Dark Teal",
                                                "Dark Yellow",
                                                "Grey",
                                                "Ivory",
                                                "Light Blue",
                                                "Light Brown",
                                                "Light Gold",
                                                "Light Gray",
                                                "Light Green",
                                                "Light Lime",
                                                "Light Magenta",
                                                "Light Orange",
                                                "Light Pink",
                                                "Light Purple",
                                                "Light Red",
                                                "Light Salmon",
                                                "Light Silver",
                                                "Light Teal",
                                                "Light Yellow",
                                                "Metallic",
                                                "Mint",
                                                "Multicolor",
                                                "Navy Blue",
                                                "Neutral",
                                                "Nude",
                                                "Olive",
                                                "Olive Green",
                                                "Pattern",
                                                "Plaid",
                                                "Plum",
                                                "Print",
                                                "Stripe",
                                                "Tan"],
                                        "availability" => ["Discontinued",
                                                            "InStock",
                                                            "InStoreOnly",
                                                            "LimitedAvailability",
                                                            "OnlineOnly",
                                                            "OutOfStock",
                                                            "PreOrder",
                                                            "SoldOut"]);

                        

                    
                            	
	
    public function getProductAttributesList(){
        $attributes =  array();
        foreach ($this->_reserved_fields as $field){
            $value  = trim(Mage::getStoreConfig('merlinsearch/merlinindex/' . $field));
            if ($value != "None"){
            $attributes[] = $value;
            }
        }
        return $attributes;
    }
    
    public function getProductAttributesDict(){
        $attributes =  array();
        foreach ($this->_reserved_fields as $field){
            $value  = trim(Mage::getStoreConfig('merlinsearch/merlinindex/' . $field));
            if ($value != "None"){
            $attributes[$field] = $value;
            }
        }
        return $attributes;
    }

    public function isValidPair($field, $value){
        $r_types = $this->_reserved_types[$field];
        $valid = false;
        foreach ($r_types as $r_type){
            if ($r_type == "string"){
                if (is_string($value)){$valid = true;}
            }
            else if ($r_type == "float"){
                if (is_double($value)){$valid = true;}
            }
            else if ($r_type == "multi-string"){
                if ($this->isMultiString($value)){$valid = true;}
            }
            else if ($r_type == "int"){
                if (is_int($value)){$valid = true;}
            }
        }
        return $valid; 
 
    }

    private function isMultiString($value){
        $valid = false;
        if (is_array($value)){
            if (count($value) >= 1){
                $valid = true;
                foreach ($value as $val){
                    if (!is_string($val)){
                        $valid = false;
                    }
                }
            }
        }
        return $valid;
    }
}

