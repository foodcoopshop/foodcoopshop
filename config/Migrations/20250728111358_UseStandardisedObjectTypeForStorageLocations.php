<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UseStandardisedObjectTypeForStorageLocations extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->execute("
            UPDATE fcs_action_logs 
            SET object_type = 'storage_location' 
            WHERE object_type = 'storage_Location'
        ");
    }
}
