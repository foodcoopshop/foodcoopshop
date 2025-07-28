<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UseStadardisedObjectTypeForStorageLocaions extends BaseMigration
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
        $query = $this->getQueryBuilder('update');
        $query->update('fcs_action_logs')
            ->set('object_type', 'storage_locations')
            ->where(['object_type' => 'storage_Locations'])
            ->execute();
    }
}
