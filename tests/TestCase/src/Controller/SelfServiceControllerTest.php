<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class SelfServiceControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use AssertPagesForErrorsTrait;
    use LoginTrait;
    use EmailTrait;

    public function testBarCodeLoginAsSuperadminIfNotEnabled()
    {
        $this->enableRetainFlashMessages();
        $this->doBarCodeLogin();
        $this->assertFlashMessage(__('Signing_in_failed_account_inactive_or_password_wrong?'));
    }

    public function testPageSelfService()
    {
        $this->loginAsSuperadmin();
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $testUrls = [
            $this->Slug->getSelfService()
        ];
        $this->assertPagesForErrors($testUrls);
    }

    public function testBarCodeLoginAsSuperadminValid()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->assertEquals($_SESSION['Auth']['User']['id_customer'], Configure::read('test.superadminId'));
    }

    public function testSelfServiceAddProductPricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1);
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceAddAttributePricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, 'bla bla');
        $response = $this->getJsonDecodedContent();
        $expectedErrorMessage = 'Bitte trage das entnommene Gewicht ein und klicke danach auf die Einkaufstasche.';
        $this->assertRegExpWithUnquotedString($expectedErrorMessage, $response->msg);
        $this->assertJsonError();
    }

    public function testSelfServiceOrderWithoutCheckboxes() {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(349, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertResponseContains('Bitte akzeptiere die AGB.');
        $this->assertResponseContains('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.');
    }

    public function testSelfServiceRemoveProductWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->removeProductFromSelfServiceCart(351);
        $this->assertJsonOk();
        $this->CartProductUnit = $this->getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $this->CartProductUnit->find('all')->toArray();
        $this->assertEmpty($cartProductUnits);
    }

    public function testSelfServiceOrderWithoutPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'order' => [
                'Carts.id_cart' => 'DESC'
            ],
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(1, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->assertMailCount(1);

        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailContainsHtmlAt(0, 'Artischocke');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testSelfServiceOrderWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = $this->getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'order' => [
                'Carts.id_cart' => 'DESC'
            ],
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Dein Einkauf');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt mit Varianten : 1,5 kg');
        $this->assertMailContainsHtmlAt(0, 'Lagerprodukt 2 : 0,5 kg');
        $this->assertMailContainsHtmlAt(0, '15,00 €');
        $this->assertMailContainsHtmlAt(0, '5,00 €');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));

    }

    public function testSelfServideOrderWithDeliveryBreak()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->loginAsSuperadmin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->finishSelfServiceCart(1, 1);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [])->toArray();
        $this->assertRegExpWithUnquotedString('Demo Superadmin hat eine neue Bestellung getätigt (15,00 €).', $actionLogs[0]->text);
    }

    private function addProductToSelfServiceCart($productId, $amount, $orderedQuantityInUnits = -1)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxAdd/',
            [
                'productId' => $productId,
                'amount' => $amount,
                'orderedQuantityInUnits' => $orderedQuantityInUnits
            ],
        );
        return $this->getJsonDecodedContent();
    }

    private function removeProductFromSelfServiceCart($productId)
    {
        $this->getSelfServicePostOptions();
        $this->post(
            '/warenkorb/ajaxRemove/',
            [
                'productId' => $productId
            ],
        );
        return $this->getJsonDecodedContent();
    }

    private function getSelfServicePostOptions()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest',
                'ACCEPT' => 'application/json',
                'REFERER' => Configure::read('app.cakeServerName') . '/' . __('route_self_service'),
            ],
        ]);
    }

    private function finishSelfServiceCart($general_terms_and_conditions_accepted, $cancellation_terms_accepted)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                'cancellation_terms_accepted' => $cancellation_terms_accepted
            ],
        ];
        $this->configRequest([
            'headers' => [
                'REFERER' => Configure::read('app.cakeServerName') . '/' . __('route_self_service'),
            ],
        ]);
        $this->post(
            $this->Slug->getSelfService(),
            $data,
        );
    }

    private function doBarCodeLogin()
    {
        $this->post($this->Slug->getLogin(), [
            'barCode' => Configure::read('test.superadminBarCode')
        ]);
    }

}
