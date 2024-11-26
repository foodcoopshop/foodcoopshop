<?php
declare(strict_types=1);

use Cake\Datasource\FactoryLocator;
use Migrations\AbstractMigration;

class ProductsAddFieldForNew extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('fcs_product');
        $table->addColumn('new', 'date', [
            'default' => null,
            'null' => true,
        ])->update();
        $this->execute('UPDATE fcs_product SET new = created');

        $actionLogsTable = FactoryLocator::get('Table')->get('ActionLogs');
        $actionLogs = $actionLogsTable->find()
            ->where([
                $actionLogsTable->aliasField('type') => 'product_added',
            ]);

        $productTable = FactoryLocator::get('Table')->get('Products');
        foreach ($actionLogs as $actionLog) {
            $productEntity = $productTable->find()->where([$productTable->aliasField('id_product') => $actionLog->object_id])->first();
            if (empty($productEntity)) {
                continue;
            }
            $productEntity->created = $actionLog->date;
            $productTable->save($productEntity, ['validate' => false]);
        }

    }
}
