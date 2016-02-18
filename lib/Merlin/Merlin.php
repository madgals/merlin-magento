<?php

require_once 'MerlinGetFunction.php';
require_once 'MultiFieldParameter.php';
require_once 'Facet.php';

require_once 'MerlinEngine.php';
require_once 'MerlinSearch.php';
require_once 'MerlinCrud.php';

require_once 'Search.php';
require_once 'Sort.php';
require_once 'Filter.php';
require_once 'EnumFacet.php';
require_once 'RangeFacet.php';
require_once 'HistFacet.php';
require_once 'Group.php';
require_once 'Geo.php';
require_once 'MultiSearch.php';
require_once 'Vrec.php';
require_once 'Qc.php';
require_once 'Typeahead.php';
require_once 'Crud.php';
require_once 'Feedback.php';


//namespace Merlin;
//
//spl_autoload_register(function ($class) {
//    $parts = explode('\\', $class);
//    $file = __DIR__ . DIRECTORY_SEPARATOR . end($parts) . '.php';
//    if (file_exists($file)) {
//        require $file;
//    }
//});
//
//function assertHandler($file, $line, $code, $desc = null)
//{
//    throw new MerlinException($desc);
//}
//assert_options(ASSERT_CALLBACK, 'assertHandler');
