<?php

class DATABASE_CONFIG
{

    public $default = [
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'database' => 'foodcoopshop_test',
        'host' => '127.0.0.1',
        'login' => 'root',
        'password' => '',
        'prefix' => 'fcs_',
        'encoding' => 'utf8'
    ];

    public $test = [
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'database' => 'foodcoopshop_test',
        'host' => '127.0.0.1',
        'login' => 'root',
        'password' => '',
        'prefix' => 'fcs_',
        'encoding' => 'utf8'
    ];
}
