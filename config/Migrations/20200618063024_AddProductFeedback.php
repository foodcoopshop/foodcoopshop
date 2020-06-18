<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddProductFeedback extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            CREATE TABLE `fcs_order_detail_feedbacks` (
              `id_order_detail` int(10) UNSIGNED NOT NULL,
              `text` text CHARACTER SET utf8mb4 NOT NULL,
              `customer_id` int(10) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `fcs_order_detail_feedbacks`
              ADD PRIMARY KEY (`id_order_detail`);
            ALTER TABLE `fcs_order_detail_feedbacks`
              MODIFY `id_order_detail` int(10) UNSIGNED NOT NULL;
            COMMIT;
        ");
    }
}
