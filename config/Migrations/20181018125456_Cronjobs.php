<?php
use Cake\Core\Configure;
use Migrations\AbstractMigration;

class Cronjobs extends AbstractMigration
{
    public function change()
    {
        $this->execute("

            CREATE TABLE `fcs_cronjobs` (
              `id` int(11) NOT NULL,
              `name` varchar(255) NOT NULL,
              `time_interval` varchar(50) NOT NULL,
              `day_of_month` tinyint(3) UNSIGNED DEFAULT NULL,
              `weekday` varchar(50) DEFAULT NULL,
              `not_before_time` time NOT NULL,
              `active` tinyint(3) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            INSERT INTO `fcs_cronjobs` (`id`, `name`, `time_interval`, `day_of_month`, `weekday`, `not_before_time`, `active`) VALUES
            (1, 'BackupDatabase', 'day', NULL, NULL, '04:00:00', 1),
            (2, 'CheckCreditBalance', 'week', NULL, 'Friday', '22:30:00', 1),
            (3, 'EmailOrderReminder', 'week', NULL, 'Monday', '18:00:00', '" . Configure::read('app.emailOrderReminderEnabled') . "'),
            (4, 'PickupReminder', 'week', NULL, 'Monday', '09:00:00', 1),
            (5, 'SendInvoices', 'month', 11, NULL, '07:30:00', 1),
            (6, 'SendOrderLists', 'week', NULL, 'Wednesday', '04:30:00', 1);
            
            CREATE TABLE `fcs_cronjob_logs` (
              `id` int(11) NOT NULL,
              `cronjob_id` int(10) UNSIGNED NOT NULL,
              `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `success` tinyint(3) UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            ALTER TABLE `fcs_cronjobs`
              ADD PRIMARY KEY (`id`);
            ALTER TABLE `fcs_cronjob_logs`
              ADD PRIMARY KEY (`id`);
            ALTER TABLE `fcs_cronjobs`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
            ALTER TABLE `fcs_cronjob_logs`
              MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
            COMMIT;
            
        ");
        
    }
}
