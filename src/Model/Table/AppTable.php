<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\ORM\AppMarshaller;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
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
 * 
 * @template TEntity of \Cake\Datasource\EntityInterface
 * @method TEntity newEmptyEntity()
 * @method TEntity newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<TEntity> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method TEntity get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method TEntity findOrCreate(mixed $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method TEntity patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<TEntity> patchEntities(iterable<TEntity> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method TEntity|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method TEntity saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<TEntity>|false saveMany(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method iterable<TEntity> saveManyOrFail(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method bool delete(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method bool deleteMany(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method bool deleteManyOrFail(iterable<TEntity> $entities, array<string, mixed> $options = [])
 */
class AppTable extends Table
{

    public string $tablePrefix = 'fcs_';

    public function initialize(array $config): void
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        if ((PHP_SAPI == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
            /** @phpstan-ignore-next-line */
            $this->setConnection(ConnectionManager::get('test'));
        }
        parent::initialize($config);
    }

    /**
     * @return list<string>
     */
    public function getAllValidationErrors(EntityInterface $entity): array
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

    public function marshaller(): Marshaller
    {
        return new AppMarshaller($this);
    }

}
