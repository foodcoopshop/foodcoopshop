<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class OrderDetailFeedbacksTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_order_detail');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('text', __('Please_enter_your_feedback.'));
        $validator->minLength('text', 5, __('Please_enter_at_least_{0}_characters.', [5]));
        return $validator;
    }


}
