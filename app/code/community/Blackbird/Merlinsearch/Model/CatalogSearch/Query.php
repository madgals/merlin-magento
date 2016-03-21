<?php

class Blackbird_Merlinsearch_Model_CatalogSearch_Query extends Mage_CatalogSearch_Model_Query
{
    public function getSuggestCollection() 
    {
        //$collection = parent::getSuggestCollection();
        //file_put_contents("debug.txt", print_r($collection, true), FILE_APPEND);
        //return $collection;
        return null;
    }
}
