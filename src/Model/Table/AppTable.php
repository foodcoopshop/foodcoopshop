<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\ORM\AppMarshaller;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Marshaller;
use Cake\ORM\Table;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppTable extends Table
{

    public $tablePrefix = 'fcs_';

    public function initialize(array $config): void
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        if ((PHP_SAPI == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
            /** @phpstan-ignore-next-line */
            $this->setConnection(ConnectionManager::get('test'));
        }
        parent::initialize($config);
    }

    public function getAllValidationErrors($entity)
    {
        $preparedErrors = [];
        foreach($entity->getErrors() as $field => $message) {
            $errors = array_keys($message);
            foreach($errors as $error) {
                $preparedErrors[] = $message[$error];
            }
        }
        return $preparedErrors;
    }

    /**
     * {@inheritDoc}
     * @see \Cake\ORM\Table::marshaller()
     */
    public function marshaller(): Marshaller
    {
        return new AppMarshaller($this);
    }

}
