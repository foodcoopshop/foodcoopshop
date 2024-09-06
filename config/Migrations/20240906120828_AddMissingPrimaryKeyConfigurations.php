<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMissingPrimaryKeyConfigurations extends AbstractMigration
{

    public function change(): void
    {
        $query = "ALTER TABLE `fcs_configuration` DROP INDEX `name`;
                  ALTER TABLE `fcs_configuration` ADD PRIMARY KEY( `name`);";
        $this->execute($query);
    }
}
