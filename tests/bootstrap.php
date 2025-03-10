<?php
declare(strict_types=1);

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
use Cake\Core\Configure;
use Cake\Utility\Security;
use Migrations\Migrations;
use Migrations\TestSuite\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

// 1) import structure
$migrator = new Migrator();
$migrator->runMany([
    ['plugin' => 'Queue', 'connection' => 'test'],
]);

// 2) run new migrations (located in main folder)
//$migrator->run([], false); // causes "Going to drop all tables in this source, and re-apply migrations."
$migrations = new Migrations();
$markAsMigratedMigrations = [
    '20240313184917_ManufacturerLoginFix',
    '20230612181632_AddConfigurationTextForHome',
    '20250209201857_AddConfigurationShowOnlyProductsForNextWeekFilterEnabled',
];
foreach($markAsMigratedMigrations as $markAsMigratedMigration) {
    $migrations->markMigrated($markAsMigratedMigration, ['connection' => 'test']);
}
$migrations->migrate(['connection' => 'test']);

Configure::write('appDb.FCS_DEFAULT_LOCALE', 'de_DE'); // manually set locale as fixtures are not loaded yet
require dirname(__DIR__) . '/config/bootstrap_locale.php';

Security::setSalt(Configure::read('Security.salt_for_unit_tests'));

// always set to app.customerMainNamePart to firstname for unit tests even if different in custom_config.php
Configure::write('app.customerMainNamePart', 'firstname');


$_SERVER['PHP_SELF'] = '/';

// phpunit with enabled processIsolation sends headers before output
// https://github.com/cakephp/docs/pull/6988
session_id('cli');
