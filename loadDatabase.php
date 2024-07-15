<?php
$serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
$serviceContainer->initDatabaseMapFromDumps(array (
  'default' => 
  array (
    'tablesByName' => 
    array (
      'license_types' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\LicenseTypeTableMap',
      'product_licenses' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\LicenseTableMap',
      'product_serial_numbers' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\ProductTableMap',
      'shop_products' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\ProductTypeTableMap',
    ),
    'tablesByPhpName' => 
    array (
      '\\License' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\LicenseTableMap',
      '\\LicenseType' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\LicenseTypeTableMap',
      '\\Product' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\ProductTableMap',
      '\\ProductType' => '\\Serato\\SwsApp\\Test\\Propel\\Model\\Map\\ProductTypeTableMap',
    ),
  ),
));
