<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class AddTaxesAustriaSeed extends AbstractSeed
{
    public function run()
    {
        $query = "
            INSERT INTO `fcs_tax` VALUES
            (1,20.000,1,0),
            (2,10.000,1,0),
            (3,13.000,1,0);
        ";
        $this->execute($query);
    }
}
