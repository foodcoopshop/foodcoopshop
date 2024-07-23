<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use App\Model\Traits\MultipleEmailsRuleTrait;
use App\Model\Traits\NoDeliveryDaysOrdersExistTrait;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Validation\Validator;
use App\Model\Traits\NumberRangeValidatorTrait;

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
class ConfigurationsTable extends AppTable
{

    use NumberRangeValidatorTrait;
    use MultipleEmailsRuleTrait;
    use NoDeliveryDaysOrdersExistTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;

    public const CASHLESS_PAYMENT_ADD_TYPE_MANUAL = 'manual';
    public const CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD = 'list-upload';

    public function initialize(array $config): void
    {
        $this->setTable('configuration');
        parent::initialize($config);
        $this->setPrimaryKey('name');
    }

    public function getVersion(): string
    {
        $versionFileWithPath = ROOT . DS . 'VERSION.txt';
        if (!file_exists($versionFileWithPath)) {
            throw new \Exception('version file not found: ' . $versionFileWithPath);
        }
        $file = fopen($versionFileWithPath, "r");
        $version = fgets($file);
        return $version;
    }

    public function validationFcsFacebookUrl(Validator $validator)
    {
        $validator->allowEmptyString('value');
        $validator->urlWithProtocol('value', __('Please_enter_a_valid_internet_address.'));
        return $validator;
    }

    public function validationFcsInstagramUrl(Validator $validator)
    {
        $validator->allowEmptyString('value');
        $validator->urlWithProtocol('value', __('Please_enter_a_valid_internet_address.'));
        return $validator;
    }

    public function validationFcsAppEmail(Validator $validator)
    {
        $validator->notEmptyString('value', __('Please_enter_an_email_address.'));
        $validator->email('value', true, __('The_email_address_is_not_valid.'));
        return $validator;
    }

    public function validationFcsAccountingEmail(Validator $validator)
    {
        $validator->notEmptyString('value', __('Please_enter_an_email_address.'));
        $validator->email('value', true, __('The_email_address_is_not_valid.'));
        return $validator;
    }

    public function validationFcsRegistrationNotificationEmails(Validator $validator)
    {
        $validator->allowEmptyString('value');
        $validator->add('value', 'multipleEmails', [
            'rule' => 'ruleMultipleEmails',
            'provider' => 'table',
            'message' => __('At_least_one_email_is_not_valid._Please_separate_multiple_with_comma_without_space.')
        ]);
        return $validator;
    }

    public function validationFcsBackupEmailAddressBcc(Validator $validator)
    {
        $validator->allowEmptyString('value');
        $validator->add('value', 'multipleEmails', [
            'rule' => 'ruleMultipleEmails',
            'provider' => 'table',
            'message' => __('At_least_one_email_is_not_valid._Please_separate_multiple_with_comma_without_space.')
        ]);
        return $validator;
    }

    public function validationFcsMinimalCreditBalance(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'value', -1000, 1000);
        return $validator;
    }

    public function validationFcsCheckCreditBalanceLimit(Validator $validator)
    {
        $validator->numeric('value', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'value', -100, 500);
        return $validator;
    }

    public function validationFcsNoDeliveryDaysGlobal(Validator $validator)
    {
        $validator->allowEmptyString('value');
        $validator->add('value', 'noDeliveryDaysOrdersExist', [
            'provider' => 'table',
            'rule' => 'noDeliveryDaysOrdersExist'
        ]);
        return $validator;
    }

    public function validationFcsNewsletterEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsShowProductsForGuests(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsSaveStorageLocationForProducts(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsUserFeedbackEnabled(Validator $validator)
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

    public function validationFcsFeedbackToProductsEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsOrderCommentEnabled(Validator $validator)
    {
        return $this->getNumberRangeValidator($validator, 'value', 0, 1);
    }

    public function validationFcsAllowOrdersForDeliveryRhythmOneOrTwoWeeksOnlyInWeekBeforeDelivery(Validator $validator)
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
        return $this->getNumberRangeValidator($validator, 'value', 0, 60);
    }

    public function validationFcsCashlessPaymentAddType(Validator $validator)
    {
        $values = array_keys(Configure::read('app.configurationHelper')->getCashlessPaymentAddTypeOptions());
        return $validator->inList('value', $values, __('The_following_values_are_valid:') . ' ' . implode(', ', $values));
    }

    public function validationFcsAppName(Validator $validator)
    {
        $validator->notEmptyString('value', __('Please_enter_the_name_of_the_foodcoop.'));
        $validator = $this->getLengthBetweenValidator($validator, 'value', 5, 255);
        return $validator;
    }

    private function getLengthBetweenValidator($validator, $field, $min, $max)
    {
        $message = __('The_amount_of_characters_needs_to_be_between_{0}_and_{1}.', [$min, $max]);
        $validator->lengthBetween($field, [$min, $max], $message);
        return $validator;
    }

    public function getConfigurations(array $customConditions = [])
    {
        $conditions = array_merge([
            'active' => APP_ON
        ], $customConditions);
        $configurations = $this->find('all',
        fields: ['name', 'value', 'type'],
        conditions: $conditions,
        order: [
            'position' => 'ASC'
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
