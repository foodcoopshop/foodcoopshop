<?php

class DATABASE_CONFIG
{

    /**
     * database configuration of the production database
     */
    public $default = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'example.com',
        'login' => 'username',
        'password' => 'password',
        'database' => 'foodcoopshop_db',
        'prefix' => 'fcs_',
        'encoding' => 'utf8'
    );

    /**
     * database configuration of the test database
     */
//     public $test = array(
//         'datasource' => 'Database/Mysql',
//         'persistent' => false,
//         'host' => 'example.com',
//         'login' => 'user',
//         'password' => 'password',
//         'database' => 'foodcoopshop_db_test',
//         'prefix' => 'fcs_',
//         'encoding' => 'utf8'
//     );
    
}
