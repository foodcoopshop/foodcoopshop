<?php
declare(strict_types=1);

use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class AddConfigurationShowOnlyProductsForNextWeekFilterEnabled extends AbstractMigration
{
    public function change(): void
    {
        $configurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->newEntity(
            [
                'name' => 'FCS_SHOW_ONLY_PRODUCTS_FOR_NEXT_WEEK_FILTER_ENABLED',
                'active' => 1,
                'value' => 0,
                'type' => 'boolean',
                'position' => 3600,
            ]
        );
        $configurationsTable->save($configuration);
    }
}