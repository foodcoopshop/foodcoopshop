<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMissingPrimaryKeyConfigurations extends BaseMigration
{

    public function change(): void
    {
        $query = "ALTER TABLE `fcs_configuration` DROP INDEX `name`;
                  ALTER TABLE `fcs_configuration` ADD PRIMARY KEY( `name`);";
        $this->execute($query);
    }
}
