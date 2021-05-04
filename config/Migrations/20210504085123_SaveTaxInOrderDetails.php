<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Cake\Datasource\FactoryLocator;

class SaveTaxInOrderDetails extends AbstractMigration
{
    public function change()
    {

        $this->execute("ALTER TABLE `fcs_order_detail` ADD `tax_unit_amount` DECIMAL(16,6) NOT NULL DEFAULT '0' AFTER `id_tax`, ADD `tax_total_amount` DECIMAL(16,6) NOT NULL DEFAULT '0' AFTER `tax_unit_amount`, ADD `tax_rate` DECIMAL(10,3) NOT NULL DEFAULT '0' AFTER `tax_total_amount`;");

        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $orderDetails = $this->OrderDetail->find('all', [
            'contain' => [
                'Taxes',
                'OrderDetailTaxes',
            ]
        ])->toArray();

        $i= 0;
        foreach($orderDetails as $orderDetail) {
            if (!is_null($orderDetail->tax)) {
                $orderDetails[$i]->tax_rate = $orderDetail->tax->rate;
            }
            if (!is_null($orderDetail->order_detail_tax)) {
                $orderDetails[$i]->tax_unit_amount = $orderDetail->order_detail_tax->unit_amount;
                $orderDetails[$i]->tax_total_amount = $orderDetail->order_detail_tax->total_amount;
            }
            $i++;
        }

        $this->OrderDetail->saveMany($orderDetails);

        $this->execute("DROP TABLE `fcs_order_detail_tax`;");
        $this->execute("ALTER TABLE `fcs_order_detail` DROP `id_tax`;");

    }
}
