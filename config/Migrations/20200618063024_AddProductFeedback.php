<?php
declare(strict_types=1);

use Cake\I18n\I18n;
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

        switch(I18n::getLocale()) {
            case 'de_DE':
                $text = 'Feedback-Funktion für Produkte aktiviert?<br /><div class="small">Mitglieder können Feedback zu bestellten Produkte verfassen.</div>';
                break;
            case 'pl_PL':
            case 'en_US':
                $text = 'Are members allowed to write feedback to products?';
                break;
        }

        $sql = "INSERT INTO `fcs_configuration` (
                  `id_configuration`, `active`, `name`, `text`, `value`, `type`, `position`, `locale`, `date_add`, `date_upd`
               )
               VALUES (
                    NULL, '1', 'FCS_FEEDBACK_TO_PRODUCTS_ENABLED', '".$text."', '1', 'boolean', '320', '".I18n::getLocale()."', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";
        $this->execute($sql);

    }
}
