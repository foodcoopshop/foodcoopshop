<?php
if (!is_array($lines)
    || empty($lines)
    ) {
        exit(PHP_EOL . 'Cannot load config' . DS . 'app_config.php.' . PHP_EOL);
    }
    
    foreach ($lines as $line) {
        if (($pos = strpos($line, '\'mysqlCommand\'')) !== false) {
            $line = substr($line, $pos + strlen('\'mysqlCommand\','));
            $line = explode('\'', $line, 3);
            $mysql_cmd = $line[1];
        }
    }
    
    if (empty($mysql_cmd)) {
        exit(PHP_EOL . 'Cannot read mysqlCommand from Config' . DS . 'app_config.php.' . PHP_EOL);
    }
    
    if (strpos($mysql_cmd, 'mysql') === false) {
        exit(PHP_EOL . 'Cannot use mysqlCommand from Config' . DS . 'app_config.php. Must use mysql' . PHP_EOL);
    }
?>