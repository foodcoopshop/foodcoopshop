<?php
/**
 * CakePayment
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CakePayment extends AppModel
{

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        ),
        'Manufacturer' => array(
            'foreignKey' => 'id_manufacturer'
        ),
        'CreatedBy' => array(
            'className' => 'Customer',
            'foreignKey' => 'created_by'
        ),
        'ChangedBy' => array(
            'className' => 'Customer',
            'foreignKey' => 'changed_by'
        ),
    );

    private function getManufacturerDepositConditions($manufacturerId=null)
    {
        $conditions = array(
            'CakePayment.status' => APP_ON,
            'CakePayment.id_customer' => 0
        );
        if (!is_null($manufacturerId)) {
            $conditions['CakePayment.id_manufacturer'] = $manufacturerId;
        }
        $conditions['CakePayment.type'] = 'deposit';
        return $conditions;
    }

    /**
     * @param int $manufacturerId
     * @param string $monthAndYear
     * @return array
     */
    public function getManufacturerDepositsByMonth($manufacturerId, $monthAndYear)
    {
        $conditions = $this->getManufacturerDepositConditions($manufacturerId);
        $conditions['DATE_FORMAT(CakePayment.date_add, \'%Y-%c\')'] = $monthAndYear;

        $paymentSum = $this->find('all', array(
            'fields' => 'CakePayment.*',
            'conditions' => $conditions,
            'order' => array('CakePayment.date_add' => 'DESC'),
        ));

        return $paymentSum;
    }

    /**
     * @return float
     */
    public function getManufacturerDepositMoneySum()
    {

        $conditions = $this->getManufacturerDepositConditions();
        $conditions['CakePayment.text'] = 'money';

        $paymentSum = $this->find('all', array(
            'fields' => 'SUM(amount) as sumManufacturerMoneyDeposit',
            'conditions' => $conditions,
            'order' => array('CakePayment.date_add' => 'DESC'),
        ));

        return $paymentSum[0][0]['sumManufacturerMoneyDeposit'];

    }

    /**
     * @param int $manufacturerId
     * @param boolean $groupByMonth
     * @return array
     */
    public function getMonthlyDepositSumByManufacturer($manufacturerId, $groupByMonth)
    {

        $conditions = $this->getManufacturerDepositConditions($manufacturerId);

        $fields = array(
            'SUM(amount) as sumDepositReturned'
        );
        if ($groupByMonth) {
            $fields[] = 'DATE_FORMAT(CakePayment.date_add, \'%Y-%c\') as monthAndYear';
        }
        $paymentSum = $this->find('all', array(
            'fields' => $fields,
            'conditions' => $conditions,
            'order' => array('CakePayment.date_add' => 'DESC'),
            'group' => $groupByMonth ? 'monthAndYear' : null
        ));

        return $paymentSum;
    }

    /**
     * @param int $customerId
     * @param string $type
     * @return float
     */
    public function getSum($customerId, $type)
    {
        $conditions = array(
            'CakePayment.id_customer' => $customerId,
            'CakePayment.id_manufacturer' => 0,
            'CakePayment.status' => APP_ON
        );

        $conditions['CakePayment.type'] = $type;

        $paymentSum = $this->find('all', array(
            'fields' => array(
                'SUM(amount) as SumAmount'
            ),
            'conditions' => $conditions
        ));

        return $paymentSum[0][0]['SumAmount'];
    }

}

?>