<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Csv;
use League\Csv\Reader;

class CsvReader extends Reader {

    const TYPE_RAIFFEISEN = 1;
    
    private $type;
    
    public function setType($type): void
    {
        $this->type = $type;
        $this->configureType();
    }
    
    public function configureType(): void
    {
        switch($this->type) {
            case self::TYPE_RAIFFEISEN:
                $this->setDelimiter(';');
                break;
        }
    }
    
}

?>