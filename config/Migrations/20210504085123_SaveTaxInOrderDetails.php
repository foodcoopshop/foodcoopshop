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

        $orderDetails = $this->OrderDetail->find('all')->toArray();

        $i= 0;
        foreach($orderDetails as $orderDetail) {

            $sql = "SELECT t.rate FROM fcs_tax t LEFT JOIN fcs_order_detail od ON t.id_tax = od.id_tax WHERE od.id_order_detail = :orderDetailId";
            $statement = $this->OrderDetail->getConnection()->prepare($sql);
            $params = ['orderDetailId' => $orderDetail->id_order_detail];
            $statement->execute($params);
            $taxes =  $statement->fetchAll('assoc');

            if (!empty($taxes)) {
                $orderDetails[$i]->tax_rate = $taxes[0]['rate'];
            }

            $sql = "SELECT odt.* FROM fcs_order_detail od LEFT JOIN fcs_order_detail_tax odt ON odt.id_order_detail = od.id_order_detail WHERE od.id_order_detail = :orderDetailId";
            $statement = $this->OrderDetail->getConnection()->prepare($sql);
            $params = ['orderDetailId' => $orderDetail->id_order_detail];
            $statement->execute($params);
            $orderDetailTaxes =  $statement->fetchAll('assoc');

            if (!empty($orderDetailTaxes)) {
                $orderDetails[$i]->tax_unit_amount = $orderDetailTaxes[0]['unit_amount'] ?? 0;
                $orderDetails[$i]->tax_total_amount = $orderDetailTaxes[0]['total_amount'] ?? 0;
            }
            $i++;
        }

        $this->OrderDetail->saveMany($orderDetails);

        $this->execute("DROP TABLE `fcs_order_detail_tax`;");
        $this->execute("ALTER TABLE `fcs_order_detail` DROP `id_tax`;");

    }
}
