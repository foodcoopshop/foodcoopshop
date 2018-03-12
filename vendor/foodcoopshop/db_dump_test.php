<?php
/**
 * Dump DB structure and data for installation
 */
if (!defined('DATASOURCE')) {
    define('DATASOURCE', 'TEST');
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// do the magic
include realpath(__DIR__) . DS . 'db_dump_common.php';
