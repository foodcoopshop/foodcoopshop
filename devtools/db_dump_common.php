<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!defined('DATASOURCE')) {
    exit('Do not use directly.');
}

$tmpDbName = 'foodcoopshop_tmp';

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

echo 'Loading config for ' . DATASOURCE . ', locale: ' . $locale . ' ';

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

$unmodifiedStructureFile = $dir . 'devtools' . DS . 'unmodified-structure.sql';
if (DATASOURCE == 'PROD' && $locale == 'de_DE') {
    copy($dir . $datasource[DATASOURCE]['structure'], $unmodifiedStructureFile);
}
$mysqldump_cmd = '';
$mysql_cmd = '';
$lines = @file($dir . 'config' . DS . 'app_config.php');
require('get_mysqldump_cmd.php');
require('get_mysql_cmd.php');

if (file_exists($dir . 'config' . DS . 'custom_config.php')) {
    $lines = @file($dir . 'config' . DS . 'custom_config.php');
    require('get_mysqldump_cmd.php');
    require('get_mysql_cmd.php');
}

echo 'done' . PHP_EOL;
echo 'Resetting database and executing migrations...';
$cmd = sprintf('"%1$s" -h %2$s -u %3$s -p%4$s --port %5$s -e "DROP DATABASE %6$s;"', $mysql_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $db_conf['port'], $tmpDbName);
exec($cmd);
$cmd = sprintf('"%1$s" -h %2$s -u %3$s -p%4$s --port %5$s -e "CREATE DATABASE %6$s;"', $mysql_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $db_conf['port'], $tmpDbName);
exec($cmd);
$cmd = sprintf('"%1$s" -h %2$s -u %3$s -p%4$s --port %5$s %6$s < %7$s', $mysql_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $db_conf['port'], $tmpDbName, $unmodifiedStructureFile);
exec($cmd);
$cmd = sprintf('"%1$s" -h %2$s -u %3$s -p%4$s --port %5$s %6$s < %7$s', $mysql_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $db_conf['port'], $tmpDbName, $dir . $datasource[DATASOURCE]['data']);
exec($cmd);

$cmd = 'bash ' . $dir . 'bin/cake migrations migrate';
exec($cmd, $result);
foreach ($result as $line) {
    echo PHP_EOL . $line;
}

$cmd = 'bash ' . $dir . 'bin/cake migrations migrate -p Queue';
exec($cmd, $result);
foreach ($result as $line) {
    echo PHP_EOL . $line;
}

echo PHP_EOL . 'Dumping structure...';
$result = array();
$cmd = sprintf('"%1$s" --host="%2$s" --user="%3$s" --password="%4$s" --no-create-db --no-data --events --routines --skip-opt --create-options --add-drop-table --disable-keys --extended-insert --quick --set-charset --quote-names --skip-comments --skip-add-locks --single-transaction --force --result-file="%5$s" %6$s 2>&1', $mysqldump_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $dir . $datasource[DATASOURCE]['structure'] . '.tmp', $tmpDbName);
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
$cmd = sprintf('"%1$s" --host="%2$s" --user="%3$s" --password="%4$s" --port="%5$s"--no-create-info --skip-opt --create-options --disable-keys --extended-insert --quick --set-charset --quote-names --skip-comments --skip-add-locks --single-transaction --force --result-file="%6$s" %7$s 2>&1', $mysqldump_cmd, $db_conf['host'], $db_conf['username'], $db_conf['password'], $db_conf['port'], $dir . $datasource[DATASOURCE]['data'] . '.tmp', $tmpDbName);
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
