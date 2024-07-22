<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {
        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'Keine Kühlung',10),
            (2,'Kühlschrank',20),
            (3,'Tiefkühler',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'Alle Produkte','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
