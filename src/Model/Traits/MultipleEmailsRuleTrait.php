<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Validation\Validation;

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
trait MultipleEmailsRuleTrait
{

    public function ruleMultipleEmails($check): bool
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