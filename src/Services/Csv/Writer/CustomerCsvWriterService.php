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

use Admin\Traits\Customers\Filter\CustomersFilterTrait;
use Cake\Core\Configure;

class CustomerCsvWriterService extends BaseCsvWriterService
{

    use CustomersFilterTrait;
    
    public function getHeader()
    {
        $header = [
            __('Id'),
            __('Name'),
            __('Zip'),
            __('City'),
            __('Street_and_number'),
            __('Additional_address_information'),
            __('Mobile'),
            __('Phone'),
            __('Group'),
            __('Email'),
            __('Status'),
            __('Credit'),
            __('Order_reminder'),
            __('Check_credit_reminder'),
            __('Register_date'),
            __('Last_pickup_day'),
            __('Comment'),
        ];

        if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
            $header[] = __('Newsletter');
        }
        if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS')) {
            $header[] = __('Member_fee') . ' ' . $this->getRequestQuery('year');
        }

        return $header;

    }

    public function getRecords()
    {

        $active = h($this->getRequestQuery('active', APP_ON));
        $year = h($this->getRequestQuery('year', date('Y')));
        $newsletter = h($this->getRequestQuery('newsletter', ''));

        $customers = $this->getCustomers($active, $year, $newsletter);
        $records = [];
        foreach($customers as $customer) {

            $lastPickupDay = '';
            if (!empty($customer->last_pickup_day)) {
                $lastPickupDay = $customer->last_pickup_day->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            }

            $record = [
                $customer->id_customer,
                $customer->decoded_name,
                $customer->address_customer->postcode,
                $customer->address_customer->city,
                $customer->address_customer->address1,
                $customer->address_customer->address2,
                $customer->address_customer->phone_mobile,
                $customer->address_customer->phone,
                Configure::read('app.htmlHelper')->getGroupName($customer->id_default_group),
                $customer->email,
                $customer->active,
                Configure::read('app.numberHelper')->formatAsDecimal($customer->credit_balance),
                $customer->email_order_reminder_enabled,
                $customer->check_credit_reminder_enabled,
                $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                $lastPickupDay,
                $customer->address_customer->comment,
            ];

            if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
                $record[] = $customer->newsletter_enabled;
            }

            if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS')) {
                Configure::read('app.numberHelper')->formatAsDecimal($record[] = $customer->member_fee);
            }

            $records[] = $record;

        }

        return $records;
    }


}