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

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.superadminId'), 0, 'product');
        $this->assert403ForbiddenHeader();
        $this->assertEmpty($this->browser->getContent());
    }

    public function testAddPaymentWithInvalidData()
    {
        // TODO test wrong action type
        // TODO test and maybe change db_config_FCS_PAYMENT_PRODUCT_MAXIMUM
        // TODO test and change negative amount (200,--) is also triggered because of if (preg_match('/\-/', $amount)) change that
    }

    public function testAddPaymentForOneself()
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $amountToAdd = 10.5;
        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), $amountToAdd, 'product');
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($amountToAdd, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment product did not increase credit balance');
        $this->logout();

        // TODO test action log record
    }

    public function testAddPaymentAsSuperadminForCustomer()
    {
        $this->loginAsSuperadmin();
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $amountToAdd = 10.5;
        $this->addPayment(Configure::read('test.customerId'), $amountToAdd, 'product');
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($amountToAdd, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment product did not increase credit balance');
        $this->logout();

        // TODO test action log record
    }

    public function testAddDepositPaymentAsManufacturer()
    {
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
