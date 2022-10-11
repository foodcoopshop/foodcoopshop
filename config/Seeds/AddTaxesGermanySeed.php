<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class AddTaxesGermanySeed extends AbstractSeed
{
    public function run()
    {
        $query = "
            INSERT INTO `fcs_tax` VALUES
            (1,19.000,1,0),
            (2,7.000,1,0),
            (3,9.500,1,0),
            (4,10.700,1,0);
        ";
        $this->execute($query);
    }
}
