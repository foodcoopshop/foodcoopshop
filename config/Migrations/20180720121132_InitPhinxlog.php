<?php
use Migrations\AbstractMigration;

class InitPhinxlog extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            
            CREATE TABLE IF NOT EXISTS `phinxlog` (
              `version` bigint(20) NOT NULL,
              `migration_name` varchar(100) DEFAULT NULL,
              `start_time` timestamp NULL DEFAULT NULL,
              `end_time` timestamp NULL DEFAULT NULL,
              `breakpoint` tinyint(1) NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            ALTER TABLE `phinxlog` ADD UNIQUE(`migration_name`);
        
            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180213193117, 'Migration018', '2018-02-13 17:49:03', '2018-02-13 17:49:04', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
                (20180213193123, 'Migration019', '2018-02-13 17:49:04', '2018-02-13 17:49:04', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180213193133, 'Migration020', '2018-02-13 17:49:04', '2018-02-13 17:49:04', 0); 

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180217191214, 'CakeUpdate', '2018-03-05 09:31:25', '2018-03-05 09:32:13', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180313083036, 'TimebasedCurrency', '2018-04-16 07:26:12', '2018-04-16 07:26:20', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180430130912, 'PricePerUnit', '2018-05-18 09:00:58', '2018-05-18 09:01:06', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180528152246, 'ShowProductPriceForGuests', '2018-06-01 10:02:58', '2018-06-01 10:02:58', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180604063719, 'PricePerUnitFix', '2018-06-04 12:56:06', '2018-06-04 12:56:07', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180601101119, 'LocaleConfig', '2018-06-11 08:13:02', '2018-06-11 08:13:03', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180613080329, 'CategoriesLevelDepthFix', '2018-06-13 08:20:52', '2018-06-13 08:20:53', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180613121712, 'TreeLeftRightFix', '2018-06-13 12:29:04', '2018-06-13 12:29:06', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180613174031, 'CurrencySymbolAsConfiguration', '2018-06-25 08:55:19', '2018-06-25 08:55:19', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180626080524, 'AddLocaleToDatabaseConfig', '2018-06-27 08:00:47', '2018-06-27 08:00:47', 0);

            INSERT IGNORE INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES 
                (20180702075300, 'RenameShopOrderToInstantOrder', '2018-07-02 09:23:18', '2018-07-02 09:23:18', 0);
            
            ALTER TABLE `phinxlog` DROP INDEX `migration_name`;

            ");
        
    }
}
