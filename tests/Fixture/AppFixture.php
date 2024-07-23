<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

use FriendsOfCake\Fixturize\TestSuite\Fixture\ChecksumTestFixture;
use Cake\Datasource\ConnectionInterface;
use Cake\Core\Configure;


abstract class AppFixture extends ChecksumTestFixture {

    const IMPLEMENTED_FIXTURES = [
        'app.ActionLogs',
        'app.Addresses',
        'app.Attributes',
        'app.Barcodes',
        'app.BlogPosts',
        'app.Carts',
        'app.CartProducts',
        'app.CartProductUnits',
        'app.Categories',
        'app.CategoryProducts',
        'app.Configurations',
        'app.CronjobLogs',
        'app.Cronjobs',
        'app.Customers',
        'app.Deposits',
        'app.Images',
        'app.Invoices',
        'app.InvoiceTaxes',
        'app.Feedbacks',
        'app.Manufacturers',
        'app.OrderDetails',
        'app.OrderDetailFeedbacks',
        'app.OrderDetailPurchasePrices',
        'app.OrderDetailUnits',
        'app.Pages',
        'app.Payments',
        'app.PickupDays',
        'app.Products',
        'app.ProductAttributes',
        'app.ProductAttributeCombination',
        'app.PurchasePrices',
        'app.QueuedJobs',
        'app.QueueProcesses',
        'app.Sliders',
        'app.StockAvailables',
        'app.StorageLocations',
        'app.SyncDomains',
        'app.SyncProducts',
        'app.Taxes',
        'app.Units',
    ];

    public function truncate(ConnectionInterface $connection): bool
    {
        if (Configure::read('app.testDebug')) {
            return true;
        }
        return parent::truncate($connection);
    }

}
?>