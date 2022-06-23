<?php

namespace App\Model\Table;

use App\ORM\AppMarshaller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Marshaller;
use Cake\ORM\Table;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

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
class AppTable extends Table
{

    public $tablePrefix = 'fcs_';

    public function initialize(array $config): void
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        if ((php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
            $this->setConnection(ConnectionManager::get('test'));
        }
        parent::initialize($config);
    }

    public function getAllowOnlyOneWeekdayValidator(Validator $validator, $field, $fieldName)
    {
        $validator->add($field, 'allow-only-one-weekday', [
            'rule' => function ($value, $context) {
            if (Configure::read('app.timeHelper')->getDeliveryWeekday() != Configure::read('app.timeHelper')->formatAsWeekday(strtotime($value))) {
                return false;
            }
            return true;
            },
            'message' => __('{0}_needs_to_be_a_{1}.', [
                $fieldName,
                Configure::read('app.timeHelper')->getWeekdayName(Configure::read('app.timeHelper')->getDeliveryWeekday())
            ])
        ]);
        return $validator;
    }

    public function clearZeroArray($array)
    {
        foreach($array as $key => $value) {
            if (array_sum($value) == 0) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function noDeliveryDaysOrdersExist ($value, $context) {

        $manufacturerId = null;
        if (!empty($context['data']) && !empty($context['data']['id_manufacturer'])) {
            $manufacturerId = $context['data']['id_manufacturer'];
        }

        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');

        if (!is_null($manufacturerId)) {
            $productsAssociation = $orderDetailsTable->getAssociation('Products');
            $productsAssociation->setJoinType('INNER'); // necessary to apply condition
            $productsAssociation->setConditions([
                'Products.id_manufacturer' => $manufacturerId
            ]);
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $query = $orderDetailsTable->find('all', [
            'conditions' => [
                'pickup_day IN' => $value
            ],
            'group' => 'pickup_day',
            'contain' => [
                'Products'
            ]
        ]);
        $query->select(
            [
                'PickupDayCount' => $query->func()->count('OrderDetails.pickup_day'),
                'pickup_day'
            ]
        );

        $result = true;
        if (!empty($query->toArray())) {
            $pickupDaysInfo = [];
            foreach($query->toArray() as $orderDetail) {
                $formattedPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                $pickupDaysInfo[] = $formattedPickupDay . ' (' . $orderDetail->PickupDayCount . 'x)';
            }
            $result = __('The_following_delivery_day(s)_already_contain_orders:_{0}._To_save_the_delivery_break_either_cancel_them_or_change_the_pickup_day.', [join(', ', $pickupDaysInfo)]);
        }

        return $result;
    }

    public function getNumberRangeValidator(Validator $validator, $field, $min, $max, $additionalErrorMessageSuffix='', $showDefaultErrorMessage=true)
    {
        $message = __('Please_enter_a_number_between_{0}_and_{1}.', [
            Configure::read('app.numberHelper')->formatAsDecimal($min, 0),
            Configure::read('app.numberHelper')->formatAsDecimal($max, 0)
        ]);
        if ($additionalErrorMessageSuffix != '') {
            if (!$showDefaultErrorMessage) {
                $message = '';
            }
            $message .= ' ' . $additionalErrorMessageSuffix;
        }
        $validator->lessThanOrEqual($field, $max, $message);
        $validator->greaterThanOrEqual($field, $min, $message);
        $validator->notEmptyString($field, $message);
        return $validator;
    }

    public function sortByVirtualField($object, $name)
    {
        $sortedObject = (object) Hash::sort($object->toArray(), '{n}.' . $name, 'ASC', [
            'type' => 'locale',
            'ignoreCase' => true,
        ]);
        return $sortedObject;
    }

    public function getAllValidationErrors($entity)
    {
        $preparedErrors = [];
        foreach($entity->getErrors() as $field => $message) {
            $errors = array_keys($message);
            foreach($errors as $error) {
                $preparedErrors[] = $message[$error];
            }
        }
        return $preparedErrors;
    }

    /**
     * {@inheritDoc}
     * @see \Cake\ORM\Table::marshaller()
     */
    public function marshaller(): Marshaller
    {
        return new AppMarshaller($this);
    }

    public function ruleMultipleEmails($check)
    {
        $emails = explode(',', $check);
        if (!is_array($emails)) {
            $emails = [$emails];
        }
        foreach ($emails as $email) {
            $validates = Validation::email($email, true);
            if (!$validates) {
                return false;
            }
        }
        return true;
    }

}
