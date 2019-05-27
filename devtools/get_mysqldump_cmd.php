<?php
if (!is_array($lines)
    || empty($lines)
    ) {
        exit(PHP_EOL . 'Cannot load config' . DS . 'app_config.php.' . PHP_EOL);
    }
    
    foreach ($lines as $line) {
        if (($pos = strpos($line, '\'mysqlDumpCommand\'')) !== false) {
            $line = substr($line, $pos + strlen('\'mysqlDumpCommand\','));
            $line = explode('\'', $line, 3);
            $mysqldump_cmd = $line[1];
        }
    }
    
    if (empty($mysqldump_cmd)) {
        exit(PHP_EOL . 'Cannot read mysqlDumpCommand from Config' . DS . 'app_config.php.' . PHP_EOL);
    }
    
    if (strpos($mysqldump_cmd, 'mysqldump') === false) {
        exit(PHP_EOL . 'Cannot use mysqlDumpCommand from Config' . DS . 'app_config.php. Must use mysqldump' . PHP_EOL);
    }
?>