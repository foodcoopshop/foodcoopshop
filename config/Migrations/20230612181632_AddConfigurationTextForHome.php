<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class AddConfigurationTextForHome extends AbstractMigration
{
    public function change(): void
    {
        $configurationsTable = TableRegistry::getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->newEntity(
            [
                'name' => 'FCS_HOME_TEXT',
                'text' => 'Text that is shown on the home page.<br /><div class="small">Optional.</div>',
                'value' => '',
                'type' => 'textarea_big',
                'position' => 1290,
                'locale' => I18n::getLocale(),
            ]
        );
        $configurationsTable->save($configuration);
    }
}