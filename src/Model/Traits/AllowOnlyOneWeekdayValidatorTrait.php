<?php
declare(strict_types=1);

namespace App\Model\Traits;

use App\Services\DeliveryRhythmService;
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
trait AllowOnlyOneWeekdayValidatorTrait
{

    public function getAllowOnlyOneWeekdayValidator(Validator $validator, $field, $fieldName): Validator
    {
        $validator->add($field, 'allow-only-one-weekday', [
            'rule' => function ($value, $context) {
            if ((new DeliveryRhythmService())->getDeliveryWeekday() != Configure::read('app.timeHelper')->formatAsWeekday(strtotime($value))) {
                return false;
            }
            return true;
            },
            'message' => __('{0}_needs_to_be_a_{1}.', [
                $fieldName,
                Configure::read('app.timeHelper')->getWeekdayName((new DeliveryRhythmService())->getDeliveryWeekday())
            ])
        ]);
        return $validator;
    }

}