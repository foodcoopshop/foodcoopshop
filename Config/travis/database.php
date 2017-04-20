<?php

class DATABASE_CONFIG
{

    public $default = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'database' => 'foodcoopshop_test',
        'host' => '127.0.0.1',
        'login' => 'travis',
        'password' => '',
        'prefix' => 'fcs_',
        'encoding' => 'utf8'
    );

    public $test = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'database' => 'foodcoopshop_test',
        'host' => '127.0.0.1',
        'login' => 'travis',
        'password' => '',
        'prefix' => 'fcs_',
        'encoding' => 'utf8'
    );
}
