<?php
/**
 * Dump DB structure and data for installation
 */
if (!defined('DATASOURCE')) {
    define('DATASOURCE', 'PROD');
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$locale= $argv[1];
include realpath(__DIR__) . DS . 'db_dump_common.php';
