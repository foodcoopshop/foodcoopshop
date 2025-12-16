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

class BarcodesFixture extends AppFixture
{
    public string $table = 'fcs_barcodes';

    public const array BARCODE_PRODUCT_A = [
        'id' => 1,
        'product_id' => ProductsFixture::ID_STOCK_PRODUCT_A,
        'product_attribute_id' => 0,
        'barcode' => '234567890123', // 12 digits
    ];
    public const array BARCODE_ATTRIBUTE_A = [
        'id' => 2,
        'product_id' => 0,
        'product_attribute_id' => 13,
        'barcode' => '2145678901234', // 13 digits
    ];
    public const array BARCODE_PRODUCT_B = [
        'id' => 3,
        'product_id' => ProductsFixture::ID_STOCK_PRODUCT_WITH_WEIGHT_BARCODE,
        'product_attribute_id' => 0,
        'barcode' => '2712345000000',
    ];
    public const array BARCODE_ATTRIBUTE_B = [
        'id' => 4,
        'product_id' => 0,
        'product_attribute_id' => 15,
        'barcode' => '2112345000000',
    ];

    public function init(): void
    {
        $this->records = [
            self::BARCODE_PRODUCT_A,
            self::BARCODE_ATTRIBUTE_A,
            self::BARCODE_PRODUCT_B,
            self::BARCODE_ATTRIBUTE_B,
        ];
        parent::init();
    }

}
?>