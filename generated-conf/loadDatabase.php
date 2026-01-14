<?php
$serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
$serviceContainer->initDatabaseMapFromDumps(array (
  'default' => 
  array (
    'tablesByName' => 
    array (
      'users' => '\\App\\Models\\Map\\UserTableMap',
    ),
    'tablesByPhpName' => 
    array (
      '\\User' => '\\App\\Models\\Map\\UserTableMap',
    ),
  ),
));
