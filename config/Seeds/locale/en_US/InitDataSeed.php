<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {

        $translatedConfigurationValuesMap = [
            'FCS_DEFAULT_LOCALE' => 'en_US',
            'FCS_RIGHT_INFO_BOX_HTML' => '<h3>Delivery time</h3><p>You can order every week until Tuesday midnight and pick the products up the following Friday.</p>',
            'FCS_REGISTRATION_INFO_TEXT' => 'You need to be a member if you want to order here.',
            'FCS_BANK_ACCOUNT_DATA' => 'Credit account Example Bank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878',
            'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS' => ', 3pm to 5pm',
            'FCS_CURRENCY_SYMBOL' => '$',
        ];
        foreach($translatedConfigurationValuesMap as $configurationName => $value) {
            $this->execute("UPDATE fcs_configuration SET value = '$value' WHERE name = '$configurationName';");
        }

        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'No cooling',10),
            (2,'Refrigerator',20),
            (3,'Freezer',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'All Products','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
