<?php

App::uses('AppCakeTestCase', 'Test');

/**
 * PaymentsControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsControllerTest extends AppCakeTestCase
{

    public function testAccessAddLoggedOut()
    {
        $this->addPayment(Configure::read('test.superadminId'), 0, 'product');
        $this->assert403ForbiddenHeader();
        $this->assertEmpty($this->browser->getContent());
    }

    public function testAddProduct()
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance(Configure::read('test.superadminId'));
        $amount = 10.5;
        $this->browser->doFoodCoopShopLogin();
        $this->addPayment(Configure::read('test.superadminId'), $amount, 'product');
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance(Configure::read('test.superadminId'));
        $this->assertEquals($amount, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment product did not increase credit balance');
        $this->browser->doFoodCoopShopLogout();

        // TODO test action log record
        // TODO test adding for different user as superadmin (access, action log, correct user ids)
    }

    /**
     * @param int $productId
     * @return json string
     */
    private function addPayment($customerId, $amount, $type)
    {
        $this->browser->ajaxPost('/admin/payments/add', array(
            'data' => array(
                'customerId' => $customerId,
                'amount' => $amount,
                'type' => $type
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }
}
