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
use App\Test\TestCase\AppCakeTestCase;
use App\Services\Csv\Writer\OrderDetailCsvWriterService;
use League\Csv\Bom;

class OrderDetailCsvWriterServiceTest extends AppCakeTestCase
{

    public string $defaultHeader = 'Id;Menge;Produkt;Hersteller;Preis;Pfand;Gewicht;"Preis pro";Mitglied;Abholtag;Bestellstatus;Bestell-Typ;Bestelldatum';

    public function testWrite(): void
    {

        $this->loginAsSuperadmin();

        $productIdA = 347; // forelle
        $this->addProductToCart($productIdA, 2);
        $this->finishCart();
        $pickupDay = '2018-02-02';
        $created = '2018-02-01 09:17:00';

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll([
            'pickup_day' => $pickupDay,
            'created' => $created,
        ], null);

        $this->setDummyRequest();
        

        $writerService = new OrderDetailCsvWriterService();
        $writerService->setRequestQueryParams([
            'pickupDay' => [
                $pickupDay,
            ],
        ]);
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);
        
        $this->assertEquals(6, count($lines));
        $this->assertEquals(Bom::Utf8->value . $this->defaultHeader, $lines[0]);
        $this->assertEquals('2;1;Beuschl;"Demo Fleisch-Hersteller";4,54;;;;"Demo Superadmin";02.02.2018;"Bestellung getätigt";Vorbestellung;"01.02.2018 09:17"', $lines[1]);
        $this->assertEquals('4;2;Forelle;"Demo Fleisch-Hersteller";10,50;;700;"100 g";"Demo Superadmin";02.02.2018;"Bestellung getätigt";Vorbestellung;"01.02.2018 09:17"', $lines[2]);
        $this->assertEquals('1;1;Artischocke;"Demo Gemüse-Hersteller";1,82;0,50;;Stück;"Demo Superadmin";02.02.2018;"Bestellung getätigt";Vorbestellung;"01.02.2018 09:17"', $lines[3]);
        $this->assertEquals('3;1;Milch;"Demo Milch-Hersteller";0,62;0,50;;0,5l;"Demo Superadmin";02.02.2018;"Bestellung getätigt";Vorbestellung;"01.02.2018 09:17"', $lines[4]);

    }

}
