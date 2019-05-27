<?php
/**
 * Dump DB structure and data for DATASOURCE
 */

if (!defined('DATASOURCE')) {
    exit('Do not use directly.');
}

$locale = 'de_DE';
// $locale = 'en_US';

$datasource = array(
    'PROD' => array(
        'structure' => 'config' . DS . 'sql' . DS . '_installation' . DS . 'clean-db-structure.sql',
        'data' => 'config' . DS . 'sql' . DS . '_installation' . DS . 'clean-db-data-' . $locale . '.sql',
    ),
    'TEST' => array(
        'structure' => 'config' . DS . 'sql' . DS . '_installation' . DS . 'clean-db-structure.sql',
        'data' => 'tests' . DS . 'config' . DS . 'sql' . DS . 'test-db-data.sql',
    ),
);

echo 'Loading config...';

// get the project dir from being in [project]/devtools/
$dir = dirname(realpath(__DIR__)) . DS;

// include DB config
$config = @include $dir . 'config' . DS . 'custom_config.php';
$dbConfig = $config['Datasources'];

if (DATASOURCE == 'PROD') {
    if (empty($dbConfig['default'])) {
        exit(PHP_EOL . 'Cannot read config' . DS . 'custom_config.php.' . PHP_EOL);
    }
    $db_conf = $dbConfig['default'];  // use production DB
}

if (DATASOURCE == 'TEST') {
    if (empty($dbConfig['test'])) {
        exit(PHP_EOL . 'Cannot read config' . DS . 'custom_config.php.' . PHP_EOL);
    }
    $db_conf = $dbConfig['test'];  // use test DB
}

if (empty($db_conf)) {
    exit(PHP_EOL . 'Unknown datasource.' . PHP_EOL);
}

echo 'done' . PHP_EOL;
echo 'Reading dump command...';

$dump_cmd = '';

$lines = @file($dir . 'config' . DS . 'app_config.php');
if (!is_array($lines)
    || empty($lines)
) {
    exit(PHP_EOL . 'Cannot load config' . DS . 'app_config.php.' . PHP_EOL);
}

foreach ($lines as $line) {
    if (($pos = strpos($line, '\'mysqlDumpCommand\'')) !== false) {
        $line = substr($line, $pos + strlen('\'mysqlDumpCommand\','));
        $line = explode('\'', $line, 3);
        $dump_cmd = $line[1];
    }
}

if (empty($dump_cmd)) {
    exit(PHP_EOL . 'Cannot read mysqlDumpCommand from Config' . DS . 'app_config.php.' . PHP_EOL);
}

if (strpos($dump_cmd, 'mysqldump') === false) {
    exit(PHP_EOL . 'Cannot use mysqlDumpCommand from Config' . DS . 'app_config.php. Must use mysqldump' . PHP_EOL);
}

if (file_exists($dir . 'config' . DS . 'custom_config.php')) {
    $lines = @file($dir . 'config' . DS . 'custom_config.php');
    if (!is_array($lines)
        || empty($lines)
    ) {
        exit(PHP_EOL . 'Cannot load Config' . DS . 'custom.config.php.' . PHP_EOL);
    }

    foreach ($lines as $line) {
        if (($pos = strpos($line, '\'mysqlDumpCommand\'')) !== false) {
            $line = substr($line, $pos + strlen('\'mysqlDumpCommand\','));
            $line = explode('\'', $line, 3);
            $dump_cmd = $line[1];
        }
    }

    if (empty($dump_cmd)) {
        exit(PHP_EOL . 'Cannot read app.mysqlDumpCommand from config' . DS . 'custom_config.php.' . PHP_EOL);
    }

    if (strpos($dump_cmd, 'mysqldump') === false) {
        exit(PHP_EOL . 'Cannot use app.mysqlDumpCommand from config' . DS . 'custom_config.php. Must use mysqldump' . PHP_EOL);
    }
}

echo 'done' . PHP_EOL;
echo 'Dumping structure...';

$result = array();
$cmd = sprintf('%1$s --host="%2$s" --user="%3$s" --password="%4$s" --no-create-db --no-data --events --routines --skip-opt --create-options --add-drop-table --disable-keys --extended-insert --quick --set-charset --quote-names --skip-comments --skip-add-locks --single-transaction --force --result-file="%5$s" %6$s 2>&1', $dump_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $dir . $datasource[DATASOURCE]['structure'] . '.tmp', $db_conf['database']);
exec($cmd, $result);

foreach ($result as $line) {
    echo PHP_EOL . $line;
}
if (!empty($result)) {
    echo PHP_EOL;
}

// check dump has a result file with some content of > 4k bytes (no error message should be that long)
clearstatcache();
if (!is_readable($datasource[DATASOURCE]['structure'] . '.tmp')
    || filesize($datasource[DATASOURCE]['structure'] . '.tmp') < 4096
) {
    exit(PHP_EOL . 'Structure not dumped. Seek for help!' . PHP_EOL);
}

