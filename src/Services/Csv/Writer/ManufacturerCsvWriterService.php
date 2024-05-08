<?php
declare(strict_types=1);

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
namespace App\Services\Csv\Writer;

use Admin\Traits\Manufacturers\Filter\ManufacturersFilterTrait;

class ManufacturerCsvWriterService extends BaseCsvWriterService
{

    use ManufacturersFilterTrait;
    
    public function getHeader()
    {
        $header = [
            __('Id'),
            __('Name'),
            /*
            __('Zip'),
            __('City'),
            __('Street_and_number'),
            __('Additional_address_information'),
            __('Mobile'),
            __('Phone'),
            __('Email'),
            __('Status'),
            __('Credit'),
            __('Order_reminder'),
            __('Check_credit_reminder'),
            __('Register_date'),
            __('Last_pickup_day'),
            __('Comment'),
            */
        ];

        return $header;

    }

    public function getRecords()
    {

        $active = h($this->getRequestQuery('active', $this->getDefaultActive()));
        $dateFrom = h($this->getRequestQuery('dateFrom', $this->getDefaultDate()));

        $manufacturers = $this->getManufacturers($active, $dateFrom);
        $records = [];
        foreach($manufacturers as $manufacturer) {

            $record = [
                $manufacturer->id_manufacturer,
                $manufacturer->decoded_name,
                /*
                $manufacturer->address_manufacturer->postcode,
                $manufacturer->address_manufacturer->city,
                $manufacturer->address_manufacturer->address1,
                $manufacturer->address_manufacturer->address2,
                $manufacturer->address_manufacturer->phone_mobile,
                $manufacturer->address_manufacturer->phone,
                $manufacturer->email,
                $manufacturer->active,
                Configure::read('app.numberHelper')->formatAsDecimal($manufacturer->credit_balance),
                $manufacturer->email_order_reminder_enabled,
                $manufacturer->check_credit_reminder_enabled,
                $manufacturer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                $lastPickupDay,
                $this->decodeHtml($manufacturer->address_manufacturer->comment),
                */
            ];

            $records[] = $record;

        }

        return $records;
    }


}