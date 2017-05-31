<?php
/**
 * Configuration file used by Propel ORM
 *
 * Note: this file references environment variables. These environment variables
 * are defined in the .env file in the root of this project.
 *
 * If you want to use this file from a command line tool (eg. the Propel CLI tools)
 * prefix the command with `source .env &&` to load the environment variables from
 * the .env file.
 *
 * eg. To run the Propel command that build model classes, instead of:
 *
 *    $ vendor/bin/propel model:build
 *
 *  use:
 *
 *    $ source .env && vendor/bin/propel model:build
 *
 */
return [
    'propel' => [
        'generator' => [
            'namespaceAutoPackage' => false,
            'schema' => [
                'autoPackage' => true
            ]
        ],
        'paths' => [
            // The directory where Propel expects to find your `schema.xml` file.
            'schemaDir' => './tests/resources/propel/schemas/',

            // The directory where Propel should output generated object model classes.
            'phpDir' => './tests/resources/propel/model',
        ],
        'database' => [
            'connections' => [
                'default' => [
                    'adapter' => 'sqlite',
                    'dsn' => 'sqlite::memory:',
                    'user' => 'root',
                    'password' => 'password',
                    'classname' => '\\Propel\\Runtime\\Connection\\ConnectionWrapper'
                ]
            ]
        ]
    ]
];
