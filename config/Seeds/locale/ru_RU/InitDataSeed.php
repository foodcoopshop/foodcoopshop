<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class InitDataSeed extends AbstractSeed
{
    public function run(): void
    {

        $translatedConfigurationValuesMap = [
            'FCS_DEFAULT_LOCALE' => 'ru_RU',
            'FCS_RIGHT_INFO_BOX_HTML' => '<h3>Время доставки</h3><p>Вы можете делать заказы каждую неделю до полуночи вторника и забирать товары в следующую пятницу.</p>',
            'FCS_REGISTRATION_INFO_TEXT' => 'Вам необходимо быть зарегистрированным покупателем если вы хотите сделать заказ.',
            'FCS_BANK_ACCOUNT_DATA' => 'Наменование банка / Реквизиты счёта / № тел. +7900000000',
            'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS' => ', 15:00 до 17:00 ч.',
            'FCS_CURRENCY_SYMBOL' => '₽',
        ];
        foreach($translatedConfigurationValuesMap as $configurationName => $value) {
            $this->execute("UPDATE fcs_configuration SET value = '$value' WHERE name = '$configurationName';");
        }

        $query = "
            INSERT INTO `fcs_storage_locations` VALUES
            (1,'Без охлаждения',10),
            (2,'Холодильник',20),
            (3,'Морозильная камера',30);
        ";
        $this->execute($query);

        $query = "
            INSERT INTO `fcs_category` VALUES
            (20,2,'Все Товары','',3,4,1,'2016-10-19 21:05:00','2016-10-19 21:05:00');
        ";
        $this->execute($query);

    }
}
