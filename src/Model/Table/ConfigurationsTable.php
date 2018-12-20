<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use App\Lib\Error\Exception\ConfigFileMissingException;
use Cake\Filesystem\File;
use Cake\Validation\Validator;

/**
 * Configuration
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ConfigurationsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('configuration');
        parent::initialize($config);
        $this->setPrimaryKey('id_configuration');
    }

    /**
     * @param string $plugin
     * @throws ConfigFileMissingException
     * @return string (version)
     */
    public function getVersion()
    {
        $versionFileWithPath = ROOT . DS . 'VERSION.txt';

        if (!file_exists($versionFileWithPath)) {
            throw new ConfigFileMissingException('version file not found: ' . $versionFileWithPath);
        }
        $file = new File($versionFileWithPath);
        $version = $file->read(true, 'r');

        return $version;
    }

    public function validationFcsFacebookUrl(Validator $validator)
    {
        $validator->allowEmpty('value');
        $validator->urlWithProtocol('value', __('Please_enter_a_valid_internet_address.'));
        return $validator;
    }

    public function validationFcsAppEmail(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_an_email_address.'));
        $validator->email('value', false, __('The_email_address_is_not_valid.'));
        return $validator;
    }

    public function validationFcsAccountingEmail(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_an_email_address.'));
        $validator->email('value', false, __('The_email_address_is_not_valid.'));
        return $validator;
    }

    public function validationFcsBackupEmailAddressBcc(Validator $validator)
    {
        $validator->allowEmpty('value');
        $validator->email('value', false, __('The_email_address_is_not_valid.'));
        return $validator;
    }

    public function validationFcsMinimalCreditBalance(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'value', 0, 500);
        return $validator;
    }

    public function validationFcsCartEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsShowProductsForGuests(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsShowProductPriceForGuests(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsDefaultNewMemberActive(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsShowFoodcoopshopBacklink(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsOrderCommentEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsProductAvailabilityLow(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        return $this->getNumberRangeValidator($validator, 'value', 0, 50);
    }

    public function validationFcsDaysShowProductAsNew(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        return $this->getNumberRangeValidator($validator, 'value', 0, 14);
    }

    public function validationFcsPaymentProductMaximum(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        return $this->getNumberRangeValidator($validator, 'value', 50, 1000);
    }

    public function validationFcsCustomerGroup(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', CUSTOMER_GROUP_MEMBER, CUSTOMER_GROUP_ADMIN);
    }

    public function validationFcsAppName(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_the_name_of_the_foodcoop.'));
        $validator = $this->getLengthBetweenValidator($validator, 'value', 5, 255);
        return $validator;
    }

    public function validationFcsTimebasedCurrencyEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsTimebasedCurrencyName(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_the_paying_with_time_module_name.'));
        $validator = $this->getLengthBetweenValidator($validator, 'value', 2, 10);
        return $validator;
    }

    public function validationFcsTimebasedCurrencyShortcode(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_the_abbreviation_of_the_paying_with_time_module.'));
        $validator = $this->getLengthBetweenValidator($validator, 'value', 1, 3);
        return $validator;
    }

    public function validationFcsTimebasedCurrencyExchangeRate(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_enter_the_exchange_rate_for_the_paying_with_time_module_in_{0}.',[Configure::read('appDb.FCS_CURRENCY_SYMBOL')]));
        $validator->decimal('value', 2, __('Please_enter_exactly_2_decimals.'));
        return $validator;
    }

    public function validationFcsTimebasedCurrencyMaxCreditBalanceCustomer(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_provide_a_value.'));
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'value', 0, 50);
        return $validator;
    }

    public function validationFcsTimebasedCurrencyMaxCreditBalanceManufacturer(Validator $validator)
    {
        $validator->notEmpty('value', __('Please_provide_a_value.'));
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'value', 0, 200);
        return $validator;
    }

    private function getRuleEqualsToMultipleValuesValidator($validator, $field, $values)
    {
        $validator->inList($field, array_keys($values), __('The_following_values_are_valid:') . ' ' . implode(', ', array_keys($values)));
        return $validator;
    }

    private function getLengthBetweenValidator($validator, $field, $min, $max)
    {
        $message = __('The_amount_of_characters_needs_to_be_between_{0}_and_{1}.', [$min, $max]);
        $validator->lengthBetween($field, [$min, $max], $message);
        return $validator;
    }

    public function getConfigurations()
    {
        $configurations = $this->find('all', [
            'fields' => ['id_configuration', 'name', 'value', 'type', 'text'],
            'conditions' => [
                'active' => APP_ON
            ],
            'order' => [
                'position' => 'ASC'
            ]
        ]);
        return $configurations;
    }

    public function loadConfigurations()
    {
        $configurations = $this->getConfigurations();
        foreach ($configurations as $configuration) {
            Configure::write('appDb.' . $configuration->name, $configuration->value);
        }
    }
}
