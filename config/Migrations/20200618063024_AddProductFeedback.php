<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddProductFeedback extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            CREATE TABLE `fcs_order_detail_feedbacks` (
              `id_order_detail` int(10) UNSIGNED NOT NULL DEFAULT '0',
              `text` text CHARACTER SET utf8mb4 NOT NULL,
              `customer_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ALTER TABLE `fcs_order_detail_feedbacks`
              ADD PRIMARY KEY (`id_order_detail`);
            COMMIT;
        ");
    }
}
