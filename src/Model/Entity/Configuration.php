<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Configuration extends AppEntity
{

    const CASHLESS_PAYMENT_ADD_TYPE_MANUAL = 'manual';
    const CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD = 'list-upload';

    protected function _getText(): string
    {
        return __('Configuration_text_' . $this->name);
    }

    protected function _getSubtext(): string
    {

        $subtextKey = 'Configuration_subtext_' . $this->name;

        if (__($subtextKey) == $subtextKey) {
            return '';
        }

        return __($subtextKey);

    }

    protected function _getFulltext(): string
    {
        $subtextIncludingWrapper = '';
        if ($this->subtext != '') {
            $subtextIncludingWrapper = '<br /><div class="small">' . $this->subtext . '</div>';
        }
        return $this->text . $subtextIncludingWrapper;
}

}
