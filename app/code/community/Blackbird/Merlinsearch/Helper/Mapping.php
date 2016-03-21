<?php

class Blackbird_Merlinsearch_Helper_Mapping
{
    protected $_reserved_fields = array(
        "id",
        "title",
        "description",
        "price",
		"images",
        "thumbnails",
        "sizes",
        "colors",
		"tags",
        "timestamp",
        "availability",
        "offer",
        "age",
        "brand",
        "geo",
        "gender",
        "extra_field"
    );

    protected $_facet_fields = array("enum_facets");

    protected $_reserved_types = array(
        "id"=> array("string", "int"),
        "title"=> array("string"),
        "description"=> array("string"),
        "price"=> array("float"),
        "url"=> array("string"),
        "images"=> array("multi-string-url"),
        "thumbnails"=> array("multi-string-url"),
        "sizes"=> array("multi-string"),
        "colors"=> array("multi-string"),
        "tags"=> array("multi-string"),
        "timestamp"=> array("string"),
        "availability"=> array("string"),
        "offer"=> array("string"),
        "gender"=> array("string-gender"),
        "age"=> array("string"),
        "brand"=> array("string"),
        "geo"=> array("string")
    );

    protected $_reserved_enum = array(
        "colors" => array(
            "Black",
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
            "Tan"
        ),
        "availability" => array(
            "Discontinued",
            "InStock",
            "InStoreOnly",
            "LimitedAvailability",
            "OnlineOnly",
            "OutOfStock",
            "PreOrder",
            "SoldOut"
        )
    );



    public function getReservedFields()
    {
        return $this->_reserved_fields;
    }

    public function getProductAttributesList()
    {
        $attributes =  array();
        foreach ($this->_reserved_fields as $field) {
            $value  = trim(Mage::getStoreConfig('merlinsearch/merlinindex/' . $field));
            if ($value != "None") {
                $attributes[] = $value;
            }
        }
        return $attributes;
    }

    public function getProductAttributesDict()
    {
        $attributes = array();
        foreach ($this->_reserved_fields as $field) {
            $value = trim(Mage::getStoreConfig('merlinsearch/merlinindex/' . $field));
            if ($value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $attributes[$val] = $val;
                    }
                } else {
                    $attributes[$value] = $field;
                }
            }
        }
        return $attributes;
    }

    public function getEnumFacets()
    {
        $facets = array();
        foreach ($this->_facet_fields as $field) {
            $value = Mage::getStoreConfig('merlinsearch/merlinindex/' . $field);
            if (is_array($value)) {
                foreach ($value as $val) {
                    $facets[] = $val;
                }
            } else if ($value) {
                $facets[] = $value;
            }
        }
        return $facets;
    }

    public function isValidPair($field, $value)
    {
        $r_types = $this->_reserved_types[$field];
        $valid = false;
        foreach ($r_types as $r_type) {
            if ($r_type == "string") {
                if (is_string($value)) {
                    $valid = true;
                }
            } else if ($r_type == "float") {
                if (is_double($value)) {
                    $valid = true;
                }
            } else if ($r_type == "multi-string") {
                if ($this->isMultiString($value)) {
                    $valid = true;
                }
            } else if ($r_type == "multi-string-url") {
                if ($this->isMultiStringUrl($value)) {
                    $valid = true;
                 }
            } else if ($r_type == "string-gender") {
                if ($this->isGenderString($value)) {
                    $valid = true;
                }
            } else if ($r_type == "int") {
                if (is_int($value)) {
                    $valid = true;
                }
            }
        }
        return $valid;

    }


    private function isGenderString($value)
    {
        return in_array(strtolower($value), array('male', 'female', 'unisex'));
    }

    private function isMultiString($value)
    {
        $valid = false;
        if (is_array($value)) {
            if (count($value) >= 1) {
                $valid = true;
                foreach ($value as $val) {
                    if (!is_string($val)) {
                        $valid = false;
                    }
                }
            }
        }
        return $valid;
    }

    private function isMultiStringUrl($value)
    {
        $valid = false;
        $url_start = "http";
        $url_len = strlen($url_start);
        if (is_array($value)) {
            if (count($value) >= 1) {
                $valid = true;
                foreach ($value as $val) {
                    if (!is_string($val) || (!(substr($val, 0, $url_len) === $url_start))) {
                        $valid = false;
                    }
                }
            }
        }
        return $valid;
    }
}
