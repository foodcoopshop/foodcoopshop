<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait NumberRangeValidatorTrait
{

    public function getNumberRangeValidator(Validator $validator, $field, $min, $max, $additionalErrorMessageSuffix='', $showDefaultErrorMessage=true): Validator
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

}