rename($datasource[DATASOURCE]['structure'] . '.tmp', $datasource[DATASOURCE]['structure']);

echo 'done' . PHP_EOL;
echo 'Dumping data...';

$result = array();
$cmd = sprintf('%1$s --host="%2$s" --user="%3$s" --password="%4$s" --no-create-info --skip-opt --create-options --disable-keys --extended-insert --quick --set-charset --quote-names --skip-comments --skip-add-locks --single-transaction --force --result-file="%5$s" %6$s 2>&1', $dump_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $dir . $datasource[DATASOURCE]['data'] . '.tmp', $db_conf['database']);
exec($cmd, $result);

foreach ($result as $line) {
    echo PHP_EOL . $line;
}
if (!empty($result)) {
    echo PHP_EOL;
}

// check dump has a result file with some content of > 4k bytes (no error message should be that long)
clearstatcache();
if (!is_readable($datasource[DATASOURCE]['data'] . '.tmp')
    || filesize($datasource[DATASOURCE]['data'] . '.tmp') < 4096
) {
    exit(PHP_EOL . 'Data not dumped. Seek for help!' . PHP_EOL);
}

rename($datasource[DATASOURCE]['data'] . '.tmp', $datasource[DATASOURCE]['data']);

echo 'done' . PHP_EOL;
echo 'Strip autoincrement value from structure dump...';

$infile = fopen($dir . $datasource[DATASOURCE]['structure'], 'rb');
$outfile = fopen($dir . $datasource[DATASOURCE]['structure'] . '.tmp', 'wb');
if ($infile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['structure'] . 'for reading' . PHP_EOL);
}
if ($outfile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['structure'] . '.tmp' . 'for writing' . PHP_EOL);
}

while (!feof($infile)) {
    $line = fgets($infile);
    if (stripos($line, 'AUTO_INCREMENT=') !== false) {
        $line = preg_replace('/AUTO_INCREMENT=[0-9]*/i', '', $line);
    }
    fwrite($outfile, $line);
}
fclose($infile);
fclose($outfile);

rename($datasource[DATASOURCE]['structure'] . '.tmp', $datasource[DATASOURCE]['structure']);

echo 'done' . PHP_EOL;
echo 'Insert line breaks into inserts in data dump...';

$infile = fopen($dir . $datasource[DATASOURCE]['data'], 'rb');
$outfile = fopen($dir . $datasource[DATASOURCE]['data'] . '.tmp', 'wb');
if ($infile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['data'] . 'for reading' . PHP_EOL);
}
if ($outfile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['data'] . '.tmp' . 'for writing' . PHP_EOL);
}

while (!feof($infile)) {
    $line = fgets($infile);
    $line = str_replace('VALUES (', 'VALUES' . PHP_EOL . '(', $line);
    $line = str_replace('),(', '),' . PHP_EOL . '(', $line);
    fwrite($outfile, $line);
}
fclose($infile);
fclose($outfile);

rename($datasource[DATASOURCE]['data'] . '.tmp', $datasource[DATASOURCE]['data']);

echo 'done' . PHP_EOL;
echo 'Add table truncation to data dump...';

// get the table names from structure dump
$truncates = array();
exec('grep -i -e "^CREATE TABLE" "' . $dir . $datasource[DATASOURCE]['structure'] . '"', $truncates);
foreach ($truncates as $k => $v) {
    $truncates[$k] = str_ireplace(array('CREATE TABLE', ' ('), array('TRUNCATE TABLE', ';'), $v);
}

$infile = fopen($dir . $datasource[DATASOURCE]['data'], 'rb');
$outfile = fopen($dir . $datasource[DATASOURCE]['data'] . '.tmp', 'wb');
if ($infile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['data'] . 'for reading' . PHP_EOL);
}
if ($outfile === false) {
    exit(PHP_EOL . 'Cannot open ' . $datasource[DATASOURCE]['data'] . '.tmp' . 'for writing' . PHP_EOL);
}

$done = false;
while (!feof($infile)) {
    $line = fgets($infile);
    if (!$done && (stripos($line, '/*!40000 ALTER TABLE') === 0)) {
        $done = true;
        fwrite($outfile, '-- Truncate tables before insertion' . PHP_EOL);
        foreach ($truncates as $v) {
            fwrite($outfile, $v . PHP_EOL);
        }
        fwrite($outfile, PHP_EOL);
    }
    fwrite($outfile, $line);
}
fclose($infile);
fclose($outfile);

rename($datasource[DATASOURCE]['data'] . '.tmp', $datasource[DATASOURCE]['data']);

echo 'done' . PHP_EOL;
