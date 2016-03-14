The goal of this extension is enable Blackbird Search Experience on Magento Stores.

The main functionality of this extension can be broken down into 3 parts

1. Autocomplete(typeahead)
    Handled in typeahead.phtml

2. Search
    Indexing-
        Handled in MerlinIndexer. Reindexer defaults to upload full attribute set for products in Batch Mode
        Mappings of Attributes to reserved field names is handling in system admin.
        Speed improvements to indexer are necessary. Handling of product groupings other than configuration are needed improvements
    Queries-
        Queries are handled in Colllection.php. This inherits from the base product collection class. LoadQuery function call Blackbird Search API
        with relevant query.
    Faceting and Filtering-
        Important Note: Filtering is only allowed on fields set to "filterable" the Blackbird API. Attempts to filter on non specified attributes will
        generate an error. Block/Layer/Filter/Price currently extends the Mage Price Filter but should be made more robust to handle customer price models. 

3. VRec (Visual Recommendations):
    VRec is currently implemented in Collection.php
    It is non currently enabled merlinsearch.xml
    Enabling VRec allows product pages to also show visually similar products as reccomendations. For instance if a Product is out of stock



