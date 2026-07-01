<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexForActionLogsDate extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $sql = "ALTER TABLE `fcs_action_logs` ADD INDEX(`date`), ADD INDEX(`type`), ADD INDEX(`customer_id`), ADD INDEX(`object_type`), ADD INDEX(`object_id`);";
        $this->execute($sql);
    }
}
