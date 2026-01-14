<?php
$serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
$serviceContainer->initDatabaseMapFromDumps(array (
  'default' => 
  array (
    'tablesByName' => 
    array (
      'orders' => '\\App\\Models\\Map\\OrderTableMap',
      'products' => '\\App\\Models\\Map\\ProductTableMap',
      'users' => '\\App\\Models\\Map\\UserTableMap',
    ),
    'tablesByPhpName' => 
    array (
      '\\Order' => '\\App\\Models\\Map\\OrderTableMap',
      '\\Product' => '\\App\\Models\\Map\\ProductTableMap',
      '\\User' => '\\App\\Models\\Map\\UserTableMap',
    ),
  ),
));
