<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddConfigurationShowOnlyProductsForNextWeekFilterEnabled extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            INSERT INTO fcs_configuration (name, active, value, type, position)
            VALUES (
                'FCS_SHOW_ONLY_PRODUCTS_FOR_NEXT_WEEK_FILTER_ENABLED', 
                1,
                0, 
                'boolean', 
                3600
            );
        ");
    }
}