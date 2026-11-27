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

 class Manufacturer extends AppEntity {

    /**
     * fields that only admins and superadmins are allowed to change
     * must be the same as the fields set to false in $_accessible (except id_manufacturer)
     */
    const ADMIN_ONLY_FIELDS = [
        'id_customer',
        'active',
        'variable_member_fee',
        'anonymize_customers',
        'send_product_sold_out_limit_reached_for_contact_person',
    ];

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        '*' => true,
        'id_manufacturer' => false,
        'id_customer' => false,
        'active' => false,
        'variable_member_fee' => false,
        'anonymize_customers' => false,
        'send_product_sold_out_limit_reached_for_contact_person' => false,
    ];

    /**
     * @return array<string, bool>
     */
    public static function getAccessibleFieldsForAdmins(bool $isAdmin): array
    {
        return array_fill_keys(self::ADMIN_ONLY_FIELDS, $isAdmin);
    }

    protected function _getDecodedName(): string
    {
        return html_entity_decode($this->name);
    }

    protected function _getDecodedBankAccountHolder(): string
    {
        return html_entity_decode($this->bank_account_holder ?? '');
    }

}
